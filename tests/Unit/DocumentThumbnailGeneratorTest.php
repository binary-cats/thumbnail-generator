<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use PhpOffice\PhpWord\Settings;
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

    public function test_generate_when_pdf_renderer_is_unavailable_returns_null(): void
    {
        $generator = $this->getThumbnailGenerator();
        $file = new UploadedFile(__DIR__ . '/sources/document.docx', 'document.docx');
        $renderer = Settings::getPdfRendererName();

        // The configured renderer path contains Dompdf, so TCPDF cannot load from it.
        Settings::setPdfRendererName(Settings::PDF_RENDERER_TCPDF);

        try {
            $this->assertNull($generator->generate($file, 100, 100));
        } finally {
            Settings::setPdfRendererName($renderer);
        }
    }

    public function test_intermediate_pdf_is_removed_after_rendering(): void
    {
        $pdfPath = null;
        $pdfProcessor = $this->createMock(PdfProcessor::class);
        $pdfProcessor->expects($this->once())->method('generateThumbnail')
            ->willReturnCallback(function (UploadedFile $pdf) use (&$pdfPath): string {
                $pdfPath = $pdf->getPathname();
                $this->assertStringStartsWith('%PDF-', file_get_contents($pdfPath));

                return 'thumbnail';
            });

        $file = new UploadedFile(__DIR__ . '/sources/document.docx', 'document.docx');
        $processor = new DocumentProcessor($pdfProcessor);

        $this->assertSame('thumbnail', $processor->generateThumbnailFromDocx($file, 100, 100));
        $this->assertFileDoesNotExist($pdfPath);
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
