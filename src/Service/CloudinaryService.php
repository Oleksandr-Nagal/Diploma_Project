<?php

namespace App\Service;

use Cloudinary\Cloudinary;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CloudinaryService
{
    private ?Cloudinary $cloudinary = null;

    public function __construct()
    {
        $url = $_ENV['CLOUDINARY_URL'] ?? '';
        if (!empty($url)) {
            $this->cloudinary = new Cloudinary($url);
        }
    }

    public function isConfigured(): bool
    {
        return $this->cloudinary !== null;
    }

    public function upload(UploadedFile $file, string $folder = 'gamefinder'): ?string
    {
        if (!$this->cloudinary) {
            return null;
        }

        try {
            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => $folder,
                'resource_type' => 'auto',
            ]);
            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function uploadAvatar(UploadedFile $file): ?string
    {
        if (!$this->cloudinary) {
            return null;
        }

        try {
            $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder' => 'gamefinder/avatars',
                'transformation' => [
                    'width' => 256,
                    'height' => 256,
                    'crop' => 'fill',
                    'gravity' => 'face',
                ],
            ]);
            return $result['secure_url'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function delete(string $url): void
    {
        if (!$this->cloudinary) {
            return;
        }

        // URL format: https://res.cloudinary.com/xxx/image/upload/v123/gamefinder/avatars/abc.jpg
        $pattern = '#/upload/(?:v\d+/)?(.+)\.\w+$#';
        if (preg_match($pattern, $url, $matches)) {
            try {
                $this->cloudinary->uploadApi()->destroy($matches[1]);
            } catch (\Exception $e) {
            }
        }
    }

    public function isCloudinaryUrl(?string $url): bool
    {
        return $url && str_contains($url, 'res.cloudinary.com');
    }
}
