<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Drivers;

use Illuminate\Http\UploadedFile;

use function in_array;

use Uc\ThumbnailGenerator\Processors\FFMpegProcessor;

/**
 * Driver for generating thumbnails for video files.
 */
class VideoDriver implements ThumbnailGenerationDriverInterface
{
    public function __construct(protected FFMpegProcessor $processor) {}

    /**
     * Determine whether the driver supports given file to generate thumbnail.
     */
    public function supports(UploadedFile $file): bool
    {
        return in_array(
            $file->guessExtension(),
            ['mp4', 'flv', 'avi', 'mkv', 'asf', 'webm', 'mov', 'ogg', 'ogv', 'svg'],
            true,
        );
    }

    /**
     * Generate thumbnail for given file.
     */
    public function generate(UploadedFile $file, int $width, int $height): ?string
    {
        return $this->processor->generateVideoThumbnail($file, $width, $height);
    }
}
