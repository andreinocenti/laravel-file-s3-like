<?php
namespace AndreInocenti\LaravelFileS3Like\Services;

class MimeType{
    private \Mimey\MimeTypes $mimeTypes;
    public function __construct()
    {
        $this->initiateMimeTypeAndCustomMimes();
    }

    private function initiateMimeTypeAndCustomMimes()
    {
        $builder = \Mimey\MimeMappingBuilder::create();
        $builder->add('image/avif', 'avif');
        $this->mimeTypes = new \Mimey\MimeTypes($builder->getMapping());
    }

    public function mimeTypes(): \Mimey\MimeTypes
    {
        return $this->mimeTypes;
    }
}