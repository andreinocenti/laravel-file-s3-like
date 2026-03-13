<?php

namespace AndreInocenti\LaravelFileS3Like\Contracts;

use AndreInocenti\LaravelFileS3Like\DataTransferObjects\DiskFile;

interface StreamableFileS3LikeInterface
{
    /**
     * Upload an already opened readable stream to the configured storage disk.
     *
     * @param resource $stream
     */
    public function uploadStream($stream, string $filename, ?string $mime = null): DiskFile;

    /**
     * Save an already opened readable stream to the configured storage disk.
     *
     * @param resource $stream
     */
    public function saveStream($stream, string $filename, ?string $mime = null): DiskFile;
}
