<?php
namespace AndreInocenti\LaravelFileS3Like\Facades;

use Illuminate\Support\Facades\Facade;


class FileS3Like extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'file-s3-like';
    }
}