<?php

use AndreInocenti\LaravelFileS3Like\Facades\FileS3Like;
use AndreInocenti\LaravelFileS3Like\Services\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\assertTrue;

test('save text file on storage via UploadedFile', function () {
    $filepath = filesPath() . '/test-file.txt';
    $file = new UploadedFile($filepath, 'test.txt', 'text/plain', null, true);
    $filename = 'new-test-' . uniqid();
    $diskFile = FileS3Like::repository('spaces')->disk('spaces')->directory('package_test')->upload($file, $filename);
    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});


test('save image file on storage via UploadedFile', function () {
    $filepath = filesPath() . '/test-file.png';
    $file = new UploadedFile($filepath, 'test.png');
    $filename = 'new-test-' . uniqid();
    $diskFile = FileS3Like::repository('spaces')->disk('spaces')->directory('package_test')->upload($file, $filename);
    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});

test('save text file on storage via BASE64', function () {
    $filepath = filesPath() . '/test-file.txt';
    $filename = 'new-test-' . uniqid();
    $diskFile = FileS3Like::repository('spaces')->disk('spaces')->directory('package_test')->upload(toBase64($filepath), $filename);

    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});

test('save image file on storage via BASE64', function () {
    $filepath = filesPath() . '/test-file.png';
    $filename = 'new-test-' . uniqid();
    $diskFile = FileS3Like::repository('spaces')->disk('spaces')->directory('package_test')->upload(toBase64($filepath), $filename);

    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($filepath));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});

test('save image from url', function () {
    $url = 'https://laravel.com/img/logomark.min.svg';

    $diskFile = FileS3Like::repository('spaces')->disk('spaces')->directory('package_test')->upload($url);

    assertTrue(Storage::disk('spaces')->exists($diskFile->getFilepath()));
    assertTrue(file_get_contents($diskFile->getUrl()) == file_get_contents($url));
    Storage::disk('spaces')->delete($diskFile->getFilepath());
});


test('Create and use a Presigned URL for a PNG file with typeFile', function () {
    $filepath = 'test-dir/10';
    $presigned = FileS3Like::repository('spaces')->disk('spaces')->directory('package_test')->presignedUrl(
        filepath: $filepath,
        expiration: 600,
        fileType: 'image/png',
        public: true
    );

    // PNG 1x1 transparente (base64) — evita depender de GD/Imagick
    $pngBinary = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQI12P4//8/AwAI/AL+Xkz8WQAAAABJRU5ErkJggg=='
    );

    // Act: upload via presigned URL (PUT)
    // Use os headers devolvidos na assinatura (tipicamente inclui "Content-Type")
    $url = $presigned->presigned_url;
    $response = Http::withHeaders($presigned->headers)
        ->withBody($pngBinary, 'image/png')
        ->put($url);

    // Assert: status 2xx
    expect($response->successful())
        ->toBeTrue("Upload failed: HTTP {$response->status()} - {$response->body()}");

    // Assert: o objeto existe no bucket
    $disk = Storage::disk('spaces');

    expect($url)->not->toBeNull('Presigned did not return the object url');

    expect($disk->exists($presigned->key))
        ->toBeTrue("Object not found after upload: {$presigned->key}");

    // Cleanup: deletar e confirmar remoção
    expect($disk->delete($presigned->key))->toBeTrue("Failed to delete {$presigned->key}");

    // Algumas vezes listagem pode ter latência; mas `exists` no S3-like normalmente reflete imediato
    expect($disk->exists($presigned->key))->toBeFalse("Object still exists after delete: {$presigned->key}");
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