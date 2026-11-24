<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Drivers;

use Illuminate\Http\UploadedFile;
use Uc\ThumbnailGenerator\Enums\OutputFormat;
use Uc\ThumbnailGenerator\Processors\PdfProcessor;

/**
 * Driver for generating thumbnails for pdf documents.
 */
class PdfDriver implements OutputFormatAwareDriverInterface
{
    /**
     * PdfDriver constructor.
     */
    public function __construct(
        protected readonly PdfProcessor $pdfProcessor,
    ) {}

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
    public function generate(UploadedFile $file, int $width, int $height, ?OutputFormat $output = null): ?string
    {
        return $this->pdfProcessor->generateThumbnail($file, $width, $height, $output);
    }
}
