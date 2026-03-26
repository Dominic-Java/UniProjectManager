<?php

namespace App\Services\Media;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AvatarImageService
{
    public const TARGET_SIZE = 320;

    public function storeResizedAvatar(UploadedFile $file, int $userId): string
    {
        $path = 'avatars/' . $userId . '/avatar_' . now()->format('Ymd_His') . '_' . Str::lower(Str::random(10)) . '.jpg';
        $binary = $this->resizeAndEncodeToJpeg($file->getRealPath());

        if ($binary !== null) {
            Storage::disk('public')->put($path, $binary);
            return $path;
        }

        return $file->store('avatars/' . $userId, 'public');
    }

    private function resizeAndEncodeToJpeg(?string $realPath): ?string
    {
        if (
            !$realPath
            || !is_file($realPath)
            || !function_exists('getimagesize')
            || !function_exists('imagecreatefromstring')
            || !function_exists('imagecreatetruecolor')
            || !function_exists('imagecopyresampled')
        ) {
            return null;
        }

        $imageData = @file_get_contents($realPath);
        if (!is_string($imageData) || $imageData === '') {
            return null;
        }

        $source = @imagecreatefromstring($imageData);
        if (!$source) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $cropSize = min($sourceWidth, $sourceHeight);
        if ($cropSize <= 0) {
            imagedestroy($source);
            return null;
        }

        $srcX = (int) floor(($sourceWidth - $cropSize) / 2);
        $srcY = (int) floor(($sourceHeight - $cropSize) / 2);

        $destination = imagecreatetruecolor(self::TARGET_SIZE, self::TARGET_SIZE);
        if (!$destination) {
            imagedestroy($source);
            return null;
        }

        imagecopyresampled(
            $destination,
            $source,
            0,
            0,
            $srcX,
            $srcY,
            self::TARGET_SIZE,
            self::TARGET_SIZE,
            $cropSize,
            $cropSize
        );

        ob_start();
        imagejpeg($destination, null, 85);
        $binary = ob_get_clean();

        imagedestroy($destination);
        imagedestroy($source);

        return is_string($binary) ? $binary : null;
    }
}

