<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Drivers;

use Illuminate\Http\UploadedFile;
use Uc\ThumbnailGenerator\Processors\FFMpegProcessor;

use function in_array;

/**
 * Driver for generating thumbnails for audio files.
 */
class AudioDriver implements ThumbnailGenerationDriverInterface
{
    public function __construct(protected FFMpegProcessor $processor) {}

    /**
     * Determine whether the driver supports given file to generate thumbnail.
     */
    public function supports(UploadedFile $file): bool
    {
        return in_array($file->guessExtension(), ['mpga', 'mp3', 'aac', 'm4a'], true);
    }

    /**
     * Generate thumbnail for given file.
     */
    public function generate(UploadedFile $file, int $width, int $height): ?string
    {
        return $this->processor->generateAudioThumbnail($file, $width, $height);
    }
}
