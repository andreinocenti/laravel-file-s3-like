# Laravel Package - Files to S3 Like Cloud Storage

This is a Laravel package that handles and make easy the upload, overwrite, delete, cdn purge of files and directories on AWS S3 like cloud storages.

It supports sending a file URL, a form uploaded file (UploadedFile) or a base64 file string.

It supports generating a presigned url.

## Support

Currently supports:
- **Digital Ocean SPACES**
- **Google Cloud Storage (GCS)**

In the future I will add new cloud storages support or accept pull requests.

## Configuration

You should install it via composer:

`composer require andreinocenti/laravel-file-s3-like`

### 1. Digital Ocean Spaces

This package use the Laravel Illuminate\Support\Facades\Storage facade to handle the files.

So you must config the filesystem AWS like disk that you want to use.

Below the optimal config to be used in `config/filesystem.php`:

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

### 2. Google Cloud Storage (GCS)

To use GCS, you need to install the required dependencies:

`composer require google/cloud-storage spatie/laravel-google-cloud-storage`

Then configure the disk in `config/filesystem.php`:

```php
'gcs' => [
    'driver' => 'gcs',
    'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'your-project-id'),
    'key_file' => env('GOOGLE_APPLICATION_CREDENTIALS'), // Path to service account json file
    'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'your-bucket'),
    'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', null), // Optional: prefix for all files
    'storage_api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI', null), // Optional: custom API URI
    'api_endpoint' => env('GOOGLE_CLOUD_STORAGE_API_ENDPOINT', null), // Optional: custom API endpoint
    'visibility' => 'public', // Default visibility
    'throw' => false,
],
```

Ensure you have your Google Cloud Service Account JSON key file reachable and the path correctly set in `GOOGLE_APPLICATION_CREDENTIALS` environment variable.

## Usage

### Digital Ocean Spaces
You can use the `FileS3LikeSpaces` facade:
```php
use AndreInocenti\LaravelFileS3Like\Facades\FileS3LikeSpaces;

FileS3LikeSpaces::disk('spaces-disk')
    ->directory('images')
    ->upload($file, 'new-test');
```

Or using the generic `FileS3Like` facade:
```php
use AndreInocenti\LaravelFileS3Like\Facades\FileS3Like;

FileS3Like::repository('spaces')
    ->disk('spaces-disk')
    ->directory('images')
    ->upload($file, 'new-test');
```

### Google Cloud Storage (GCS)
Use the `FileS3Like` facade with the `gcs` repository:

```php
use AndreInocenti\LaravelFileS3Like\Facades\FileS3Like;

FileS3Like::repository('gcs')
    ->disk('gcs') // The disk name configured in config/filesystems.php
    ->directory('uploads')
    ->upload($file, 'profile-picture');
```

## Methods / Accessors

