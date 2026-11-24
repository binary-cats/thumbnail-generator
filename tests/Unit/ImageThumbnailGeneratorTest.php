<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Drivers\ImageDriver;
use Uc\ThumbnailGenerator\ThumbnailGenerator;

class ImageThumbnailGeneratorTest extends AbstractThumbnailGenerator
{
    public function test_generate_regular_return_thumbnail(): void
    {
        $generator = $this->createThumbnailGenerator();
        $content = $generator->generate(
            new UploadedFile(__DIR__ . '/sources/image.png', 'image.png'),
            200,
            300,
        );

        $this->assertImageProperties($content, 200, 300, 'image/png');
    }

    public function test_generate_svg_returns_original_content(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><rect width="200" height="200"/></svg>';
        $file = UploadedFile::fake()->createWithContent('image.svg', $svg);

        $this->assertSame($svg, $this->createThumbnailGenerator()->generate($file, 100, 100));
    }

    protected function createThumbnailGenerator(): ThumbnailGenerator
    {
        return new ThumbnailGenerator(
            new ImageDriver(
                new ImageManipulator(
                    new ImageManager(
                        new Driver(),
                    ),
                ),
            ),
        );
    }
}
