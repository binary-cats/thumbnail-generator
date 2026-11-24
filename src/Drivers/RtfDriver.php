<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Drivers;

use Illuminate\Http\UploadedFile;
use Uc\ThumbnailGenerator\Processors\DocumentProcessor;

/**
 * Driver for generating thumbnails for rtf documents.
 */
class RtfDriver implements ThumbnailGenerationDriverInterface
{
    public function __construct(
        protected readonly DocumentProcessor $documentProcessor,
    ) {}

    /**
     * Determine whether the driver supports given file to generate thumbnail.
     */
    public function supports(UploadedFile $file): bool
    {
        return $file->guessExtension() === 'rtf';
    }

    /**
     * Generate thumbnail for given file.
     *
     *
     * @throws \ImagickException
     */
    public function generate(UploadedFile $file, int $width, int $height): ?string
    {
        return $this->documentProcessor->generateThumbnailFromRtf($file, $width, $height);
    }
}
