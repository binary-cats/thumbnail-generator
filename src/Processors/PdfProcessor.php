<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Processors;

use Illuminate\Http\UploadedFile;
use Imagick;
use ImagickException;
use Uc\ImageManipulator\ImageManipulator;

/**
 * Utility class for generating thumbnails of pdf documents.
 * Wraps Imagick extension.
 */
class PdfProcessor
{
    public function __construct(protected ImageManipulator $imageManipulator) {}

    /**
     * Generate thumbnail of given file.
     *
     *
     * @throws ImagickException
     */
    public function generateThumbnail(UploadedFile $file, int $width, int $height): ?string
    {
        $content = $this->getFirstPageContent($file);
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
    protected function getFirstPageContent(UploadedFile $file): ?string
    {
        try {
            $imagick = new Imagick();
            // Read first page of the file.
            $imagick->readImage(sprintf('%s[0]', $file->path()));
            $imagick->setImageFormat('jpeg');
        } catch (ImagickException) {
            return null;
        }

        return $imagick->getImageBlob();
    }
}
