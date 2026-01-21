<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\UploadReview\Services\UploadReviewService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Domain\UploadReview\Models\UploadReview;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UploadReviewController extends Controller
{
    public function __construct(private UploadReviewService $service) {}

public function submit(Request $request)
{
    // 1️⃣ Validation
    $request->validate([
        'document_no' => 'required|string',
        'reviewer_name' => 'required|string|max:255',
        'contact_number' => 'required|string|max:255',
        'fb_screenshot' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'google_screenshot' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'private_feedback' => 'nullable|string',
        'others' => 'nullable|string',
    ]);

    // 2️⃣ Check max reviews
    $reviewCount = UploadReview::where('document_no', $request->document_no)->count();
        if ($reviewCount >= 4) {
            return response()->json([
                'code' => 'MAX_REVIEWS',
                'message' => 'This service already reached the maximum number of reviews.'
            ], 403);
        }

        if (!$request->hasFile('fb_screenshot') && !$request->hasFile('google_screenshot')) {
            return response()->json([
                'code' => 'SCREENSHOT_REQUIRED',
                'message' => 'At least one screenshot (Facebook or Google) is required.'
            ], 422);
        }

        if (UploadReview::where('document_no', $request->document_no)
            ->where('reviewer_name', $request->reviewer_name)
            ->exists()) {
            return response()->json([
                'code' => 'DUPLICATE_REVIEW',
                'message' => 'You have already submitted feedback for this service.'
            ], 422);
        }


    // 5️⃣ File uploads
    $fbPath = $request->file('fb_screenshot')
        ? $request->file('fb_screenshot')->store('reviews', 'public')
        : null;

    $googlePath = $request->file('google_screenshot')
        ? $request->file('google_screenshot')->store('reviews', 'public')
        : null;

    $fbUrl = $fbPath ? url('/storage/' . $fbPath) : null;
    $googleUrl = $googlePath ? url('/storage/' . $googlePath) : null;

        if (!$request->hasFile('fb_screenshot') && !$request->hasFile('google_screenshot')) {
        return response()->json([
            'message' => 'At least one screenshot (Facebook or Google) is required.'
        ], 422);
    }

                // 6️⃣ Save review
            UploadReview::create([
                'document_no'             => $request->input('document_no'),
                'reviewer_name'           => $request->input('reviewer_name'),
                'contact_number'          => $request->input('contact_number'),

                // ✅ Selected questions from Angular
                'selected_public_question'  => $request->input('selected_public_question'),
                'selected_private_question' => $request->input('selected_private_question'),

                'private_feedback'        => $request->input('private_faq_answer'), // Angular key
                'others'                  => $request->input('privateOthers'),      // Angular key
                'fb_screenshot'           => $fbUrl,
                'google_screenshot'       => $googleUrl,
                'submitted_at'            => now(),
                'is_valid'                => true,
            ]);



    return response()->json(['message' => 'Review submitted successfully.']);
}






        public function getInterments($occupant)
            {
                $query = "SELECT
                bpar.`name1`,
                inter.`documentno`,
                inter.`date_interment`,
                occ.`occupant_name` AS occupant
            FROM mp_t_interment_order inter
            JOIN mp_l_ownership ship USING (mp_l_ownership_id)
            JOIN mp_l_preownership preown USING (mp_l_preownership_id)
            JOIN mp_i_owner owner
                ON preown.`mp_i_owner_id` = owner.`mp_i_owner_id`
            JOIN bpar_i_person bpar
                ON owner.`bpar_i_person_id` = bpar.`bpar_i_person_id`
            JOIN mp_t_interment_order_occupancy occ 
                ON inter.`mp_t_interment_order_id` = occ.`mp_t_interment_order_id`
            WHERE inter.`documentno` NOT LIKE '%-CA'
            AND inter.`documentno` NOT LIKE '%DR'
            AND occ.`occupant_name` = :occupantName
            ";

        $interments = DB::connection('mysql_secondary')
           ->select($query, ['occupantName' => $occupant]);

        if (empty($interments)) {
            return response()->json(['message' => 'No records found.'], 404);
        }

        $intermentDate = $interments[0]->date_interment ?? null;

        if (!$intermentDate) {
            return response()->json(['message' => 'Invalid record.'], 500);
        }

        // Expiration: 10 years after interment date
        $expiryDate = Carbon::parse($intermentDate)->addDays(3);
        $now = Carbon::now();

        if ($now->gt($expiryDate)) {
            return response()->json(['message' => 'Link expired.'], 403);
        }

        return response()->json($interments);
    }



    public function getByDocumentNo($document_no)
    {
        $reviews = UploadReview::where('document_no', $document_no)->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'message' => 'No reviews found for this document number'
            ], 404);
        }

        return response()->json($reviews, 200);
    }



}
