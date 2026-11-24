<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Uc\ThumbnailGenerator\Contracts\OutputFormatAwareDriverInterface;
use Uc\ThumbnailGenerator\Contracts\ThumbnailGenerationDriverInterface;
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

    public function generateFromDisk(
        Filesystem $disk,
        string $path,
        int $width,
        int $height,
        ?OutputFormat $output = null,
    ): ?string {
        $local = tmpfile();
        if ($local === false) {
            throw new RuntimeException('Unable to create a temporary file for the source.');
        }

        try {
            $source = $disk->readStream($path);
            if (! is_resource($source)) {
                throw new RuntimeException("Unable to open a read stream for [{$path}].");
            }

            try {
                if (stream_copy_to_stream($source, $local) === false || ! fflush($local)) {
                    throw new RuntimeException("Unable to copy [{$path}] to a temporary file.");
                }
            } finally {
                fclose($source);
            }

            $file = new UploadedFile(stream_get_meta_data($local)['uri'], basename($path));

            return $this->generate($file, $width, $height, $output);
        } finally {
            fclose($local);
        }
    }
}
