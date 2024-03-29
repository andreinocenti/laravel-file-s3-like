<?php
namespace AndreInocenti\LaravelFileS3Like\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Mimey\MimeTypes;

/**
 * Class to handle the file to be ready to be put in the storage, and handle filename and extension. T
 */
class File{
    private string $extension = '';
    private string $mime = '';
    private MimeTypes $mimeTypes;
    private $file;
    public function __construct(
        protected UploadedFile|string $fileToHandle,
        protected ?string $filename = null
    )
    {
        $this->initiateMimeTypeAndCustomMimes();
        $this->init();
    }

    private function init()
    {
        $this->filename = $this->filename ?: (string) Str::uuid();

        if (gettype($this->fileToHandle) == 'string') {
            $this->base64();
        }else{
            $this->formFile();
        }
    }

    private function formFile()
    {
        $this->file = file_get_contents($this->fileToHandle);
        $this->extension = strtolower($this->fileToHandle->getClientOriginalExtension());
        $this->mime = $this->mimeTypes->getMimeType($this->extension) ?: '';
        $suffix = '.' . $this->extension;
        $this->filename = preg_replace("/$suffix$/", '', $this->filename);
        $this->filename = $this->filename . $suffix;
    }

    private function base64()
    {
        if(substr($this->fileToHandle, 0, 5) == 'data:'){
            $this->fileToHandle = substr($this->fileToHandle, strpos($this->fileToHandle, ',') + 1);
        }
        $mime = $this->base64_mimetype($this->fileToHandle);
        $this->extension = $this->mimeTypes->getExtension($mime) ?: '';
        $this->mime = $this->mimeTypes->getMimeType($this->extension) ?: '';
        $this->filename = $this->filename . '.' . $this->extension;
        $this->file = base64_decode($this->fileToHandle);
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getMime()
    {
        return $this->mime;
    }


    function base64_mimetype(string $encoded, bool $strict = true): ?string
    {
        $decoded = base64_decode($encoded, $strict);
        if ($decoded) {
            $tmpFile = tmpFile();
            $tmpFilename = stream_get_meta_data($tmpFile)['uri'];

            file_put_contents($tmpFilename, $decoded);

            return mime_content_type($tmpFilename) ?: null;
        }

        return null;
    }

    function initiateMimeTypeAndCustomMimes()
    {
        $builder = \Mimey\MimeMappingBuilder::create();
        $builder->add('image/avif', 'avif');
        $this->mimeTypes = new MimeTypes($builder->getMapping());
    }
}