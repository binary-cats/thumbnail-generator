<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Drivers;

use Illuminate\Http\UploadedFile;

use function in_array;

use Uc\ThumbnailGenerator\Contracts\ThumbnailGenerationDriverInterface;
use Uc\ThumbnailGenerator\Processors\FFMpegProcessor;

/**
 * Driver for generating thumbnails for audio files.
 */
class AudioDriver implements ThumbnailGenerationDriverInterface
{
    public function __construct(
        protected readonly FFMpegProcessor $processor,
    ) {}

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
