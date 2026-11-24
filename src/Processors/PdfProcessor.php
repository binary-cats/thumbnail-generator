<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Processors;

use Illuminate\Http\UploadedFile;
use Imagick;
use ImagickException;
use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Enums\OutputFormat;

/**
 * Utility class for generating thumbnails of pdf documents.
 * Wraps Imagick extension.
 */
class PdfProcessor
{
    public function __construct(
        protected readonly ImageManipulator $imageManipulator,
    ) {}

    /**
     * Generate thumbnail of given file.
     *
     *
     * @throws ImagickException
     */
    public function generateThumbnail(UploadedFile $file, int $width, int $height, ?OutputFormat $output = null): ?string
    {
        $content = $this->getFirstPageContent($file, $output);
        if ($content === null) {
            return null;
        }

        return $this->imageManipulator->resize($content, $width, $height);
    }

    /**
     * Get content of the first page of the PDF document.
     *
     *
     * @throws ImagickException
     */
    protected function getFirstPageContent(UploadedFile $file, ?OutputFormat $output): ?string
    {
        try {
            $imagick = new Imagick();
            // Read first page of the file.
            $imagick->readImage(sprintf('%s[0]', $file->path()));
            $format = $output ?? OutputFormat::from(config('thumbnail-generator.output'));
            $imagick->setImageFormat($format->value);
        } catch (ImagickException) {
            return null;
        }

        return $imagick->getImageBlob();
    }
}
