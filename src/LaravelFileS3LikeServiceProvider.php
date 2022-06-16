<?php
namespace AndreInocenti\LaravelFileS3Like;

use AndreInocenti\LaravelFileS3Like\Repositories\FileS3LikeSpaces;
use Illuminate\Support\ServiceProvider;

class LaravelFileS3LikeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(FileS3Like::class, function ($app) {
            return new FileS3Like();
        });
        $this->app->bind(FileS3LikeSpaces::class, function ($app) {
            return new FileS3LikeSpaces();
        });
    }

    public function boot() {
    }
}