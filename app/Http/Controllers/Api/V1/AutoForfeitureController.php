<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\AutoForfeiture\Services\AutoForfeitureService;
use App\Domain\AutoForfeiture\Services\ForfeitureLineService;
use App\Domain\AutoForfeiture\Services\ForfeitureSigneeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutoForfeitureController extends Controller
{
    public function __construct(
        protected AutoForfeitureService $service,
        protected ForfeitureLineService $lineService,
        protected ForfeitureSigneeService $signeeService,
        protected ForfeitureSigneeService $signeeServicePR,
    ) {}

    public function index()
    {
        return response()->json(
            $this->service->list(),
        );
    }
    public function lineIndex()
    {
        return response()->json(
            $this->lineService->list(),
        );
    }
    public function signeeIndex()
    {
        return response()->json(
            $this->signeeService->list()
        );
    }


    public function readGoogleSheet()
    {
        $sheetId = '1c81o3OvnAeeAcGdqYR_ti_fxKi93VR9lyjhkYhLySQM';
        $gid = 0;
        $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";
        $csv = file_get_contents($url);

        if ($csv === false) {
            abort(500, 'Unable to read Google Sheet');
        }

        $lines = explode("\n", trim($csv));
        $sheetDocs = [];
        foreach (array_slice($lines, 1) as $line) {
            $row = str_getcsv($line);
            if (isset($row[2]) && !empty($row[2])) {
                $sheetDocs[] = "'" . addslashes($row[2]) . "'";
            }
        }

        // If empty, put a dummy value to avoid SQL error
        if (empty($sheetDocs)) {
            $sheetDocs[] = "''";
        }

        // Join as a comma-separated string for SQL
        $notInList = implode(',', $sheetDocs);

        return response()->json($notInList);
    }

    public function getAgedData()
    {
        // 1. Read Google Sheet
        $sheetId = '1fzhtTYuifG4_RNd200WyMjTD0a5RkjuWU9Vf2Hm2wXw';
        $sheetId2 = '1c81o3OvnAeeAcGdqYR_ti_fxKi93VR9lyjhkYhLySQM';
        $gid = 0;
        $url = "https://docs.google.com/spreadsheets/d/{$sheetId}/export?format=csv&gid={$gid}";
        $url2 = "https://docs.google.com/spreadsheets/d/{$sheetId2}/export?format=csv&gid={$gid}";
        $csv = file_get_contents($url);
        $csv2 = file_get_contents($url2);

        if ($csv === false) {
            abort(500, 'Unable to read Google Sheet');
        }

        if ($csv2 === false) {
            abort(500, 'Unable to read Google Sheet');
        }

        $lines = explode("\n", trim($csv));
        $lines2 = explode("\n", trim($csv2));
        $sheetDocs = [];
        $sheetDocs2 = [];
        foreach (array_slice($lines, 1) as $line) {
            $row = str_getcsv($line);
            if (isset($row[4]) && !empty($row[4])) {
                $sheetDocs[] = "'" . addslashes($row[4]) . "'";
            }
        }
        foreach (array_slice($lines2, 1) as $line2) {
            $row2 = str_getcsv($line2);
            if (isset($row2[1]) && !empty($row2[1])) {
                $sheetDocs2[] = "'" . addslashes($row2[1]) . "'";
            }
        }

        // If empty, put a dummy value to avoid SQL error
        if (empty($sheetDocs)) {
            $sheetDocs[] = "''";
        }
        if (empty($sheetDocs2)) {
            $sheetDocs2[] = "''";
        }

        // Join as a comma-separated string for SQL
        $notInList = implode(',', $sheetDocs);
        $existOnList = implode(',', $sheetDocs2);

        // 2. Your query with NOT IN
        $query = "
    SELECT
    MAX(agePay.name1) AS name1,
    MAX(agePay.mp_l_preownership_id) AS mp_l_preownership_id,
    MAX(agePay.is_owned) AS is_owned,
    MAX(agePay.mp_i_owner_id) AS mp_i_owner_id,
    MAX(agePay.mp_s_owner_id) AS mp_s_owner_id,
    MAX(agePay.mp_i_lot_id) AS mp_i_lot_id,
    MAX(agePay.is_status) AS is_status,
    MAX(agePay.documentno) AS documentno,
    MAX(agePay.lotID) AS lotID,
    MAX(agePay.reference) AS reference,
    MAX(agePay.date_of_payment) AS date_of_payment,
    SUM(agePay.amort_sales) AS amort_sales,
    SUM(agePay.amort_pcf) AS amort_pcf,
    SUM(agePay.amort_vat) AS amort_vat,
    SUM(agePay.dateRangebalance) AS dateRangebalance,
    MAX(agePay.ageDesc) AS ageDesc,
    MAX(agePay.amtunpaid) AS OB
FROM (
    SELECT
        bpar.name1,
        agr.docstatus AS is_status,
        preown.mp_l_preownership_id,
        preown.is_owned,
        preown.mp_i_owner_id,
        lot.mp_i_lot_id,
        owners.mp_s_owner_id,
        CONCAT('RP-LSP-', lot.area_no, LPAD(lot.block_no, 2, '0'), LPAD(lot.lot_no, 3, '0')) AS reference,
        IFNULL(docT.documentno_pr, agr.documentno) AS documentno,
        CONCAT(lot.area_no, '-', lot.block_no, '-', lot.lot_no) AS lotID,
        breakdown.date_of_payment,
        IFNULL(breakdown.amt_amort_sales,0) - IFNULL(breakdown.amt_amort_sales_used,0) AS amort_sales,
        (IFNULL(breakdown.amt_amort,0) - IFNULL(breakdown.amt_amort_used,0)) * 0.1 AS amort_pcf,
        ROUND((IFNULL(breakdown.amt_amort,0) - IFNULL(breakdown.amt_amort_used,0)) * 0.9 / 1.12 * 0.12, 2) AS amort_vat,
        (IFNULL(breakdown.amt_amort,0) - IFNULL(breakdown.amt_amort_used,0)) AS dateRangebalance,
        IF(
            DATEDIFF(CURDATE(), breakdown.date_of_payment) < 0, 'Current',
            IF(
                DATEDIFF(CURDATE(), breakdown.date_of_payment) BETWEEN 0 AND 30, '0-30 DAYS',
                IF(
                    DATEDIFF(CURDATE(), breakdown.date_of_payment) BETWEEN 31 AND 60, '31-60 DAYS',
                    IF(
                        DATEDIFF(CURDATE(), breakdown.date_of_payment) BETWEEN 61 AND 90, '61-90 DAYS',
                        '90 DAYS OVER'
                    )
                )
            )
        ) AS ageDesc,
        preown.amtcontract - ROUND(
            IFNULL(preown.total_sales,0)
            + (IFNULL(preown.amt_transferred,0)*1.12/0.9)
            + IFNULL(preown.total_vat,0)
            + IFNULL(preown.total_pcf,0)
            + IFNULL(preown.total_discount,0)
            + (IFNULL(preown.amt_waived,0)*1.12/0.9), 2
        ) AS amtunpaid
    FROM mp_l_pre_ownership_future_pmt_breakdown AS breakdown
    INNER JOIN mp_l_preownership AS preown
        ON preown.mp_l_preownership_id = breakdown.mp_l_preowership_id
    INNER JOIN mp_i_owner AS owners
        ON owners.mp_i_owner_id = preown.mp_i_owner_id
    INNER JOIN bpar_i_person AS bpar
        ON owners.bpar_i_person_id = bpar.bpar_i_person_id
    INNER JOIN mp_i_lot AS lot
        ON lot.mp_i_lot_id = preown.mp_i_lot_id
    INNER JOIN mp_t_purchagr AS agr
        ON agr.mp_t_purchagr_id = preown.mp_t_purchagr_id
    LEFT JOIN doc_t_reference_number AS docT
        ON docT.doc_t_reference_number_id = agr.doc_t_reference_number_id
    WHERE DATE(breakdown.date_of_payment) <= CURDATE()
        AND breakdown.is_paid = 0
        AND preown.amtcontract > 0
        AND (preown.is_cancelled IS FALSE OR preown.is_cancelled IS NULL)
        AND (preown.is_forfeited IS NULL OR preown.is_forfeited IS FALSE)
) AS agePay
WHERE agePay.dateRangebalance > 0
    AND agePay.ageDesc = '90 DAYS OVER'
    AND agePay.is_status != 'LCK'
    AND agePay.amtunpaid > 5
    AND agePay.documentno NOT IN ($notInList)
    AND agePay.documentno NOT IN ($existOnList)
GROUP BY agePay.mp_i_lot_id, agePay.ageDesc
    ";

        $results = DB::connection('mysql_secondary')->select($query);

        return response()->json($results);
    }

    public function getPreownDue(Request $request)
    {
        $data = $request->validate([
            'mp_l_preownership_id'           => 'required|integer',
        ]);
        $query = "SELECT
                 SUM(futurePaymentbreakDown.amt_amort-futurePaymentbreakDown.amt_amort_used) AS dueBalance,
                 SUM(futurePaymentbreakDown.amt_amort_sales-futurePaymentbreakDown.amt_amort_sales_used) AS dueSalesBalance
                 FROM mp_l_pre_ownership_future_pmt_breakdown futurePaymentbreakDown
                 INNER JOIN mp_l_preownership preown ON preown.mp_l_preownership_id = futurePaymentbreakDown.mp_l_preowership_id
                 WHERE futurePaymentbreakDown.date_of_payment<=DATE(NOW())
                 AND preown.mp_l_preownership_id =  :preownId
                 GROUP BY preown.mp_l_preownership_id;";

        $result = DB::connection('mysql_secondary')->select($query, ['preownId' => $data['mp_l_preownership_id']]);

        return response()->json($result);
    }
    public function saveToDocTReference()
    {
        return DB::connection('mysql_secondary')->transaction(function () {

            // 1. Get next DR number
            $latest = DB::connection('mysql_secondary')
                ->table('doc_t_reference_number')
                ->selectRaw("CAST(SUBSTRING(documentno_dr, 5, LENGTH(documentno_dr) - 6) AS UNSIGNED) AS number_part")
                ->where('documentno_dr', 'like', 'NFFT%DR')
                ->orderByDesc('date_draft')
                ->lockForUpdate()
                ->first();

            $nextNumber = ($latest->number_part ?? 0) + 1;

            $documentNoDr = 'NFFT' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT) . 'DR';
            $docISubmodId = DB::connection('mysql_secondary')
                ->table('doc_i_submod')
                ->where('submodule_code', 'FFT')
                ->value('doc_i_submod_id');
            // 2. Insert reference number
            $referenceId = DB::connection('mysql_secondary')
                ->table('doc_t_reference_number')
                ->insertGetId([
                    'doc_i_submod_id' => $docISubmodId,
                    'documentno_dr' => $documentNoDr,
                    'date_draft'    => now(),
                    'ad_org_id'     => '162012',
                    'date_created'  => now(),
                    'created'       => 'System Auto Forfeited',
                    'is_active'     => 1,
                ]);

            return response()->json([
                'reference_id' => $referenceId,
                'documentno_dr' => $documentNoDr,
                'doc_i_submod_id' => $docISubmodId
            ]);
        });
    }

    public function saveToForfeiture(Request $request)
    {
        $data = $request->validate([
            'doc_i_submod_id'           => 'required|integer',
            'documentno'                => 'required|string',
            'mp_s_owner_id'             => 'required|integer',
            'doc_t_reference_number_id' => 'required|integer',
            'mp_i_owner_id'             => 'required|integer',
        ]);

        $forfeiture = $this->service->create($data);

        return response()->json([
            'forfeiture_id' => $forfeiture,
        ], 201);
    }

    public function saveToForfeitureLine(Request $request)
    {
        $data = $request->validate([
            'mp_t_lotforfeiture_id' => 'required|integer',
            'mp_l_preownership_id'  => 'required|integer',
            'date_last_payment'     => 'required|date',
            'amt_overdue_sales'     => 'required|numeric',
            'amt_sales'             => 'required|numeric',
        ]);

        $forfeitureLineId = $this->lineService->create($data);

        return response()->json([
            'forfeiture_id' => $forfeitureLineId,
        ], 201);
    }
    public function saveToForfeitureSignee(Request $request)
    {
        $data = $request->validate([
            'mp_t_lotforfeiture_id' => 'required|integer',
            'usercode'              => 'required|integer',
            'bpar_i_person_id'      => 'required|integer',
        ]);

        $signeeId = $this->signeeService->create($data);

        return response()->json([
            'forfeiture_signee_id' => $signeeId,
        ], 201);
    }
    public function saveToForfeitureSigneePR(Request $request)
    {
        $data = $request->validate([
            'mp_t_lotforfeiture_id' => 'required|integer',
            'usercode'              => 'required|integer',
            'bpar_i_person_id'      => 'required|integer',
        ]);

        $signeeId = $this->signeeService->createPR($data);

        return response()->json([
            'forfeiture_signee_id' => $signeeId,
        ], 201);
    }

    // UPDATE FUNCTIONS

    public function updateToDocTReference(int $docTReferenceId)
    {

        return DB::connection('mysql_secondary')->transaction(function () use ($docTReferenceId) {

            // 1. Get next DR number
            $latest = DB::connection('mysql_secondary')
                ->table('doc_t_reference_number')
                ->selectRaw("CAST(SUBSTRING(documentno_pr, 5) AS UNSIGNED) AS number_part")
                ->where('documentno_pr', 'like', 'NFFT%')
                ->orderByDesc('date_process')
                ->lockForUpdate()
                ->first();

            $nextNumber = ($latest->number_part ?? 0) + 1;
            $documentNoPr = 'NFFT' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

            // 2. Insert reference number
            DB::connection('mysql_secondary')
                ->table('doc_t_reference_number')
                ->where('doc_t_reference_number_id', $docTReferenceId)
                ->update([
                    'documentno_pr' => $documentNoPr,
                    'date_process'  => now()
                ]);

            return response()->json([
                'doc_t_reference_number_id' => $docTReferenceId,
                'documentno_pr'             => $documentNoPr,
            ]);
        });
    }
    public function updateToForfeiture(Request $request)
    {
        $data = $request->validate([
            'mp_t_lotforfeiture_id' => 'required|integer',
            'documentno'            => 'required|string',
        ]);

        return DB::connection('mysql_secondary')->transaction(function () use ($data) {

            // 1. Update the row
            DB::connection('mysql_secondary')
                ->table('mp_t_lotforfeiture')
                ->where('mp_t_lotforfeiture_id', $data['mp_t_lotforfeiture_id'])
                ->update([
                    'docstatus'    => 'PR',
                    'documentno'   => $data['documentno'],
                    'date_updated' => now(),
                    'updated'      => 'System Auto Updated',
                ]);

            // 2. Fetch the updated row to return
            $updatedRow = DB::connection('mysql_secondary')
                ->table('mp_t_lotforfeiture as f')
                ->leftJoin('mp_t_lotforfeiture_line as l', 'f.mp_t_lotforfeiture_id', '=', 'l.mp_t_lotforfeiture_id')
                ->where('f.mp_t_lotforfeiture_id', $data['mp_t_lotforfeiture_id'])
                ->select(
                    'f.mp_t_lotforfeiture_id as mp_t_lotforfeiture_id',
                    'f.documentno',
                    'f.docstatus',
                    'l.mp_l_preownership_id as line_preownership_id',
                )
                ->first();

            return response()->json([
                'mp_t_lotforfeiture_id' => $updatedRow->mp_t_lotforfeiture_id,
                'mp_l_preownership_id'  => $updatedRow->line_preownership_id,
                'documentno'            => $updatedRow->documentno,
                'docstatus'             => $updatedRow->docstatus,
            ]);
        });
    }
    public function updateToPreownership(Request $request)
    {
        $data = $request->validate([
            'mp_l_preownership_id' => 'required|integer',
        ]);

        return DB::connection('mysql_secondary')->transaction(function () use ($data) {

            // 1. Update the row
            DB::connection('mysql_secondary')
                ->table('mp_l_preownership')
                ->where('mp_l_preownership_id', $data['mp_l_preownership_id'])
                ->update([
                    'is_forfeited'    => true,
                    'date_forfeited' => now(),
                ]);

            // 2. Fetch the updated row to return
            $updatedRow = DB::connection('mysql_secondary')
                ->table('mp_l_preownership')
                ->where('mp_l_preownership_id', $data['mp_l_preownership_id'])
                ->select(
                    'mp_i_lot_id',
                )
                ->first();

            return response()->json([
                'mp_i_lot_id' => $updatedRow->mp_i_lot_id,
            ]);
        });
    }
    public function updateToLot(Request $request)
    {
        $data = $request->validate([
            'mp_i_lot_id' => 'required|integer',
        ]);

        return DB::connection('mysql_secondary')->transaction(function () use ($data) {

            // 1. Update the row
            DB::connection('mysql_secondary')
                ->table('mp_i_lot')
                ->where('mp_i_lot_id', $data['mp_i_lot_id'])
                ->update([
                    'status_code'    => "AVL",
                    'is_reserved' => false,
                    'is_preowned' => false,
                    'is_owned' => false,
                ]);

            return response()->json([
                'message'           => 'Forfeiture updated successfully.',
            ]);
        });
    }
}
