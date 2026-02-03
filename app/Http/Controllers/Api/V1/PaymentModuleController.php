<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\PaymentModule\Models\CustomerModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class PaymentModuleController extends Controller
{
    public function verifyBparName(Request $request)
    {
        $data = $request->validate([
            'firstname'  => 'required|string',
            'middlename' => 'nullable|string',
            'lastname'   => 'required|string',
        ]);
        $firstname  = '%' . strtoupper($data['firstname']) . '%';
        $middlename = $data['middlename']
            ? '%' . strtoupper($data['middlename']) . '%'
            : '%';
        $lastname   = '%' . strtoupper($data['lastname']) . '%';

        $query = "
            SELECT DISTINCT
                bpar.name1,
                loc.phone,
                bpar.bpar_i_person_id,
                own.mp_i_owner_id
            FROM mp_t_purchagr agr
            JOIN mp_i_owner own
                ON agr.mp_i_owner_id = own.mp_i_owner_id
            JOIN bpar_i_person bpar
                ON own.bpar_i_person_id = bpar.bpar_i_person_id
            JOIN bpar_i_person_location loc
                ON bpar.bpar_i_person_id = loc.bpar_i_person_id
            WHERE
                REPLACE(REPLACE(UPPER(bpar.name1), ',', ''), '.', '') LIKE :firstname
                AND REPLACE(REPLACE(UPPER(bpar.name1), ',', ''), '.', '') LIKE :middlename
                AND REPLACE(REPLACE(UPPER(bpar.name1), ',', ''), '.', '') LIKE :lastname
            LIMIT 5
        ";

        $matches = DB::connection('mysql_secondary')->select($query, [
            'firstname'  => $firstname,
            'middlename' => $middlename,
            'lastname'   => $lastname,
        ]);

        if (count($matches) === 1) {
            return response()->json([
                'type' => 'single',
                'data' => $matches[0],
            ], 200);
        }

        if (count($matches) > 1) {
            return response()->json([
                'type' => 'multiple',
                'data' => collect($matches)->pluck('name1'),
            ], 200);
        }

        return response()->json([
            'type'    => 'none',
            'message' => 'No matching records found.',
        ], 404);
    }
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'phone'   => 'required|string',
            'name1'   => 'required|string',
            'bpar'    => 'required',
            'owner'   => 'required',
        ]);

        $phone   = $data['phone'];
        $name1   = $data['name1'];
        $bparId  = $data['bpar'];
        $ownerId = $data['owner'];

        $customer = CustomerModel::where('name1', $name1)
            ->where('phone', $phone)
            ->where('module', 'LSP')
            ->first();

        if ($customer && $customer->isLocked()) {
            return response()->json([
                'success' => false,
                'type'    => 'locked',
                'message' => 'Customer is locked due to too many failed OTP attempts.',
                'retry_in'=> $customer->locked_until->diffForHumans(),
            ], 423);
        }

        $otp       = rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        DB::table('customerOtp')->insert([
            'name1'      => $name1,
            'otp'        => $otp,
            'phone'      => $phone,
            'module'     => 'LSP',
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $apiKey  = "0da664d31027a12eefc386c454dabe3b";
        $message = "Your OTP code is: $otp. It expires in 5 minutes.";
        $senderName ="Valeenland";

        $proxy = env('HTTP_PROXY');
        if ($proxy) {
            $parts = parse_url($proxy);
            if ($parts && isset($parts['user'], $parts['pass'])) {
                $parts['pass'] = urlencode($parts['pass']);
                $proxy = "{$parts['scheme']}://{$parts['user']}:{$parts['pass']}@{$parts['host']}:{$parts['port']}";
            }
        }

        try {
            $http = Http::withHeaders([
                'Accept' => 'application/json',
            ]);

            if ($proxy) {
                $http = $http->withOptions([
                    'proxy'   => $proxy,
                    'timeout' => 10,
                ]);
            }

            $response = $http->post('https://api.semaphore.co/api/v4/messages', [
                'apikey'     => $apiKey,
                'number'     => $phone,
                'message'    => $message,
                'sendername' => $senderName,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'type'    => 'sms_failed',
                    'message' => 'Failed to send OTP via SMS. Please try again later.',
                    'details' => $response->body(),
                ], 500);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'type'    => 'connection_error',
                'message' => 'Could not connect to SMS gateway. Check your proxy or internet connection.',
                'details' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'type'    => 'unexpected_error',
                'message' => 'An unexpected error occurred while sending OTP.',
                'details' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'type'    => 'otp_sent',
            'message' => 'OTP sent successfully.',
            'data'    => [
                'name1'               => $name1,
                'phone'               => $phone,
                'module'              => 'LSP',
                'bpar_i_person_id'    => $bparId,
                'mp_i_owner_id'       => $ownerId,
                'expires_at'          => $expiresAt,
            ],
        ], 200);
    }
    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'name1'  => 'required|string',
            'bpar'   => 'required',
            'owner'  => 'required',
            'otp'    => 'required|string',
            'phone'  => 'nullable|string',
        ]);

        $name1   = $data['name1'];
        $bparId  = $data['bpar'];
        $ownerId = $data['owner'];
        $otp     = $data['otp'];
        $phone   = $data['phone'] ?? '';

        $maxAttempts = 5;
        $lockMinutes = 15;

        $customer = CustomerModel::firstOrCreate(
            [
                'name1'  => $name1,
                'phone'  => $phone,
                'module' => 'LSP',
            ],
            [
                'failed_attempts' => 0,
                'locked_until'    => null,
            ]
        );
        if ($customer->isLocked()) {
            return response()->json([
                'success'  => false,
                'type'     => 'locked',
                'message'  => 'Too many failed attempts.',
                'retry_in' => $customer->locked_until->diffForHumans(),
            ], 423);
        }
        $otpRecord = DB::table('customerOtp')
            ->where('name1', $name1)
            ->where('otp', $otp)
            ->where('module', 'LSP')
            ->where('expires_at', '>=', now())
            ->first();

        if ($otpRecord) {
            return response()->json([
                'success' => true,
                'type'    => 'verified',
                'message' => 'OTP verified successfully.',
                'data'    => [
                    'expires_in'   => '10 minutes',
                ],
            ], 200);
        }
        $customer->incrementAttempts($maxAttempts, $lockMinutes);

        $attemptsLeft = max(0, $maxAttempts - $customer->failed_attempts);

        return response()->json([
            'success'        => false,
            'type'           => $customer->isLocked() ? 'locked' : 'invalid_otp',
            'message'        => $customer->isLocked()
                ? "Too many failed attempts. Locked for $lockMinutes minutes."
                : "Invalid OTP.",
            'attempts_left'  => $attemptsLeft,
            'locked_until'   => $customer->locked_until,
        ], $customer->isLocked() ? 423 : 401);
    }
    public function getOwnerLot($bparId)
    {
        $data = DB::connection('mysql_secondary')->select("
            SELECT
                owners.mp_i_owner_id,
                bparIPerson.bpar_i_person_id,
                CONCAT(lot.area_no,'-',lot.block_no,'-',lot.lot_no) AS lot,
                preown.mp_i_lot_id
            FROM mp_i_owner owners
            INNER JOIN bpar_i_person bparIPerson
                ON bparIPerson.bpar_i_person_id = owners.bpar_i_person_id
            INNER JOIN mp_l_preownership preown
                ON owners.mp_i_owner_id = preown.mp_i_owner_id
            INNER JOIN mp_i_lot lot
                ON preown.mp_i_lot_id = lot.mp_i_lot_id
            WHERE bparIPerson.bpar_i_person_id = ?
        ", [$bparId]);

        return response()->json([
            'success' => true,
            'data'    => $data
        ]);
    }

}