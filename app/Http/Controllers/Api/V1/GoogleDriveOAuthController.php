<?php

namespace App\Http\Controllers\Api\V1;

use Google\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GoogleDriveOAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $client = new Client();

        $client->setAuthConfig(
            storage_path('app/google/oauth-client.json')
        );

        $client->setScopes([
            'https://www.googleapis.com/auth/drive.file',
        ]);

        $client->setAccessType('offline');
        $client->setPrompt('consent');

        return redirect($client->createAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $client = new Client();

        $client->setAuthConfig(
            storage_path('app/google/oauth-client.json')
        );

        $client->setScopes([
            'https://www.googleapis.com/auth/drive.file',
        ]);

        $client->setAccessType('offline');

        $token = $client->fetchAccessTokenWithAuthCode(
            $request->get('code')
        );

        if (isset($token['error'])) {
            return response()->json($token, 400);
        }

        file_put_contents(
            storage_path('app/google/token.json'),
            json_encode($token)
        );

        return '✅ Google Drive connected. You can close this tab.';
    }
}
