<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Drivers\DocxDriver;
use Uc\ThumbnailGenerator\Drivers\OdtDriver;
use Uc\ThumbnailGenerator\Drivers\RtfDriver;
use Uc\ThumbnailGenerator\Processors\DocumentProcessor;
use Uc\ThumbnailGenerator\Processors\PdfProcessor;
use Uc\ThumbnailGenerator\ThumbnailGenerator;

class DocumentThumbnailGeneratorTest extends AbstractThumbnailGenerator
{
    public function test_generate_regular_docx_return_thumbnail(): void
    {
        $generator = $this->getThumbnailGenerator();
        $content = $generator->generate(
            new UploadedFile(__DIR__ . '/sources/document.docx', 'document.docx'),
            100,
            100,
        );

        $this->assertImageProperties($content, 100, 100);
    }

    public function test_generate_regular_odt_return_thumbnail(): void
    {
        $generator = $this->getThumbnailGenerator();
        $content = $generator->generate(
            new UploadedFile(__DIR__ . '/sources/document.odt', 'document.odt'),
            150,
            200,
        );

        $this->assertImageProperties($content, 150, 200);
    }

    public function test_generate_regular_return_thumbnail(): void
    {
        $generator = $this->getThumbnailGenerator();
        $content = $generator->generate(
            new UploadedFile(__DIR__ . '/sources/document.rtf', 'document.rtf'),
            100,
            100,
        );

        $this->assertImageProperties($content, 100, 100);
    }

    protected function getThumbnailGenerator(): ThumbnailGenerator
    {
        return new ThumbnailGenerator(
            new DocxDriver(
                new DocumentProcessor(
                    new PdfProcessor(
                        new ImageManipulator(
                            new ImageManager(
                                new Driver(),
                            ),
                        ),
                    ),
                ),
            ),
            new OdtDriver(
                new DocumentProcessor(
                    new PdfProcessor(
                        new ImageManipulator(
                            new ImageManager(
                                new Driver(),
                            ),
                        ),
                    ),
                ),
            ),
            new RtfDriver(
                new DocumentProcessor(
                    new PdfProcessor(
                        new ImageManipulator(
                            new ImageManager(
                                new Driver(),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
