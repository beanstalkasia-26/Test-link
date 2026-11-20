<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Throwable;

class GenerateMediaVariants implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(public Media $media)
    {
        $this->onQueue('media');
    }

    public function handle(): void
    {
        ini_set('memory_limit', '1024M');

        $manager = ImageManager::usingDriver(Driver::class);
        $source = Storage::disk('local')->path($this->media->filename);
        $public = Storage::disk('public');

        $widths = array_values(array_filter(Media::Widths, fn (int $w) => $w <= $this->media->width));
        if ($widths === []) {
            $widths = [$this->media->width];
        }

        $encoder = new WebpEncoder(quality: 76);

        $public->makeDirectory($this->media->directory);
        foreach ($widths as $width) {
            $image = $manager->decodePath($source)->orient()->scaleDown(width: $width);
            $public->put($this->media->variantPath($width, Media::Format), (string) $image->encode($encoder));
        }

        $this->media->forceFill([
            'variants' => ['widths' => $widths, 'formats' => [Media::Format]],
            'status' => 'ready',
        ])->save();
    }

    public function failed(Throwable $exception): void
    {
        $this->media->forceFill(['status' => 'failed'])->save();
        Log::error('Image variant generation failed', ['media_id' => $this->media->id, 'error' => $exception->getMessage()]);
    }
}
