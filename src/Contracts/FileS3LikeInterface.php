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
    public function presignedUrl(
        string $filepath,
        ?string $filename = null,
        int $expiration = 900,
        string|null $fileType = null,
        bool $public = false
    ): Fluent;
}