<?php
namespace AndreInocenti\LaravelFileS3Like\Facades;

use AndreInocenti\LaravelFileS3Like\FileS3Like as LaravelFileS3LikeFileS3Like;
use Illuminate\Support\Facades\Facade;


class FileS3Like extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LaravelFileS3LikeFileS3Like::class;
    }
}