<?php

namespace AndreInocenti\LaravelFileS3Like\DataTransferObjects;

class DiskFile
{
    public function __construct(
        protected string $filepath,
        protected string $filename,
        protected string $url,
        protected ?string $extension = null,
        protected ?string $mime = null,
    ) {
    }

    /**
     * Return the filepath.
     *
     * @return string
     */
    public function getFilepath(): string
    {
        return $this->filepath ?: '';
    }

    /**
     * Returns the filename
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename ?: '';
    }

    /**
     * returns the full file url
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url  ?: '';
    }

    /**
     * returns the file extension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->extension  ?: '';
    }

    /**
     * returns the file mime type
     *
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime  ?: '';
    }
}
