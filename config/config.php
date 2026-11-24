<?php

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

];
