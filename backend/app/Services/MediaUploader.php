<?php

namespace App\Services;

use App\Jobs\GenerateMediaVariants;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MediaUploader
{
    public const AllowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    public const MaxPixels = 40_000_000;

    public function store(UploadedFile $file, array $meta = [], bool $sync = false): Media
    {
        return $this->storePath($file->getRealPath(), $file->getClientOriginalName(), $meta, $sync);
    }

    public function storePath(string $path, string $originalName, array $meta = [], bool $sync = false): Media
    {
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: '';
        if ($mime === 'application/pdf') {
            return $this->storeDocument($path, $originalName, $meta);
        }
        $info = @getimagesize($path);

        if (! isset(self::AllowedMimeTypes[$mime]) || $info === false || ($info['mime'] ?? '') !== $mime) {
            throw ValidationException::withMessages(['file' => 'Only JPEG, PNG, WebP and AVIF images can be uploaded.']);
        }

        [$width, $height] = $info;
        if ($width < 1 || $height < 1 || $width * $height > self::MaxPixels) {
            throw ValidationException::withMessages(['file' => 'The image dimensions are not supported (maximum 40 megapixels).']);
        }

        $uuid = (string) Str::uuid();
        $extension = self::AllowedMimeTypes[$mime];
        $originalPath = 'media-originals/'.$uuid.'.'.$extension;
        Storage::disk('local')->put($originalPath, file_get_contents($path));

        $media = Media::create([
            'uuid' => $uuid,
            'disk' => 'public',
            'directory' => 'media/'.now()->format('Y/m').'/'.$uuid,
            'filename' => $originalPath,
            'original_name' => Str::limit(basename($originalName), 200, ''),
            'mime_type' => $mime,
            'size' => filesize($path),
            'width' => $width,
            'height' => $height,
            'alt' => $meta['alt'] ?? null,
            'caption' => $meta['caption'] ?? null,
            'credit' => $meta['credit'] ?? null,
            'status' => 'processing',
            'uploaded_by' => auth()->id(),
        ]);

        $sync ? GenerateMediaVariants::dispatchSync($media) : GenerateMediaVariants::dispatch($media);

        return $media->refresh();
    }

    private function storeDocument(string $path, string $originalName, array $meta): Media
    {
        $handle = fopen($path, 'rb');
        $magic = fread($handle, 5);
        fclose($handle);
        if ($magic !== '%PDF-') {
            throw ValidationException::withMessages(['file' => 'The file is not a valid PDF document.']);
        }

        $uuid = (string) Str::uuid();
        $directory = 'media/'.now()->format('Y/m').'/'.$uuid;
        $name = (Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'document').'.pdf';
        Storage::disk('public')->put($directory.'/'.$name, file_get_contents($path));

        return Media::create([
            'uuid' => $uuid,
            'kind' => 'document',
            'disk' => 'public',
            'directory' => $directory,
            'filename' => $directory.'/'.$name,
            'original_name' => Str::limit(basename($originalName), 200, ''),
            'mime_type' => 'application/pdf',
            'size' => filesize($path),
            'alt' => $meta['alt'] ?? null,
            'caption' => $meta['caption'] ?? null,
            'credit' => $meta['credit'] ?? null,
            'status' => 'ready',
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function delete(Media $media): void
    {
        Storage::disk('public')->deleteDirectory($media->directory);
        if (! $media->isDocument()) {
            Storage::disk('local')->delete($media->filename);
        }
        $media->delete();
    }
}
