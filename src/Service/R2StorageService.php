<?php
declare(strict_types=1);

namespace App\Service;

use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Cake\Core\Configure;

class R2StorageService
{
    private ?S3Client $client;
    private string $bucket;

    /**
     * R2StorageService constructor.
     */
    public function __construct()
    {
        $r2 = Configure::read('R2');
        if (
            empty($r2['accessKeyId']) ||
            empty($r2['secretAccessKey']) ||
            empty($r2['accountId']) ||
            empty($r2['bucket'])
        ) {
            return;
        }

        $creds = new Credentials($r2['accessKeyId'], $r2['secretAccessKey']);
        $this->bucket = $r2['bucket'];

        $this->client = new S3Client([
            'version' => 'latest',
            'region' => 'auto',
            'endpoint' => "https://{$r2['accountId']}.r2.cloudflarestorage.com",
            'use_path_style_endpoint' => true,
            'credentials' => $creds,
        ]);
    }

    /**
     * Uploads a payload to R2 under the given key, with the given ACL.
     *
     * @param string $key Object key (e.g. "originals/123.jpg")
     * @param string $body Raw bytes or stream resource
     * @return bool True if uploaded; false on error.
     */
    public function put(string $key, string $body): bool
    {
        if (!$this->client) {
            return false;
        }

        $ext = strtolower(pathinfo($key, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        // Pick from the map or fall back
        $contentType = $map[$ext] ?? 'application/octet-stream';

        try {
            $this->client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $body,
                'ContentType' => $contentType,
                'ContentDisposition' => 'inline; filename="' . basename($key) . '"',
                'CacheControl' => 'public, max-age=31536000',
            ]);

            return true;
        } catch (AwsException) {
            return false;
        }
    }

    /**
     * Delete an object from your Cloudflare R2 bucket.
     *
     * @param string $key    The object key to delete (e.g. "originals/123.jpg")
     * @return bool True if deleted or not found; false on error.
     */
    public function delete(string $key): bool
    {
        if (!$this->client) {
            return false;
        }

        try {
            $this->client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);

            return true;
        } catch (AwsException) {
            return false;
        }
    }
}
