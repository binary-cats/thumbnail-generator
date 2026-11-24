<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Drivers;

use Illuminate\Http\UploadedFile;
use Uc\ThumbnailGenerator\Enums\OutputFormat;

interface OutputFormatAwareDriverInterface extends ThumbnailGenerationDriverInterface
{
    public function generate(UploadedFile $file, int $width, int $height, ?OutputFormat $output = null): ?string;
}
