<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Drivers\PdfDriver;
use Uc\ThumbnailGenerator\Enums\OutputFormat;
use Uc\ThumbnailGenerator\Processors\PdfProcessor;
use Uc\ThumbnailGenerator\ThumbnailGenerator;

class PdfThumbnailGeneratorTest extends AbstractThumbnailGenerator
{
    public function test_generate_regular_return_thumbnail(): void
    {
        $generator = $this->createThumbnailGenerator();
        $content = $generator->generate(
            new UploadedFile(__DIR__ . '/sources/pdf.pdf', 'pdf.pdf'),
            200,
            300,
        );

        $this->assertImageProperties($content, 200, 300);
    }

    public function test_generate_when_output_is_png_returns_png_thumbnail(): void
    {
        config(['thumbnail-generator.output' => 'png']);
        $file = new UploadedFile(__DIR__ . '/sources/pdf.pdf', 'pdf.pdf');

        $content = $this->createThumbnailGenerator()->generate($file, 200, 300);

        $this->assertImageProperties($content, 200, 300, 'image/png');
    }

    public function test_generate_webp_preview_by_override_or_configuration(): void
    {
        $generator = $this->createThumbnailGenerator();
        $file = new UploadedFile(__DIR__ . '/sources/pdf.pdf', 'pdf.pdf');

        $content = $generator->generate($file, 200, 300, output: OutputFormat::WEBP);
        $this->assertImageProperties($content, 200, 300, 'image/webp');

        config(['thumbnail-generator.output' => 'webp']);
        $this->assertImageProperties($generator->generate($file, 200, 300), 200, 300, 'image/webp');
    }

    public function test_generate_when_pdf_is_unreadable_returns_null(): void
    {
        $generator = $this->createThumbnailGenerator();
        $file = UploadedFile::fake()->createWithContent('invalid.pdf', "%PDF-1.7\n");

        $this->assertNull($generator->generate($file, 200, 300));
    }

    protected function createThumbnailGenerator(): ThumbnailGenerator
    {
        return new ThumbnailGenerator(
            new PdfDriver(
                new PdfProcessor(
                    new ImageManipulator(
                        new ImageManager(
                            new Driver(),
                        ),
                    ),
                ),
            ),
        );
    }
}
