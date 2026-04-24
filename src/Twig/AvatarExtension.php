<?php

namespace App\Twig;

use App\Service\AvatarService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\Markup;

class AvatarExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_avatar', [$this, 'renderAvatar']),
            new TwigFunction('avatar_list', [$this, 'getAvatarList']),
        ];
    }

    public function renderAvatar(?string $avatarValue, string $username, string $size = 'sm'): Markup
    {
        $sizes = [
            'sm' => ['box' => '36px', 'font' => '0.9rem', 'icon' => '0.9rem'],
            'md' => ['box' => '64px', 'font' => '1.5rem', 'icon' => '1.5rem'],
            'lg' => ['box' => '120px', 'font' => '3rem', 'icon' => '3rem'],
        ];

        $s = $sizes[$size] ?? $sizes['sm'];

        // Avatar from OAuth (Google/Discord URL)
        if ($avatarValue && str_starts_with($avatarValue, 'http')) {
            return new Markup(
                '<img src="' . htmlspecialchars($avatarValue) . '" class="avatar-' . $size . '" alt="' . htmlspecialchars($username) . '" style="width:' . $s['box'] . ';height:' . $s['box'] . ';">',
                'UTF-8'
            );
        }

        // Built-in avatar (avatar:key format)
        if ($avatarValue && str_starts_with($avatarValue, 'avatar:')) {
            $key = substr($avatarValue, 7);
            $data = AvatarService::getAvatar($key);
            if ($data) {
                return new Markup(
                    '<div style="width:' . $s['box'] . ';height:' . $s['box'] . ';border-radius:50%;background:linear-gradient(135deg,' . $data['color1'] . ',' . $data['color2'] . ');display:inline-flex;align-items:center;justify-content:center;border:2px solid ' . $data['color1'] . ';flex-shrink:0;">' .
                    '<i class="fas ' . $data['icon'] . '" style="color:#fff;font-size:' . $s['icon'] . ';"></i>' .
                    '</div>',
                    'UTF-8'
                );
            }
        }

        // Default: first letter
        $letter = mb_strtoupper(mb_substr($username, 0, 1));
        return new Markup(
            '<div style="width:' . $s['box'] . ';height:' . $s['box'] . ';border-radius:50%;background:linear-gradient(135deg,#6c5ce7,#a855f7);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:' . $s['font'] . ';border:2px solid #6c5ce7;flex-shrink:0;">' .
            htmlspecialchars($letter) .
            '</div>',
            'UTF-8'
        );
    }

    public function getAvatarList(): array
    {
        return AvatarService::AVATARS;
    }
}
