<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Drivers;

use function file_get_contents;

use Illuminate\Http\UploadedFile;

use function in_array;

use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Contracts\ThumbnailGenerationDriverInterface;

/**
 * Driver for generating thumbnails for image files.
 */
class ImageDriver implements ThumbnailGenerationDriverInterface
{
    public function __construct(
        protected readonly ImageManipulator $imageManipulator,
    ) {}

    /**
     * Determine whether the driver supports given file to generate thumbnail.
     */
    public function supports(UploadedFile $file): bool
    {
        return in_array($file->guessExtension(), ['jpeg', 'jpg', 'png', 'webp', 'svg', 'gif'], true);
    }

    /**
     * Generate thumbnail for given file.
     */
    public function generate(UploadedFile $file, int $width, int $height): ?string
    {
        $content = (string) file_get_contents($file->path());

        if (in_array($file->getMimeType(), ['image/svg+xml', 'application/svg+xml'], true)) {
            return $content;
        }

        return $this->imageManipulator->resize($content, $width, $height);
    }
}
