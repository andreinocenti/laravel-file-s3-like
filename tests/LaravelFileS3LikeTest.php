<?php

namespace AndreInocenti\LaravelFileS3Like\Tests;

use AndreInocenti\LaravelFileS3Like\FileS3Like;
use AndreInocenti\LaravelFileS3Like\LaravelFileS3LikeServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\get;

class LaravelFileS3LikeTest extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [LaravelFileS3LikeServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
        $app['config']->set("filesystems.disks.spaces", [
            'driver' => 's3',
            'key' => env('SPACES_ACCESS_KEY_ID'),
            'secret' => env('SPACES_SECRET_ACCESS_KEY'),
            'region' => env('SPACES_DEFAULT_REGION'),
            'bucket' => env('SPACES_BUCKET'),
            'url' => env('SPACES_URL'),
            'endpoint' => env('SPACES_ENDPOINT'),
            'folder' => env('SPACES_FOLDER'),
            'cdn_endpoint' => env('SPACES_CDN_ENDPOINT'),
            'use_path_style_endpoint' => env('SPACES_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ]);

        // $app['config']->set('services.github', [
        //     'client_id' => 'github-client-id',
        //     'client_secret' => 'github-client-secret',
        //     'redirect' => 'http://your-callback-url',
        // ]);
    }
}