### FileS3Like and its repositories (Eg: FileS3LikeSpaces, FileS3LikeGCS)
<table>
    <tr>
        <th>Method</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>repository(string $repository): self</td>
        <td>
            The repository is the storage service you want to use. Supported: <code>spaces</code>, <code>gcs</code>.
        </td>
    </tr>
    <tr>
        <td>disk(string $disk): self</td>
        <td>
            Sets the filesystem disk that the Storage facade will use to handle the files on the cloud storage
        </td>
    </tr>
    <tr>
        <td>directory(string $directory): self</td>
        <td>
            The directory in the cloud storage bucket that will be handled. <b>Important:</b> It will concat with the folder configurated in the file filesystem.
            The folder configuration, is good to have always the same base directory configured, instead of the "directory()" that can change anytime
            EG.: if folder was configured to be "public", and the directory "images", the final path will be `public/images`.
        </td>
    </tr>
    <tr>
        <td>upload(UploadedFile|string $file, ?string $filename = null): <a href="#diskfile">DiskFile</a></td>
        <td>
            Upload the file to the storage.
            If $filename is empty, the name of the file will be aUUID hash.
        </td>
    </tr>
    <tr>
        <td>save(string $repository): <a href="#diskfile">DiskFile</a></td>
        <td>
            Upload and update the file on the storage disk.
            For <b>Spaces</b>: Purges the CDN cache.
            For <b>GCS</b>: Same as upload (GCS requires different API for CDN invalidation).
            If a file with the same name already exists, it will be overwritten.
            If $filename is empty, the name of the file will be aUUID hash.
        </td>
    </tr>
    <tr>
        <td>presignedUrl(string $filepath, ?string $filename = null, int $expiration = 900, string|null $fileType = null, bool $public = false): object</td>
        <td>
            Create a presigned URL for direct upload to the path in <code>$filepath</code> (relative to the configured folder).
            <code>$filename</code> is optional and defaults to a UUID; extension is inferred from <code>$fileType</code> or the filename.
            <code>$fileType</code> accepts a mime type or extension (eg: <code>image/png</code>, <code>jpg</code>, <code>image/*</code>) and is used to set Content-Type.
            <code>$public</code> toggles the ACL between <code>private</code> (default) and <code>public-read</code> (Note: GCS presigned URLs handle public access differently, but the flag sets the ACL header if supported).
            <code>$expiration</code> is in seconds; default is 900 (15 minutes).
        </td>
    </tr>
    <tr>
        <td>purge(string $filepath): self</td>
        <td>
            Purges a CDN cache of a file. 
            <b>Spaces:</b> Calls DigitalOcean CDN API.
            <b>GCS:</b> No-op (use Google Cloud CDN invalidation API separately).
        </td>
    </tr>
    <tr>
        <td>delete(string $filepath): self</td>
        <td>
            Delete a file from the cloud storage disk
        </td>
    </tr>
    <tr>
        <td>deleteDirectory(string $filepath): self</td>
        <td>
            Recursively delete a directory from the cloud storage disk
        </td>
    </tr>
    <tr>
        <td>directories(string $filepath): array</td>
        <td>
            List directories in the directory set
        </td>
    </tr>
    <tr>
        <td>files(string $filepath): array</td>
        <td>
            List files in the directory set
        </td>
    </tr>
</table>

### Presigned URL usage

Use `presignedUrl` when you need a temporary URL for clients to upload directly to the bucket.

```php
use AndreInocenti\LaravelFileS3Like\Facades\FileS3Like;

$upload = FileS3Like::repository('gcs') // or 'spaces'
    ->disk('gcs')
    ->directory('uploads')
    ->presignedUrl(
        filepath: 'avatars',         // directory inside the configured folder
        filename: 'profile.png',     // optional; defaults to UUID
        expiration: 600,             // seconds
        fileType: 'image/png',       // mime or extension; sets Content-Type
        public: true,                // optional; false keeps it private
    );

return response()->json($upload);
```

The returned `Fluent` object contains the presigned URL and useful metadata:

```json
{
  "presigned_url": "https://storage.googleapis.com/...",
  "key": "uploads/avatars/profile.png",
  "public_url": "https://storage.googleapis.com/your-bucket/uploads/avatars/profile.png",
  "expires": "2024-04-30 12:34:56",
  "headers": {
    "Content-Type": "image/png",
    "ACL": "public-read"
  },
  "accepted_mime": "image/png",
  "accepted_ext": "png"
}
```

Send the provided `headers` with the upload request to ensure the ACL and mime type are applied.

### DiskFile

DiskFile is a DTO class that is created and delivered when the functions upload or save are called.
The class contain accessors that returns some file data.

<table>
    <tr>
        <th>Method</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>getFilepath(): string</td>
        <td>
            Return the file, filepath without the url. Eg: "some/path/to/file.jpg".
        </td>
    </tr>
    <tr>
        <td>getFilename(): string</td>
        <td>
            Return the file name. Eg: "file.jpg".
        </td>
    </tr>
    <tr>
        <td>getUrl(): string</td>
        <td>
            Returns the full file url. Eg: "https://somedomain.com/some/path/to/file.jpg".
        </td>
    </tr>
    <tr>
        <td>getExtension(): string</td>
        <td>
            Returns the file extension. Eg: "jpg".
        </td>
    </tr>
</table>