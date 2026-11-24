<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Uc\ThumbnailGenerator\Contracts\ThumbnailGenerationDriverInterface;
use Uc\ThumbnailGenerator\Enums\OutputFormat;
use Uc\ThumbnailGenerator\ThumbnailGenerator;
use Uc\ThumbnailGenerator\ThumbnailGeneratorFactory;

class ThumbnailGeneratorTest extends AbstractThumbnailGenerator
{
    public function test_generate_from_disk_forwards_format_and_returns_webp_pair(): void
    {
        $disk = Storage::fake('documents');
        $path = 'contracts/agreement.pdf';
        $disk->put($path, file_get_contents(__DIR__ . '/sources/pdf.pdf'));
        $factory = $this->app->make(ThumbnailGeneratorFactory::class);
        $generator = $factory->makeWebPAware($factory->createGenericThumbnailGenerator());

        [$preview, $webp] = $generator->generateFromDisk($disk, $path, 160, 100, OutputFormat::PNG);

        $this->assertImageProperties($preview, 160, 100, 'image/png');
        $this->assertImageProperties($webp, 160, 100, 'image/webp');
        $disk->assertExists($path);
    }

    public function test_missing_source_throws_a_read_error(): void
    {
        $disk = $this->createStub(Filesystem::class);
        $disk->method('readStream')->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('missing.pdf');
        (new ThumbnailGenerator())->generateFromDisk($disk, 'missing.pdf', 100, 100);
    }

    public function test_source_stream_and_local_file_are_released_on_conversion_failure(): void
    {
        $source = fopen(__DIR__ . '/sources/pdf.pdf', 'rb');
        $disk = $this->createStub(Filesystem::class);
        $disk->method('readStream')->willReturn($source);
        $localPath = null;
        $error = new RuntimeException('Conversion failed.');
        $driver = $this->createMock(ThumbnailGenerationDriverInterface::class);
        $driver->method('supports')->willReturn(true);
        $driver->expects($this->once())->method('generate')
            ->willReturnCallback(function (UploadedFile $file) use (&$localPath, $error): never {
                $this->assertSame('agreement.pdf', $file->getClientOriginalName());
                $localPath = $file->getPathname();
                $this->assertFileEquals(__DIR__ . '/sources/pdf.pdf', $localPath);

                throw $error;
            });

        try {
            (new ThumbnailGenerator($driver))->generateFromDisk($disk, 'contracts/agreement.pdf', 100, 100);
            $this->fail('Expected the conversion exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame($error, $exception);
        }

        $this->assertFalse(is_resource($source));
        $this->assertFileDoesNotExist($localPath);
    }
}
