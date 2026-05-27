<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProfilePictureService
{
    private const MAX_DIMENSION = 512;
    private const DIRECTORY = 'profile-pictures';
    private const JPEG_QUALITY = 85;

    public function store(UploadedFile $file, ?string $existingPath = null): string
    {
        if (!empty($existingPath)) {
            Storage::disk('public')->delete($existingPath);
        }

        Storage::disk('public')->makeDirectory(self::DIRECTORY);

        $filename = self::DIRECTORY . '/' . Str::uuid()->toString() . '.jpg';
        $fullPath = Storage::disk('public')->path($filename);

        if ($this->canProcessWithGd()) {
            $this->saveResizedImage($file, $fullPath);
            return $filename;
        }

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $extension = 'jpg';
        }

        $fallbackName = self::DIRECTORY . '/' . Str::uuid()->toString() . '.' . $extension;
        $file->storeAs(self::DIRECTORY, basename($fallbackName), 'public');

        return $fallbackName;
    }

    private function canProcessWithGd(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    private function saveResizedImage(UploadedFile $file, string $destination): void
    {
        $source = $this->createImageFromUpload($file);
        if ($source === false) {
            throw new RuntimeException('Unsupported or invalid image file.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        [$newWidth, $newHeight] = $this->scaledDimensions($width, $height);

        $canvas = imagecreatetruecolor($newWidth, $newHeight);
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefill($canvas, 0, 0, $white);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        if (!imagejpeg($canvas, $destination, self::JPEG_QUALITY)) {
            imagedestroy($source);
            imagedestroy($canvas);
            throw new RuntimeException('Unable to save profile picture.');
        }

        imagedestroy($source);
        imagedestroy($canvas);
    }

    /**
     * @return \GdImage|false
     */
    private function createImageFromUpload(UploadedFile $file): \GdImage|false
    {
        $path = $file->getRealPath();
        if (!$path) {
            return false;
        }

        $mime = strtolower((string) $file->getMimeType());

        return match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            'image/gif' => @imagecreatefromgif($path),
            default => false,
        };
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function scaledDimensions(int $width, int $height): array
    {
        $max = self::MAX_DIMENSION;

        if ($width <= $max && $height <= $max) {
            return [$width, $height];
        }

        $ratio = min($max / $width, $max / $height);

        return [
            max(1, (int) round($width * $ratio)),
            max(1, (int) round($height * $ratio)),
        ];
    }
}
