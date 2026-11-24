<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Processors;

use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpWord\Exception\Exception as PhpWordException;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\Writer\PDF;
use PhpOffice\PhpWord\Writer\WriterInterface;

use function base_path;
use function pathinfo;
use function stream_get_meta_data;
use function tmpfile;

/**
 * Utility class for generating thumbnails of various documents.
 * Under the hood wraps PhpOffice library.
 */
class DocumentProcessor
{
    public function __construct(
        protected readonly PdfProcessor $pdfProcessor,
    ) {
        $domPdfPath = base_path('vendor/dompdf/dompdf');

        Settings::setPdfRendererPath($domPdfPath);
        Settings::setPdfRendererName(Settings::PDF_RENDERER_DOMPDF);
    }

    /**
     * Generate thumbnail from docx document.
     *
     *
     * @throws \ImagickException
     */
    public function generateThumbnailFromDocx(UploadedFile $file, int $width, int $height): ?string
    {
        $writer = $this->createWordWriter('Word2007', $file->path());

        return $this->generateThumbnail($writer, $width, $height);
    }

    /**
     * Generate thumbnail from ODT (OpenOffice format) document.
     *
     *
     * @throws \ImagickException
     */
    public function generateThumbnailFromOdt(UploadedFile $file, int $width, int $height): ?string
    {
        $writer = $this->createWordWriter('ODText', $file->path());

        return $this->generateThumbnail($writer, $width, $height);
    }

    /**
     * Generate thumbnail from Rich text document.
     *
     *
     * @throws \ImagickException
     */
    public function generateThumbnailFromRtf(UploadedFile $file, int $width, int $height): ?string
    {
        $writer = $this->createWordWriter('RTF', $file->path());

        return $this->generateThumbnail($writer, $width, $height);
    }

    /**
     * Create instance of Word Writer.
     */
    protected function createWordWriter(string $format, string $path): PDF|WriterInterface|null
    {
        try {
            $reader = IOFactory::createReader($format)->load($path);

            return IOFactory::createWriter($reader, 'PDF');
        } catch (PhpWordException) {
            return null;
        }
    }

    /**
     * Generate thumbnail with given writer instance.
     *
     *
     * @throws \ImagickException
     */
    protected function generateThumbnail(IWriter|WriterInterface|PDF|null $writer, int $width, int $height): ?string
    {
        if ($writer === null) {
            return null;
        }

        $writer->save($path = $this->getTemporaryPath());

        return $this->generateThumbnailFromPdf($path, $width, $height);
    }

    /**
     * Create temporary file and return its path.
     */
    protected function getTemporaryPath(): string
    {
        return stream_get_meta_data(tmpfile())['uri'];
    }

    /**
     * Generate thumbnail from PDF document.
     *
     *
     * @throws \ImagickException
     */
    protected function generateThumbnailFromPdf(string $path, int $width, int $height): ?string
    {
        return $this->pdfProcessor->generateThumbnail(
            new UploadedFile($path, pathinfo($path, PATHINFO_FILENAME)),
            $width,
            $height,
        );
    }
}
