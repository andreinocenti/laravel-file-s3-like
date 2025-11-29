<?php

namespace AndreInocenti\LaravelFileS3Like\Contracts;

use AndreInocenti\LaravelFileS3Like\DataTransferObjects\DiskFile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Fluent;

interface FileS3LikeInterface
{
    public function upload(UploadedFile|string $file, ?string $filename = null): DiskFile;
    public function save(UploadedFile|string $file, ?string $filename = null): DiskFile;
    public function purge(string $filename): FileS3LikeInterface;
    public function delete(string|array $file): FileS3LikeInterface;
    public function deleteDirectory(): FileS3LikeInterface;
    public function repository(string $repository): FileS3LikeInterface;
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
        bool $public = false
    ): Fluent;

    /**
     * Check if all configs are setup
     *
     * @return boolean
     */
    public function isAllSetup(): bool;
}