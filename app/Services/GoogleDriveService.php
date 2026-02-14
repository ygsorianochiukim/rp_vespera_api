<?php

namespace App\Services;

use Google\Client as GoogleClient;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class GoogleDriveService
{
    protected GoogleClient $client;
    protected Drive $drive;

    public function __construct()
    {
        $this->client = new GoogleClient();

        $this->client->setAuthConfig(
            storage_path('app/google/oauth-client.json')
        );

        $this->client->setScopes([
            Drive::DRIVE_FILE,
        ]);

        $this->client->setAccessType('offline');

        $tokenPath = storage_path('app/google/token.json');

        if (!file_exists($tokenPath)) {
            throw new \Exception('Google Drive token not found. Run OAuth first.');
        }

        $token = json_decode(file_get_contents($tokenPath), true);
        $this->client->setAccessToken($token);

        if ($this->client->isAccessTokenExpired()) {
            if (!isset($token['refresh_token'])) {
                throw new \Exception('No refresh token available.');
            }

            $this->client->fetchAccessTokenWithRefreshToken(
                $token['refresh_token']
            );

            file_put_contents(
                $tokenPath,
                json_encode($this->client->getAccessToken())
            );
        }

        $this->drive = new Drive($this->client);
    }

    public function createFolder(string $name, ?string $parentId = null)
    {
        $metadata = new DriveFile([
            'name' => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents' => $parentId ? [$parentId] : [],
        ]);

        return $this->drive->files->create($metadata, [
            'fields' => 'id, webViewLink',
        ]);
    }

    public function uploadFiles(array $files, string $folderId): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            $metadata = new DriveFile([
                'name' => $file->getClientOriginalName(),
                'parents' => [$folderId],
            ]);

            $uploaded[] = $this->drive->files->create($metadata, [
                'data' => file_get_contents($file->getRealPath()),
                'mimeType' => $file->getMimeType(),
                'uploadType' => 'multipart',
                'fields' => 'id, name, webViewLink',
            ]);
        }

        return $uploaded;
    }
}
