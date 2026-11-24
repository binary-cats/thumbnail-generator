<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator;

use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Drivers\AudioDriver;
use Uc\ThumbnailGenerator\Drivers\DocxDriver;
use Uc\ThumbnailGenerator\Drivers\ImageDriver;
use Uc\ThumbnailGenerator\Drivers\OdtDriver;
use Uc\ThumbnailGenerator\Drivers\PdfDriver;
use Uc\ThumbnailGenerator\Drivers\RtfDriver;
use Uc\ThumbnailGenerator\Drivers\VideoDriver;
use Uc\ThumbnailGenerator\Processors\DocumentProcessor;
use Uc\ThumbnailGenerator\Processors\FFMpegProcessor;
use Uc\ThumbnailGenerator\Processors\PdfProcessor;

/**
 * Factory for creating thumbnail generator instances.
 */
class ThumbnailGeneratorFactory
{
    public function __construct(
        protected readonly FFMpegProcessor $ffmpegProcessor,
        protected readonly ImageManipulator $imageManipulator,
    ) {}

    /**
     * Create instance of ThumbnailGenerator powered with all available drivers.
     */
    public function createGenericThumbnailGenerator(): ThumbnailGenerator
    {
        return new ThumbnailGenerator(
            new ImageDriver(
                $this->imageManipulator,
            ),
            new AudioDriver(
                $this->ffmpegProcessor,
            ),
            new VideoDriver(
                $this->ffmpegProcessor,
            ),
            ...$this->createDocumentAwareThumbnailDrivers(),
        );
    }

    /**
     * Create instance of ThumbnailGenerator powered with ImageDriver.
     */
    public function createImageThumbnailGenerator(): ThumbnailGenerator
    {
        return new ThumbnailGenerator(
            new ImageDriver(
                $this->imageManipulator,
            ),
        );
    }

    /**
     * Create instance of ThumbnailGenerator powered with AudioDriver.
     */
    public function createAudioThumbnailGenerator(): ThumbnailGenerator
    {
        return new ThumbnailGenerator(
            new AudioDriver(
                $this->ffmpegProcessor,
            ),
        );
    }

    /**
     * Create instance of ThumbnailGenerator powered with VideoDriver.
     */
    public function createVideoThumbnailGenerator(): ThumbnailGenerator
    {
        return new ThumbnailGenerator(
            new VideoDriver(
                $this->ffmpegProcessor,
            ),
        );
    }

    /**
     * Create instance of ThumbnailGenerator powered with various Document drivers.
     */
    public function createDocumentThumbnailGenerator(): ThumbnailGenerator
    {
        return new ThumbnailGenerator(...$this->createDocumentAwareThumbnailDrivers());
    }

    /**
     * Enhances the provided ThumbnailGenerator instance to support WebP thumbnail generation.
     *
     * This method accepts a ThumbnailGenerator instance and returns a decorated instance
     * that is capable of generating thumbnails in the WebP format. The returned
     * WebPAwareThumbnailGenerator maintains all the original functionality while adding
     * support for WebP, a modern image format that provides superior compression.
     *
     * @param ThumbnailGenerator $generator The original ThumbnailGenerator instance to be
     *                                      enhanced.
     * @return WebPAwareThumbnailGenerator A new instance of WebPAwareThumbnailGenerator that
     *                                     supports WebP generation.
     */
    public function makeWebPAware(ThumbnailGenerator $generator): WebPAwareThumbnailGenerator
    {
        return new WebPAwareThumbnailGenerator($generator, $this->imageManipulator);
    }

    protected function createDocumentAwareThumbnailDrivers(): array
    {
        $pdfProcessor = $this->createPdfProcessor();
        $documentProcessor = $this->createDocumentProcessor($pdfProcessor);

        return [
            new PdfDriver($pdfProcessor),
            new DocxDriver($documentProcessor),
            new OdtDriver($documentProcessor),
            new RtfDriver($documentProcessor),
        ];
    }

    /**
     * Create instance of DocumentProcessor.
     */
    protected function createDocumentProcessor(PdfProcessor $processor): DocumentProcessor
    {
        return new DocumentProcessor($processor);
    }

    /**
     * Create instance of PdfProcessor.
     */
    protected function createPdfProcessor(): PdfProcessor
    {
        return new PdfProcessor($this->imageManipulator);
    }
}
