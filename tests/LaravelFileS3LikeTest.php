<?php

namespace AndreInocenti\LaravelFileS3Like\Tests;

use AndreInocenti\LaravelFileS3Like\FileS3Like;
use AndreInocenti\LaravelFileS3Like\LaravelFileS3LikeServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Google\Cloud\Storage\StorageClient;
use Spatie\GoogleCloudStorage\GoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\GoogleCloudStorageAdapter as LeagueGoogleCloudStorageAdapter;
use League\Flysystem\GoogleCloudStorage\UniformBucketLevelAccessVisibility;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Filesystem as Flysystem;

use function Pest\Laravel\get;

class LaravelFileS3LikeTest extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelFileS3LikeServiceProvider::class,
            \Spatie\GoogleCloudStorage\GoogleCloudStorageServiceProvider::class,
        ];
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

        $keyFilePath = __DIR__ . '/../application_default_credentials.json';
        $keyFile = file_exists($keyFilePath)
            ? json_decode(file_get_contents($keyFilePath), true)
            : null;

        $app['config']->set("filesystems.disks.gcs", [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
            // 'keyFilePath' => __DIR__ . '/../application_default_credentials.json', // Use correct relative path to root
            'key_file' =>  $keyFile, // Use correct relative path to root,
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX'),
            'storage_api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI'), // Optional
            'throw' => true,
            'uniform_bucket_level_access' => true,
            'uniformBucketLevelAccess' => true,
        ]);

        $app->booted(function () use ($app) {
            $app['filesystem']->extend('gcs', function ($app, $config) {
                $storageClient = new StorageClient([
                    'projectId' => $config['project_id'],
                    'keyFile' => $config['key_file'],
                ]);
                $bucket = $storageClient->bucket($config['bucket']);
                $pathPrefix = $config['path_prefix'] ?? '';
                $storageApiUri = $config['storage_api_uri'] ?? null;

                $visibilityHandler = new UniformBucketLevelAccessVisibility();

                $adapter = new LeagueGoogleCloudStorageAdapter(
                    $bucket,
                    $pathPrefix,
                    $visibilityHandler,
                    $storageApiUri
                );

                $flysystem = new Flysystem($adapter, $config);

                return new GoogleCloudStorageAdapter(
                    $flysystem,
                    $adapter,
                    $config,
                    $storageClient
                );
            });
        });
    }
}
