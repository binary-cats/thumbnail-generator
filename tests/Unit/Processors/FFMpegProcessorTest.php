<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Tests\Unit\Processors;

use FFMpeg\Media\Frame;
use FFMpeg\Media\Video;
use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Uc\ImageManipulator\ImageManipulator;
use Uc\ThumbnailGenerator\Processors\FFMpegProcessor;
use Uc\ThumbnailGenerator\Tests\TestCase;

class FFMpegProcessorTest extends TestCase
{
    public function test_generate_video_thumbnail_when_file_contains_only_audio_returns_null(): void
    {
        $processor = new FFMpegProcessor(new ImageManipulator(new ImageManager(new Driver())));
        $file = new UploadedFile(__DIR__ . '/../sources/audio_without_artwork.mp3', 'audio_without_artwork.mp3');

        $this->assertNull($processor->generateVideoThumbnail($file, 100, 100));
    }

    public function test_generate_video_thumbnail_when_frame_save_writes_no_image_returns_null(): void
    {
        // Simulate a successful frame save that writes no image bytes.
        $frame = $this->createMock(Frame::class);
        $frame->expects($this->once())->method('save')->willReturnSelf();
        $processor = $this->processorWithFrame($frame);
        $file = new UploadedFile(__DIR__ . '/../sources/mp4.mp4', 'mp4.mp4');

        $this->assertNull($processor->generateVideoThumbnail($file, 100, 100));
    }

    public function test_partial_frame_is_removed_when_save_throws(): void
    {
        $framePath = null;
        $error = new \RuntimeException('Frame save failed.');
        $frame = $this->createMock(Frame::class);
        $frame->expects($this->once())->method('save')
            ->willReturnCallback(function (string $path) use (&$framePath, $error): never {
                $framePath = $path;
                file_put_contents($path, 'partial frame');
                throw $error;
            });

        $processor = $this->processorWithFrame($frame);
        $file = new UploadedFile(__DIR__ . '/../sources/mp4.mp4', 'mp4.mp4');

        try {
            $processor->generateVideoThumbnail($file, 100, 100);
            $this->fail('Expected the frame save exception.');
        } catch (\RuntimeException $exception) {
            $this->assertSame($error, $exception);
        }
        $this->assertFileDoesNotExist($framePath);
    }

    private function processorWithFrame(Frame $frame): FFMpegProcessor
    {
        $imageManipulator = new ImageManipulator(new ImageManager(new Driver()));

        return new class($imageManipulator, $frame) extends FFMpegProcessor
        {
            public function __construct(ImageManipulator $imageManipulator, private readonly Frame $frame)
            {
                parent::__construct($imageManipulator);
            }

            protected function retrieveFrame(Video $video, float $second): Frame
            {
                return $this->frame;
            }
        };
    }
}
