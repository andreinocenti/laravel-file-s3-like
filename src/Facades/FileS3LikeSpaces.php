<?php

namespace AndreInocenti\LaravelFileS3Like\Facades;

use AndreInocenti\LaravelFileS3Like\Repositories\FileS3LikeSpaces as RepositoriesFileS3LikeSpaces;
use Illuminate\Support\Facades\Facade;


class FileS3LikeSpaces extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RepositoriesFileS3LikeSpaces::class;
    }
}
