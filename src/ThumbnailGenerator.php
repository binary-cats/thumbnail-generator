<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator;

use Illuminate\Http\UploadedFile;
use Uc\ThumbnailGenerator\Drivers\OutputFormatAwareDriverInterface;
use Uc\ThumbnailGenerator\Drivers\ThumbnailGenerationDriverInterface;
use Uc\ThumbnailGenerator\Enums\OutputFormat;

/**
 * Utility to generate thumbnails for specified files.
 */
class ThumbnailGenerator
{
    /**
     * @var ThumbnailGenerationDriverInterface[]|array
     */
    protected array $drivers = [];

    /**
     * ThumbnailGenerator constructor.
     */
    public function __construct(ThumbnailGenerationDriverInterface ...$drivers)
    {
        $this->drivers = $drivers;
    }

    /**
     * Generate thumbnail of given file.
     */
    public function generate(UploadedFile $file, int $width, int $height, ?OutputFormat $output = null): ?string
    {
        foreach ($this->drivers as $driver) {
            if ($driver->supports($file)) {
                if ($output !== null && $driver instanceof OutputFormatAwareDriverInterface) {
                    return $driver->generate($file, $width, $height, $output);
                }

                return $driver->generate($file, $width, $height);
            }
        }

        return null;
    }
}
