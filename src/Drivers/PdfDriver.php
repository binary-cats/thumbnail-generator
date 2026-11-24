<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Drivers;

use Illuminate\Http\UploadedFile;
use Uc\ThumbnailGenerator\Processors\PdfProcessor;

/**
 * Driver for generating thumbnails for pdf documents.
 */
class PdfDriver implements ThumbnailGenerationDriverInterface
{
    /**
     * PdfDriver constructor.
     */
    public function __construct(protected PdfProcessor $pdfProcessor) {}

    /**
     * {@inheritDoc}
     */
    public function supports(UploadedFile $file): bool
    {
        return $file->guessExtension() === 'pdf';
    }

    /**
     * Generate thumbnail for given file.
     *
     *
     * @throws \ImagickException
     */
    public function generate(UploadedFile $file, int $width, int $height): ?string
    {
        return $this->pdfProcessor->generateThumbnail($file, $width, $height);
    }
}
