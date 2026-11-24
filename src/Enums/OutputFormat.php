<?php

declare(strict_types=1);

namespace Uc\ThumbnailGenerator\Enums;

enum OutputFormat: string
{
    case PNG = 'png';
    case JPEG = 'jpeg';
    case WEBP = 'webp';
}
