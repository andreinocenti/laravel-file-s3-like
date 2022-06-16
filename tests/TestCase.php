<?php

namespace AndreInocenti\LaravelFileS3Like\Tests;

use AndreInocenti\LaravelFileS3Like\LaravelFileS3LikeServiceProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use CreatesApplication;
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            LaravelFileS3LikeServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}
