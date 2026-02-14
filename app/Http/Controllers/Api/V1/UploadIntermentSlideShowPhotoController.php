<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Domain\UploadIntermentSlideShowPhoto\Models\UploadIntermentSlideShowPhoto;
use App\Services\GoogleDriveService;
use Carbon\Carbon;

class UploadIntermentSlideShowPhotoController extends Controller
{
    protected $gdrive;

    public function __construct(GoogleDriveService $gdrive)
    {
        $this->gdrive = $gdrive;
    }

    /**
     * Upload slideshow photos (multiple) to Google Drive
     */
        public function store(Request $request)
        {
            // Validate the request
            $request->validate([
                'document_no'   => 'required|string|max:50',
                'uploader_name' => 'required|string|max:100',
                'email_add'     => 'required|email|max:100', // added proper email validation
                'photo.*'       => 'required|image|max:10240', // 10MB
            ]);

            // Get interment record to retrieve occupant
            $records = $this->getIntermentsCustomerRecordsForSlideShowUpload($request->document_no)->getData();
            if (empty($records[0])) {
                return response()->json(['message' => 'Occupant not found.'], 404);
            }

            $occupantName = $records[0]->occupant;

            // Get or create slideshow record
            $slideshow = UploadIntermentSlideShowPhoto::firstOrNew([
                'document_no' => $request->document_no,
            ]);

            $existingPhotos = $slideshow->photo ?? [];

            // Create folder in Google Drive if not already saved
            if (empty($slideshow->folder_id)) {
                $folder = $this->gdrive->createFolder($occupantName);
                $slideshow->folder_id = $folder->id;
            }

            $uploadedFiles = [];
            if ($request->hasFile('photo')) {
                $uploadedFiles = $this->gdrive->uploadFiles($request->file('photo'), $slideshow->folder_id);
            }

            // Get the Drive links
            $fileLinks = array_map(fn($f) => $f->webViewLink, $uploadedFiles);

            // Save all fields including email_add
            $slideshow->uploader_name = $request->uploader_name;
            $slideshow->email_add = $request->email_add; // ✅ Save email
            $slideshow->photo = array_merge($existingPhotos, $fileLinks);
            $slideshow->submitted_at = now();
            $slideshow->save();

            return response()->json([
                'message' => 'Slideshow photos uploaded to Google Drive successfully.',
                'data'    => $slideshow,
            ], 201);
        }

    /**
     * Get interment record for slideshow upload
     */
    public function getIntermentsCustomerRecordsForSlideShowUpload($document_no)
    {
        $query = "
            SELECT
                bpar.bpar_i_person_id,
                bpar.name1,
                inter.documentno,
                inter.date_interment,
                occ.occupant_name AS occupant,
                info.contact_no
            FROM mp_t_interment_order inter
            JOIN mp_l_ownership ship USING (mp_l_ownership_id)
            JOIN mp_l_preownership preown USING (mp_l_preownership_id)
            JOIN mp_i_owner owner ON preown.mp_i_owner_id = owner.mp_i_owner_id
            JOIN bpar_i_person bpar ON owner.bpar_i_person_id = bpar.bpar_i_person_id
            JOIN mp_t_interment_order_occupancy occ 
                ON inter.mp_t_interment_order_id = occ.mp_t_interment_order_id
            JOIN mp_t_interment_order_informants_data info 
                ON occ.mp_t_interment_order_id = info.mp_t_interment_order_id
            WHERE inter.documentno NOT LIKE '%-CA'
              AND inter.documentno NOT LIKE '%DR'
              AND inter.documentno = :document_no
        ";

        $records = DB::connection('mysql_secondary')->select($query, ['document_no' => $document_no]);

        if (empty($records)) {
            return response()->json(['message' => 'No records found.'], 404);
        }

        $intermentDate = $records[0]->date_interment ?? null;
        if (!$intermentDate) {
            return response()->json(['message' => 'Invalid interment date.'], 400);
        }

        $expiryDate = Carbon::parse($intermentDate)->addDays(3);
        $now = Carbon::now();

        if ($now->gt($expiryDate)) {
            return response()->json(['message' => 'Link expired.'], 403);
        }

        return response()->json($records);
    }

    /**
     * Get slideshow by document number
     */
    public function showByDocumentNo($document_no)
    {
        $slideshow = UploadIntermentSlideShowPhoto::where('document_no', $document_no)->first();
        if (!$slideshow) {
            return response()->json(['message' => 'Slideshow not found.'], 404);
        }
        return response()->json($slideshow);
    }

    /**
     * Delete a single photo (Drive version)
     */
    public function deletePhoto(Request $request, $id)
    {
        $slideshow = UploadIntermentSlideShowPhoto::find($id);
        if (!$slideshow) {
            return response()->json(['message' => 'Slideshow not found.'], 404);
        }

        $photoUrl = $request->input('photo_url');
        $slideshow->photo = array_filter($slideshow->photo ?? [], fn($p) => $p !== $photoUrl);

        // TODO: Optionally delete from Google Drive using Drive file ID

        $slideshow->save();

        return response()->json(['message' => 'Photo deleted.', 'photos' => $slideshow->photo]);
    }

    /**
     * Delete entire slideshow
     */
    public function destroy($id)
    {
        $slideshow = UploadIntermentSlideShowPhoto::find($id);
        if (!$slideshow) {
            return response()->json(['message' => 'Slideshow not found.'], 404);
        }

        // TODO: Optionally delete all files/folder from Google Drive

        $slideshow->delete();

        return response()->json(['message' => 'Slideshow deleted.']);
    }
}
