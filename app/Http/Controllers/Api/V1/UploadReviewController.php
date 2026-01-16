<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\UploadReview\Services\UploadReviewService;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Domain\UploadReview\Models\UploadReview;

class UploadReviewController extends Controller
{
    public function __construct(private UploadReviewService $service) {}

    public function submit(Request $request)
    {
        $request->validate([
            'document_no' => 'required|string',
            'reviewer_name' => 'required|string',
            'q1' => 'required|string',
            'q2' => 'required|string',
            'q3' => 'required|string',
            'q4' => 'required|string',
            'q5' => 'required|string',
            'q6' => 'required|string',
            'fb_screenshot' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'google_screenshot' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        ]);

        $fbPath = $request->hasFile('fb_screenshot') ? $request->file('fb_screenshot')->store('reviews', 'public') : null;
        $googlePath = $request->hasFile('google_screenshot') ? $request->file('google_screenshot')->store('reviews', 'public') : null;

        UploadReview::create([
            'document_no' => $request->document_no,
            'reviewer_name' => $request->reviewer_name,
            'q1' => $request->q1,
            'q2' => $request->q2,
            'q3' => $request->q3,
            'q4' => $request->q4,
            'q5' => $request->q5,
            'q6' => $request->q6,
            'others' => $request->others,
            'fb_username' => $request->fb_username,
            'google_username' => $request->google_username,
            'fb_screenshot_path' => $fbPath,
            'google_screenshot_path' => $googlePath,
            'submitted_at' => now(),
            'is_valid' => true,
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
          AND occ.`occupant_name` = :occupantName";

        $interments = DB::connection('mysql_secondary')
            ->select($query, ['occupantName' => $occupant]);

        return response()->json($interments);
    }
}
