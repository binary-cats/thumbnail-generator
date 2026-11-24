<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit;

use Illuminate\Http\UploadedFile;
use Uc\ThumbnailGenerator\Processors\FFMpegProcessor;
use Uc\ThumbnailGenerator\ThumbnailGeneratorFactory;

class ThumbnailGeneratorServiceProviderTest extends AbstractThumbnailGenerator
{
    public function test_resolve_factory_from_container_and_generate_thumbnail(): void
    {
        $factory = $this->app->make(ThumbnailGeneratorFactory::class);
        $file = new UploadedFile(__DIR__ . '/sources/mp4.mp4', 'mp4.mp4');

        $content = $factory->createVideoThumbnailGenerator()->generate($file, 100, 100);

        $this->assertImageProperties($content, 100, 100);
    }

    public function test_resolve_ffmpeg_processor_as_singleton(): void
    {
        $processor = $this->app->make(FFMpegProcessor::class);

        $this->assertSame($processor, $this->app->make(FFMpegProcessor::class));
    }
}
