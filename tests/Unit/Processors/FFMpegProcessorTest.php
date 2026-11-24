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
        $imageManipulator = new ImageManipulator(new ImageManager(new Driver()));

        $processor = new class($imageManipulator, $frame) extends FFMpegProcessor
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
        $file = new UploadedFile(__DIR__ . '/../sources/mp4.mp4', 'mp4.mp4');

        $this->assertNull($processor->generateVideoThumbnail($file, 100, 100));
    }
}
