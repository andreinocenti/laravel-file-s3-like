# Laravel Package - Files to S3 Like Cloud Storage

This is a Laravel package that handles and make easy the upload, overwrite, delete, cdn purge of files and directories on AWS S3 like cloud storages.

It supports a form uploaded file (UploadedFile) or a base64 file string


## Support

At the actual version it support only Digital Ocean SPACES cloud storage.

In the future I will add new cloud storages support or accept pull requests.

## Configuration

This package use the Laravel Illuminate\Support\Facades\Storage facade to handle the files.

So you must config the filesystem AWS like disk that you want to use.

Below the optimal config to be used

`config/filesystem.php`
```php
'spaces-disk' => [
    'driver' => 's3',
    'key' => env('SPACES_ACCESS_KEY_ID'),
    'secret' => env('SPACES_SECRET_ACCESS_KEY'),
    'region' => env('SPACES_DEFAULT_REGION'),
    'bucket' => env('SPACES_BUCKET'),
    'url' => env('SPACES_URL'),
    'endpoint' => env('SPACES_ENDPOINT'),
    'folder' => env('SPACES_FOLDER'), // This will be the default directory used. It can be empty, if so the default directory will be the bucket root
    'cdn_endpoint' => env('SPACES_CDN_ENDPOINT'), // at Digital Ocean Spaces the CDN is auto set when a file is uploaded. So set here the cdn_endpoint (edge)
    'use_path_style_endpoint' => env('SPACES_USE_PATH_STYLE_ENDPOINT', false),
    'throw' => false,
],
```

## Usage

You can use the FileS3LikeSpaces facade
```php
use AndreInocenti\LaravelFileS3Like\Facades\FileS3LikeSpaces;

FileS3LikeSpaces::disk('spaces-disk')
    ->directory('images')
    ->upload($file, 'new-test');
```

Or you can use the FileS3Like Facade, if you do you must set the repository() you want to use.
```php
use AndreInocenti\LaravelFileS3Like\Facades\FileS3Like;

FileS3Like::repository('spaces')
    ->disk('spaces-disk')
    ->directory('images')
    ->upload($file, 'new-test');
```


### Methods / Accessors

<table>
<tr>
    <th>Method</th>
    <th>Description</th>
</tr>
<tr>
    <td>repository(string $repository)</td>
    <td>
        The repository is the s3 like service that this package support, that you want to use. Eg.: spaces. Check the [Support list](Support)
    </td>
</tr>
<tr>
    <td>disk(string $disk)</td>
    <td>
        Sets the filesystem disk that the Storage facade will use to handle the files on the cloud storage
    </td>
</tr>
<tr>
    <td>directory(string $directory)</td>
    <td>
        The directory in the cloud storage bucket that will be handled. <b>Important:</b> It will concat with the folder configurated in the file filesystem. EG.: if folder was configured to be "public", and the directory "images", the final path will be `public/images`.
        The folder configuration, is good to have always the same base directory configured, instead of the "directory()" that can change anytime
    </td>
</tr>
<tr>
    <td>upload(UploadedFile|string $file, ?string $filename = null)</td>
    <td>
        Upload the file to the storage.
    </td>
</tr>
<tr>
    <td>save(string $repository)</td>
    <td>
        Upload and update the file on the space disk, purge cache and return the file url.
        If a file with the same name already exists, it will be overwritten and  the cache will be purged.
        If it is a new file, it will only be uploaded.
    </td>
</tr>
<tr>
    <td>purge(string $filepath)</td>
    <td>
        Purges a CDN cache of a file. $filename - Inform the file name and path of the file to be purged.
    </td>
</tr>
<tr>
    <td>delete(string $filepath)</td>
    <td>
        Delete a file from the cloud storage disk
    </td>
</tr>
<tr>
    <td>deleteDirectory(string $filepath)</td>
    <td>
        Recursively delete a directory from the cloud storage disk
    </td>
</tr>
</table>