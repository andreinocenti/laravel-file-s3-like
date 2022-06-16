<?php
namespace AndreInocenti\LaravelFileS3Like\Repositories;

use AndreInocenti\LaravelFileS3Like\Contracts\FileS3LikeInterface;
use AndreInocenti\LaravelFileS3Like\DataTransferObjects\DiskFile;
use AndreInocenti\LaravelFileS3Like\FileS3Like;
use AndreInocenti\LaravelFileS3Like\Services\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


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
            $file->getExtension()
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
}