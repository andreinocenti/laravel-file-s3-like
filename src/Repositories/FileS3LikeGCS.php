<?php
namespace AndreInocenti\LaravelFileS3Like\Repositories;

use AndreInocenti\LaravelFileS3Like\Contracts\FileS3LikeInterface;
use AndreInocenti\LaravelFileS3Like\DataTransferObjects\DiskFile;
use AndreInocenti\LaravelFileS3Like\FileS3Like;
use AndreInocenti\LaravelFileS3Like\Services\File;
use AndreInocenti\LaravelFileS3Like\Services\MimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Mimey\MimeTypes;

class FileS3LikeGCS extends FileS3Like implements FileS3LikeInterface{
    public function __construct()
    {
        $this->repository = 'gcs';
        $this->repoInstance = $this;
        parent::__construct();
        // Override default visibility to null for GCS to support Uniform Bucket-Level Access
        $this->visibility = null;
    }

    /**
     * set the repository as gcs
     *
     * @param string $repository
     * @return self
     */
    public function repository($repository = 'gcs'): self
    {
        return $this;
    }

    /**
     * Check if all configs are setup
     *
     * @return boolean
     */
    public function isAllSetup(): bool
    {
        $defaultMsg = "See the LaravelFileS3Like docs for more info. Access: https://github.com/andreinocenti/laravel-file-s3-like";

        if (!$this->disk) {
            throw new \Exception("You must call the disk() function before other functions. $defaultMsg");
        }

        // GCS doesn't require endpoint/key/secret in the same way Spaces does if using ADC.
        // We could check for project_id or bucket, but those are disk driver details.
        // For this abstraction, having a disk selected is the primary requirement.

        return true;
    }

    /**
     * Upload a file to the storage.
     *
     * @param UploadedFile|string $file - The file can be either a Illuminate\Http\UploadedFile or a base64 string file
     * @return DiskFile
     */
    public function upload(UploadedFile|string $file, ?string $filename = null): DiskFile
    {
        $file = new File($file, $filename);
        $filename = $file->getFilename();
        $filepath = "{$this->directory}/{$filename}";
        $options = $this->visibility ? $this->visibility : [];
        Storage::disk($this->disk)->put($filepath, $file->getFile());


        return new DiskFile(
            $filepath,
            $filename,
            $this->publicUrl($filepath),
            $file->getExtension(),
            $file->getMime()
        );
    }

    public function publicUrl(string $filepath): string
    {
        return $this->cdnEndpoint
            ? rtrim($this->cdnEndpoint, '/') . '/' . $filepath
            : Storage::disk($this->disk)->url($filepath);
    }

    /**
     * Upload and update the file on the space disk, purge cache and return the file url.
     *
     * @param UploadedFile|string $file - The file can be either a Illuminate\Http\UploadedFile or a base64 string file
     * @param string|null $filename
     * @return DiskFile
     */
    public function save(UploadedFile|string $file, ?string $filename = null): DiskFile
    {
        // For GCS, save is effectively an upload since we don't have a simple purge API
        return $this->upload($file, $filename);
    }

    /**
     * Purges a CDN cache of a file.
     * Note: GCS doesn't have a simple purge API compatible with S3/Spaces XML API.
     * Use Cloud CDN invalidation separately if needed.
     *
     * @param string $fileName
     * @return self
     */
    public function purge(string $filename): self
    {
        // No-op for GCS
        return $this;
    }

    /**
     * Delete a file from the s3 like disk
     *
     * @param string|array $file - The file(s) to be deleted.
     * @return self
     */
    public function delete(string|array $file): self
    {
        Storage::disk($this->disk)->delete($file);
        return $this;
    }

    /**
     * Recursively delete a directory from the s3 like disk
     *
     * @return self
     */
    public function deleteDirectory(): self
    {
        Storage::disk($this->disk)->deleteDirectory($this->directory);
        return $this;
    }

    /**
     * Create a presigned URL for a file in the s3 like disk
     * $fileType eg: 'jpg', 'video/mp4', 'image/*', null (default)
     *
     * @param string $fileName - The path to the file
     * @param string $fileName - The filename, without the path. If null, a UUID will be generated
     * @param int $expiration - The expiration time in seconds (default 900 seconds)
     * @param string|null $fileType - The file type(s) to be allowed for the presigned URL
     * @return Fluent - The presigned URL
     */
    public function presignedUrl(
        string $filepath,
        ?string $filename = null,
        int $expiration = 900,
        string|null $fileType = null,
        bool $public = false,
    ): Fluent
    {
        $this->isAllSetup();
        // build filename
        $filename = $filename ?: (string)Str::uuid();

        $expirationTimestamp = now()->addSeconds($expiration);

        // Infer extension and mime
        $mimeTypes = (new MimeType())->mimeTypes();
        $mime = '';
        $ext = '';
        if($fileType){
            if (str_contains($fileType, '/')) {
                // É MIME
                $ext = $mimeTypes->getExtension(strtolower($fileType));
                $mime = strtolower($fileType);
            } else {
                // É extensão
                $ext  = ltrim(strtolower($fileType), '.');
                $mime = $mimeTypes->getMimeType($ext) ?: '';
            }
        }else{
            $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));
            $mime = $mimeTypes->getMimeType($ext) ?: '';
        }

        // change/add extension to filename
        // check if filename has extension
        if(!str_ends_with($filename, '.' . $ext)){
            $filename = preg_replace('/\.[^.]+$/', '', $filename); // remove existing extension
            $filename .= '.' . $ext; // add new extension
        }
        // check if last chart of filepath is /
        if(!str_ends_with($filepath, '/')){
            $filepath = rtrim($filepath, '/');
        }
        $filepath = "{$this->directory}/$filepath/{$filename}";

        // Get the underlying Google Cloud Storage adapter and bucket
        // The disk must be configured with driver 'gcs'
        $disk = Storage::disk($this->disk);

        // Use Laravel's standard temporaryUrl method if available
        // This delegates to the adapter's temporaryUrl method
        $options = [
            'method' => 'PUT',
            'contentType' => $mime,
            'version' => 'v4', // Optional
        ];

        $signedUrl = $disk->temporaryUrl($filepath, $expirationTimestamp, $options);

        return new Fluent([
        'presigned_url' => $signedUrl,
            'key' => $filepath,
            'public_url' => $this->publicUrl($filepath),
            'expires' => $expirationTimestamp->toDateTimeString(),
            'headers' => [
                'Content-Type' => $mime,
            ],
            'accepted_mime' => $mime,
            'accepted_ext' => $ext,
        ]);
    }
}
