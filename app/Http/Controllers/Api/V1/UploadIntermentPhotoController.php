<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Domain\UploadIntermentPhoto\Models\UploadIntermentPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;



class UploadIntermentPhotoController extends Controller
{

    /**
     * Store new Lapida photos
     */
    public function store(Request $request)
    {
        $request->validate([
            'document_no' => 'required|string|max:50',
            'occupants' => 'required|array|min:1',
            'occupants.*.occupant_name' => 'required|string|max:100',
            'occupants.*.uploader_name' => 'required|string|max:100',
            'occupants.*.photo' => 'required|image|max:10240',
        ]);

        $uploads = [];

        foreach ($request->occupants as $item) {
            // Skip if already exists
            $existing = UploadIntermentPhoto::where('document_no', $request->document_no)
                ->where('occupant', $item['occupant_name'])
                ->first();

            if ($existing) continue;

            // Save file to public folder
            $file = $item['photo'];
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('interment_photos'), $fileName);
            $photoUrl = url('public/interment_photos/' . $fileName);

            $uploads[] = UploadIntermentPhoto::create([
                'document_no' => $request->document_no,
                'occupant' => $item['occupant_name'],
                'uploader_name' => $item['uploader_name'],
                'photo' => $photoUrl,
                'submitted_at' => now(),
                'is_valid' => true,
            ]);
        }

        return response()->json([
            'uploads' => $uploads,
            'message' => 'Photos uploaded successfully.'
        ], 201);
    }

    /**
     * Update existing photo
     */
    public function update(Request $request, $id)
    {
        $upload = UploadIntermentPhoto::findOrFail($id);

        $request->validate([
            'photo' => 'required|image|max:10240',
            'uploader_name' => 'sometimes|string|max:100',
            'is_valid' => 'sometimes|boolean',
        ]);

        // Delete old file if exists
        if ($upload->photo) {
            $oldFile = basename($upload->photo);
            $oldPath = public_path("interment_photos/$oldFile");
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $file = $request->file('photo');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('interment_photos'), $fileName);

        // Store full URL in DB
        $upload->photo = url('public/interment_photos/' . $fileName);
        $upload->fill($request->only(['uploader_name', 'is_valid']));
        $upload->save();

        return response()->json([
            'upload' => $upload,
            'message' => 'Photo updated successfully.'
        ]);
    }

    /**
     * Get all photos by document number
     */
    public function getByDocumentNo($document_no)
    {
        $photos = UploadIntermentPhoto::where('document_no', $document_no)->get();

        // Ensure full URL is returned
        $photos->transform(function ($item) {
            if ($item->photo && !str_contains($item->photo, 'http')) {
                $item->photo = url('public/interment_photos/' . basename($item->photo));
            }
            return $item;
        });

        return response()->json($photos);
    }

    /**
     * Delete photo
     */
    public function destroy($id)
    {
        $upload = UploadIntermentPhoto::findOrFail($id);

        if ($upload->photo) {
            $oldFile = basename($upload->photo);
            $oldPath = public_path("interment_photos/$oldFile");
            if (file_exists($oldPath)) unlink($oldPath);
        }

        $upload->delete();

        return response()->json(['message' => 'Photo deleted successfully.']);
    }


    /**
     * Get interment records for a document_no
     */
    public function getIntermentsCustomerRecordsForUpload($document_no)
    {
        $query = "SELECT
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
            JOIN mp_t_interment_order_occupancy occ ON inter.mp_t_interment_order_id = occ.mp_t_interment_order_id
            JOIN mp_t_interment_order_informants_data info ON occ.mp_t_interment_order_id = info.mp_t_interment_order_id
            WHERE inter.documentno NOT LIKE '%-CA'
              AND inter.documentno NOT LIKE '%DR'
              AND inter.documentno = :document_no
               AND occ.`mp_i_interment_vessel_id` = 3
        ";

        $records = DB::connection('mysql_secondary')->select($query, ['document_no' => $document_no]);

        if (empty($records)) {
            return response()->json(['message' => 'No records found.'], 404);
        }

     $intermentDate = $getIntermentsCustomer_records[0]->date_interment ?? null;
        $expiryDate = Carbon::parse($intermentDate)->addDays(3);
       $now = Carbon::now();


        if ($now->gt($expiryDate)) {
            return response()->json(['message' => 'Link expired.'], 403);
        }

        return response()->json($records);
    }


    public function validatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:10240' // 10MB
        ]);

        $response = Http::attach(
            'photo',
            file_get_contents($request->file('photo')->getRealPath()),
            $request->file('photo')->getClientOriginalName()
        )->post(env('N8N_VALIDATE_URL'));

        if (!$response->successful()) {
            return response()->json([
                'output' => 'Validation service unavailable',
                'is_valid' => false
            ], 500);
        }

        return $response->json();
    }
}
