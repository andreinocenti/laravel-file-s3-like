<?php

use AndreInocenti\LaravelFileS3Like\Facades\FileS3LikeSpaces;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\assertTrue;

function toBase64($filepath)
{
    return base64_encode(file_get_contents($filepath));
}

test('save text file on storage via UploadedFile', function () {
    $filepath = filesPath() . '/test-file.txt';
    $file = new UploadedFile($filepath, 'test.txt', 'text/plain', null, true);
    $diskFile = FileS3LikeSpaces::disk('spaces')->directory('package_test')->upload($file, 'new-test');
    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});


test('save image file on storage via UploadedFile', function () {
    $filepath = filesPath() . '/test-file.png';
    $file = new UploadedFile($filepath, 'test.png');
    $diskFile = FileS3LikeSpaces::disk('spaces')->directory('package_test')->upload($file, 'new-test');
    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});

test('save text file on storage via BASE64', function () {
    $filepath = filesPath() . '/test-file.txt';
    $diskFile = FileS3LikeSpaces::disk('spaces')->directory('package_test')->upload(toBase64($filepath), 'new-test');

    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});

test('save image file on storage via BASE64', function () {
    $filepath = filesPath() . '/test-file.png';
    $diskFile = FileS3LikeSpaces::disk('spaces')->directory('package_test')->upload(toBase64($filepath), 'new-test');

    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});
