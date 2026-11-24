<?php

use Uc\ThumbnailGenerator\Enums\OutputFormat;

return [

    /*
    |--------------------------------------------------------------------------
    | FFmpeg and FFprobe Executable Paths
    |--------------------------------------------------------------------------
    |
    | FFmpeg extracts thumbnail frames and FFprobe reads media details such
    | as duration. If the executables are installed outside the system PATH,
    | specify the absolute path to each binary here.
    |
     */

    'ffmpeg_binary_path' => env('FFMPEG_BINARY_PATH'),
    'ffprobe_binary_path' => env('FFPROBE_BINARY_PATH'),

    /*
    |--------------------------------------------------------------------------
    | Document Preview Format
    |--------------------------------------------------------------------------
    |
    | PDF, DOCX, ODT, and RTF previews use this image format. Supported
    | values are "jpeg", "png", and "webp".
    |
    */

    'output' => env('THUMBNAIL_GENERATOR_OUTPUT', OutputFormat::JPEG->value),

];
