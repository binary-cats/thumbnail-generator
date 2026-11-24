# Thumbnail Generator for Laravel

Generate a thumbnail from a Laravel uploaded file and return its image bytes for storage or use in a response. Raster thumbnails fit within the requested width and height while preserving the aspect ratio.

## Installation

Install the package in a Laravel application running PHP 8.3 or later in the PHP 8 series, with the Imagick PHP extension enabled.

```bash
composer require ucraft-com/thumbnail-generator
```

Laravel discovers the service provider automatically. Make both `ffmpeg` and `ffprobe` available on the PHP process's `PATH`. The factory initializes its media processor when Laravel resolves it, including when requesting an image-only generator. For PDF and document previews, ImageMagick also needs Ghostscript and permission to read PDFs.

## Usage

Resolve the factory from Laravel's container and pass an `Illuminate\Http\UploadedFile` to the generator. In a route or controller handling a validated upload named `file`:

```php
use Uc\ThumbnailGenerator\ThumbnailGeneratorFactory;

$file = $request->file('file');
$factory = app(ThumbnailGeneratorFactory::class);
$generator = $factory->createGenericThumbnailGenerator();

$thumbnail = $generator->generate($file, 320, 180);
```

The generic generator selects the first driver that supports the detected file type. An unsupported upload returns `null`. To limit accepted inputs, use `createImageThumbnailGenerator()`, `createAudioThumbnailGenerator()`, `createVideoThumbnailGenerator()`, or `createDocumentThumbnailGenerator()`.

| Input | Thumbnail |
| --- | --- |
| JPEG, PNG, GIF, WebP | Resized image |
| SVG | Original SVG bytes at the original dimensions |
| Video, including MP4, MOV, WebM, and AVI | A frame from 10% into the video, capped at 10 seconds |
| Audio, including MP3 | Embedded artwork, or `null` when there is no visual stream |
| PDF | First-page preview in JPEG, PNG, or WebP |
| DOCX, ODT, RTF | First-page preview after conversion to PDF |

PDF, DOCX, ODT, and RTF previews default to JPEG. Set `THUMBNAIL_GENERATOR_OUTPUT` to `jpeg`, `png`, or `webp` in your Laravel application's `.env` to choose the default. The corresponding configuration key is `thumbnail-generator.output`.

To override the format for one PDF or document preview, pass an `OutputFormat` to `generate()`. Subsequent calls use the configured default.

```php
use Uc\ThumbnailGenerator\Enums\OutputFormat;

$thumbnail = $generator->generate($file, 320, 180, output: OutputFormat::WEBP);
```

## WebP output

Wrap a generator to get both the thumbnail and a WebP copy in one call. If the underlying generator returns `null`, both results are `null`.

```php
$webPGenerator = $factory->makeWebPAware($generator);
[$thumbnail, $webPThumbnail] = $webPGenerator->generate($file, 320, 180);
```
