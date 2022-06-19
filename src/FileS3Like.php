<?php

namespace AndreInocenti\LaravelFileS3Like;

use AndreInocenti\LaravelFileS3Like\Contracts\FileS3LikeInterface;
use AndreInocenti\LaravelFileS3Like\DataTransferObjects\DiskFile;
use AndreInocenti\LaravelFileS3Like\Repositories\FileS3LikeSpaces;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileS3Like implements FileS3LikeInterface
{
    protected ?string $cdnEndpoint = null;
    protected ?string $endpoint = null;
    protected ?string $folder = null;
    protected ?string $disk = null;
    protected ?string $directory = null;
    protected ?string $repository = null;
    protected string $visibility = 'public';
    protected FileS3LikeInterface $repoInstance;

    public function __construct()
    {
        $this->visibility = 'public';
    }

    /**
     * The repository is s3 like service you want to use. Eg.: spaces. Check https://github.com/andreinocenti/laravel-file-s3-like documentation for more info.
     *
     * @param string $repository
     * @return self
     */
    public function repository(string $repository): self
    {
        $this->repository = $repository;
        $this->repoInstance = match($repository){
            'spaces' => new FileS3LikeSpaces(),
            default => throw new \Exception("The repository '$repository' is not supported", 1)
        };
        $this->repoInstance->repository = $repository;
        return $this;
    }

    /**
     * Check if all configs are setup
     *
     * @return boolean
     */
    protected function isAllSetup(): bool
    {
        $defaultMsg = "See the LaravelFileS3Like docs for more info. Access: https://github.com/andreinocenti/laravel-file-s3-like";

        if (!$this->repoInstance || !$this->repository) {
            throw new \Exception("You must set a valid repository before call any other function. $defaultMsg");
        }

        if (!$this->repoInstance->disk) {
            throw new \Exception("You must call the disk() function before other functions. $defaultMsg");
        }

        if (!$this->repoInstance->endpoint) {
            throw new \Exception("The Disk '{$this->repoInstance->disk}' endpoint is not configured. See the LaravelFileS3Like docs for more info. $defaultMsg");
        }

        return true;
    }

    /**
     * The laravel's filesystem.disk name that will be used. This function MUST the called before any other function.
     *
     * @param string $disk
     * @return self
     */
    public function disk(string $disk): self
    {
        $this->repoInstance->disk = $disk;
        $this->repoInstance->endpoint = config("filesystems.disks.$disk.endpoint");

        $this->isAllSetup();
        $this->repoInstance->cdnEndpoint = config("filesystems.disks.$disk.cdn_endpoint");
        $this->repoInstance->cdnEndpoint = $this->repoInstance->cdnEndpoint ?: $this->repoInstance->endpoint;
        $this->repoInstance->folder = config("filesystems.disks.$disk.folder") ?: '';
        $this->repoInstance->directory = $this->repoInstance->folder
            ? $this->repoInstance->folder . ($this->repoInstance->directory ?: '')
            : '';

        return $this;
    }

    /**
     * Sets the directory where the FileS3Like will work from
     *
     * @param string $directory
     * @return self
     */
    public function directory(string $directory): self
    {
        $this->repoInstance->directory = $this->repoInstance->folder
            ? $this->repoInstance->folder . (str_ends_with($this->repoInstance->folder, '/') ? '' : '/') . $directory
            : $directory;
        return $this;
    }

    /**
     * Sets the visibility of the files. The default is public.
     *
     * @param string $visibility
     * @return self
     */
    public function visibility(string $visibility): self
    {
        $this->repoInstance->visibility = $visibility;
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
        $this->isAllSetup();

        return $this->repoInstance->upload($file, $filename);
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
        $this->isAllSetup();
        return $this->repoInstance->save($file, $filename);
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
        $this->isAllSetup();
        return $this->repoInstance->purge($filename);
    }

    /**
     * Delete a file from the s3 like disk
     *
     * @param string|array $file - The file(s) to be deleted.
     * @return self
     */
    public function delete(string|array $file): self
    {
        $this->isAllSetup();
        return $this->repoInstance->delete($file);
    }

    /**
     * Recursively delete a directory from the s3 like disk
     *
     * @return void
     */
    public function deleteDirectory(): self
    {
        $this->isAllSetup();
        return $this->repoInstance->deleteDirectory();
    }

    /**
     * List directories in the directory set
     *
     * @return array
     */
    public function directories(): array
    {
        $this->isAllSetup();
        return Storage::disk($this->repoInstance->disk)->directories($this->repoInstance->directory);
    }

    /**
     * List files in the directory set
     *
     * @return array
     */
    public function files(): array
    {
        $this->isAllSetup();
        return Storage::disk($this->repoInstance->disk)->files($this->repoInstance->directory);
    }
}
