<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Enums\OutputFormat;
use Uc\ThumbnailGenerator\Processors\FFMpegProcessor;
use Uc\ThumbnailGenerator\ThumbnailGeneratorFactory;

class GenericThumbnailGeneratorTest extends AbstractThumbnailGenerator
{
    public static function mediaProvider(): array
    {
        return [
            [__DIR__ . '/sources/audio_with_artwork.mp3', 'audio_with_artwork.mp3', 'image/jpeg'], // audio
            [__DIR__ . '/sources/document.docx', 'document.docx', 'image/jpeg'], // document
            [__DIR__ . '/sources/document.odt', 'document.odt', 'image/jpeg'], // document
            [__DIR__ . '/sources/document.rtf', 'document.rtf', 'image/jpeg'], // document
            [__DIR__ . '/sources/image.png', 'image.png', 'image/png'], // image
            [__DIR__ . '/sources/pdf.pdf', 'pdf.pdf', 'image/jpeg'], // pdf
            [__DIR__ . '/sources/webm.webm', 'webm.webm', 'image/jpeg'], // video
            [__DIR__ . '/sources/mov.mov', 'mov.mov', 'image/jpeg'], // video
            [__DIR__ . '/sources/asf.asf', 'asf.asf', 'image/jpeg'], // video
            [__DIR__ . '/sources/mkv.mkv', 'mkv.mkv', 'image/jpeg'], // video
            [__DIR__ . '/sources/avi.avi', 'avi.avi', 'image/jpeg'], // video
            [__DIR__ . '/sources/flv.flv', 'flv.flv', 'image/jpeg'], // video
            [__DIR__ . '/sources/mp4.mp4', 'mp4.mp4', 'image/jpeg'], // video
        ];
    }

    #[DataProvider('mediaProvider')]
    public function test_generate_with_different_formats_returns_thumbnail(
        string $path,
        string $name,
        ?string $mime,
    ): void {
        $generator = $this
            ->getThumbnailGeneratorFactory()
            ->createGenericThumbnailGenerator();
        $content = $generator->generate(new UploadedFile($path, $name), 100, 100);

        $this->assertImageProperties($content, 100, 100, $mime);
    }

    public static function specializedMediaProvider(): array
    {
        return [
            'image' => ['createImageThumbnailGenerator', 'image.png', 'image/png'],
            'audio' => ['createAudioThumbnailGenerator', 'audio_with_artwork.mp3', 'image/jpeg'],
            'video' => ['createVideoThumbnailGenerator', 'mp4.mp4', 'image/jpeg'],
            'document' => ['createDocumentThumbnailGenerator', 'document.docx', 'image/jpeg'],
        ];
    }

    #[DataProvider('specializedMediaProvider')]
    public function test_specialized_factory_generates_thumbnail(
        string $factoryMethod,
        string $name,
        string $mime,
    ): void {
        $generator = $this->getThumbnailGeneratorFactory()->$factoryMethod();
        $content = $generator->generate(new UploadedFile(__DIR__ . '/sources/' . $name, $name), 100, 100);

        $this->assertImageProperties($content, 100, 100, $mime);
    }

    public function test_generate_when_format_is_unsupported_returns_null(): void
    {
        $generator = $this->getThumbnailGeneratorFactory()->createGenericThumbnailGenerator();
        $file = UploadedFile::fake()->createWithContent('unsupported.txt', 'Plain text');

        $this->assertNull($generator->generate($file, 100, 100));
    }

    public function test_webp_factory_returns_original_and_webp_thumbnails(): void
    {
        $factory = $this->getThumbnailGeneratorFactory();
        $generator = $factory->makeWebPAware($factory->createImageThumbnailGenerator());
        $file = new UploadedFile(__DIR__ . '/sources/image.png', 'image.png');

        [$original, $webp] = $generator->generate($file, 100, 100);

        $this->assertImageProperties($original, 100, 100, 'image/png');
        $this->assertImageProperties($webp, 100, 100, 'image/webp');
    }

    public static function documentPreviewProvider(): array
    {
        return [
            ['pdf.pdf'],
            ['document.docx'],
            ['document.odt'],
            ['document.rtf'],
        ];
    }

    #[DataProvider('documentPreviewProvider')]
    public function test_output_override_applies_to_one_document_preview(string $name): void
    {
        config(['thumbnail-generator.output' => 'png']);
        $factory = $this->getThumbnailGeneratorFactory();
        $generator = $factory->makeWebPAware($factory->createGenericThumbnailGenerator());
        $file = new UploadedFile(__DIR__ . '/sources/' . $name, $name);

        [$jpeg, $webp] = $generator->generate($file, 100, 100, output: OutputFormat::JPEG);
        [$default] = $generator->generate($file, 100, 100);

        $this->assertImageProperties($jpeg, 100, 100, 'image/jpeg');
        $this->assertImageProperties($webp, 100, 100, 'image/webp');
        $this->assertImageProperties($default, 100, 100, 'image/png');
    }

    protected function getThumbnailGeneratorFactory(): ThumbnailGeneratorFactory
    {
        return new ThumbnailGeneratorFactory(
            new FFMpegProcessor(
                new ImageManipulator(
                    new ImageManager(
                        new Driver(),
                    ),
                ),
            ),
            new ImageManipulator(
                new ImageManager(
                    new Driver(),
                ),
            ),
        );
    }
}
