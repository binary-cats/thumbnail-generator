<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Drivers\ImageDriver;
use Uc\ThumbnailGenerator\ThumbnailGenerator;
use Uc\ThumbnailGenerator\WebPAwareThumbnailGenerator;

class WebPAwareThumbnailGeneratorTest extends AbstractThumbnailGenerator
{
    public function test_generate_image_return_two_thumbnails(): void
    {
        $generator = $this->createThumbnailGenerator();
        $content = $generator->generate(
            new UploadedFile(__DIR__ . '/sources/image.png', 'image.png'),
            200,
            300,
        );

        $this->assertIsArray($content);
        $this->assertCount(2, $content);
        $this->assertImageProperties($content[0], 200, 300, 'image/png');
        $this->assertImageProperties($content[1], 200, 300, 'image/webp');
    }

    public function test_generate_when_format_is_unsupported_returns_two_nulls(): void
    {
        $generator = $this->createThumbnailGenerator();
        $file = new UploadedFile(__DIR__ . '/sources/pdf.pdf', 'pdf.pdf');

        $this->assertSame([null, null], $generator->generate($file, 200, 300));
    }

    protected function createThumbnailGenerator(): WebPAwareThumbnailGenerator
    {
        return new WebPAwareThumbnailGenerator(
            new ThumbnailGenerator(
                new ImageDriver(
                    new ImageManipulator(
                        new ImageManager(
                            new Driver(),
                        ),
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
