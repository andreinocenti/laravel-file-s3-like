<?php

use AndreInocenti\LaravelFileS3Like\Facades\FileS3LikeSpaces;
use AndreInocenti\LaravelFileS3Like\Services\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\assertTrue;

test('save text file on storage via UploadedFile', function () {
    $filepath = filesPath() . '/test-file.txt';
    $file = new UploadedFile($filepath, 'test.txt', 'text/plain', null, true);
    $filename = 'new-test-' . uniqid();
    $diskFile = FileS3LikeSpaces::disk('spaces')->directory('package_test')->upload($file, $filename);
    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});


test('save image file on storage via UploadedFile', function () {
    $filepath = filesPath() . '/test-file.png';
    $file = new UploadedFile($filepath, 'test.png');
    $filename = 'new-test-' . uniqid();
    $diskFile = FileS3LikeSpaces::disk('spaces')->directory('package_test')->upload($file, $filename);
    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});

test('save text file on storage via BASE64', function () {
    $filepath = filesPath() . '/test-file.txt';
    $filename = 'new-test-' . uniqid();
    $diskFile = FileS3LikeSpaces::disk('spaces')->directory('package_test')->upload(toBase64($filepath), $filename);

    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});

test('save image file on storage via BASE64', function () {
    $filepath = filesPath() . '/test-file.png';
    $filename = 'new-test-' . uniqid();
    $diskFile = FileS3LikeSpaces::disk('spaces')->directory('package_test')->upload(toBase64($filepath), $filename);

    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});


test('test UploadedFile for avif file', function () {
    $filepath = filesPath() . '/test-file.avif';
    $file = new UploadedFile($filepath, 'test.avif');
    $file = new File($file, 'new-file.avif');
    assertTrue($file->getExtension() == 'avif');
    assertTrue($file->getFilename() == 'new-file.avif');
    assertTrue($file->getMime() == 'image/avif');
    assertTrue($file->getFile() == file_get_contents($filepath));
});