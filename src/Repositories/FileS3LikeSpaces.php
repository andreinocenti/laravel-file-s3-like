<?php
namespace AndreInocenti\LaravelFileS3Like\Repositories;

use AndreInocenti\LaravelFileS3Like\Contracts\FileS3LikeInterface;
use AndreInocenti\LaravelFileS3Like\DataTransferObjects\DiskFile;
use AndreInocenti\LaravelFileS3Like\FileS3Like;
use AndreInocenti\LaravelFileS3Like\Services\File;
use AndreInocenti\LaravelFileS3Like\Services\MimeType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Mimey\MimeTypes;

class FileS3LikeSpaces extends FileS3Like implements FileS3LikeInterface{
    public function __construct()
    {
        $this->repository = 'spaces';
        $this->repoInstance = $this;
        parent::__construct();
    }

    /**
     * set the repository as spaces
     *
     * @param string $repository
     * @return self
     */
    public function repository($repository = 'spaces'): self
    {
        return $this;
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
        Storage::disk($this->disk)->put($filepath, $file->getFile(), $this->visibility);
        return new DiskFile(
            $filepath,
            $filename,
            $this->cdnEndpoint. '/' . $filepath,
            $file->getExtension(),
            $file->getMime()
        );
    }

    /**
     * Upload and update the file on the space disk, purge cache and return the file url.
     * If a file with the same name already exists, it will be overwritten and  the cache will be purged.
     * If it is a new file, it will only be uploaded and the cnd cache will be generated.
     *
     * @param UploadedFile|string $file - The file can be either a Illuminate\Http\UploadedFile or a base64 string file
     * @param string|null $filename
     * @return DiskFile
     */
    public function save(UploadedFile|string $file, ?string $filename = null): DiskFile
    {
        $file = $this->upload($file, $filename);
        static::purge($file->file);

        return $file;
    }

    /**
     * Purges a CDN cache of a file.
     * $filename - Inform the file name and path of the file to be purged.
     *
     * @param string $fileName
     * @return self
     */
    public function purge(string $filename): self
    {
        $file = $this->directory . '/' . $filename;
        Http::asJson()->delete(
            $this->cdnEndpoint . '/cache',
            [
                'files' => [$file],
            ]
        );
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
        if(is_array($file)){
            foreach($file as $f){
                $this->purge($f);
            }
        }else{
            $this->purge($file);
        }

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

        $expiration = now()->addSeconds($expiration);

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

        $data = Storage::disk($this->disk)->temporaryUploadUrl($filepath, $expiration, [
            'ContentType' => $mime,
            'ACL' => $public ? 'public-read' : 'private',
        ]);
        return new Fluent([
            'presigned_url' => $data['url'],
            'key' => $filepath,
            'public_url' => $this->cdnEndpoint . '/' . $filepath,
            'expires' => $expiration->toDateTimeString(),
            'headers' => $data['headers'],
            'accepted_mime' => $mime,
            'accepted_ext' => $ext,
        ]);
    }


    /**
     * @return string|null
     */
    private function guessMimeFromExtension(string $ext): ?string
    {
        if ($ext === '') {
            return null;
        }

        $mimeTypes = (new MimeType())->mimeTypes();

        $mimes = $mimeTypes->getMimeType($ext);
        return $mimes[0] ?? null;
    }

    /**
     * @return string|null
     */
    private function guessExtensionFromMime(string $mime): ?string
    {
        if ($mime === '') {
            return null;
        }
        $exts = MimeTypes::getDefault()->getExtensions($mime);
        return $exts[0] ?? null;
    }
}