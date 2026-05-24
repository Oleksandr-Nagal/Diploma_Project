<?php

namespace App\Service;

class EmojiService
{
    public const STANDARD_EMOJIS = [
        '😀', '😂', '😍', '🥰', '😎', '🤔', '😢', '😡',
        '👍', '👎', '👋', '🙌', '💪', '🤝', '✌️', '🫡',
        '❤️', '🔥', '⭐', '🎮', '🏆', '💯', '✅', '❌',
        '🎉', '🎊', '🙏', '💬', '👀', '🤣', '😴', '🤷',
    ];

    public const PREMIUM_STICKERS = [
        'gg' => ['label' => 'GG', 'icon' => 'fa-hand-peace', 'color1' => '#00b894', 'color2' => '#00cec9'],
        'victory' => ['label' => 'Victory Royale', 'icon' => 'fa-trophy', 'color1' => '#f1c40f', 'color2' => '#e67e22'],
        'headshot' => ['label' => 'Headshot', 'icon' => 'fa-crosshairs', 'color1' => '#e74c3c', 'color2' => '#c0392b'],
        'rage_quit' => ['label' => 'Rage Quit', 'icon' => 'fa-face-angry', 'color1' => '#e74c3c', 'color2' => '#f39c12'],
        'noob' => ['label' => 'Noob', 'icon' => 'fa-baby', 'color1' => '#fd79a8', 'color2' => '#e84393'],
        'pro_gamer' => ['label' => 'Pro Gamer', 'icon' => 'fa-gamepad', 'color1' => '#6c5ce7', 'color2' => '#a29bfe'],
        'clutch' => ['label' => 'Clutch!', 'icon' => 'fa-bolt', 'color1' => '#f1c40f', 'color2' => '#e67e22'],
        'respawn' => ['label' => 'Respawn', 'icon' => 'fa-rotate', 'color1' => '#00cec9', 'color2' => '#0984e3'],
        'camp' => ['label' => 'Camper', 'icon' => 'fa-campground', 'color1' => '#55efc4', 'color2' => '#00b894'],
        'loot' => ['label' => 'Loot!', 'icon' => 'fa-box-open', 'color1' => '#f1c40f', 'color2' => '#fdcb6e'],
        'boss' => ['label' => 'Boss Fight', 'icon' => 'fa-dragon', 'color1' => '#e74c3c', 'color2' => '#8e44ad'],
        'shield' => ['label' => 'Shield Up', 'icon' => 'fa-shield-halved', 'color1' => '#3498db', 'color2' => '#2980b9'],
        'critical' => ['label' => 'Critical Hit', 'icon' => 'fa-explosion', 'color1' => '#e74c3c', 'color2' => '#f39c12'],
        'stealth' => ['label' => 'Stealth', 'icon' => 'fa-ghost', 'color1' => '#636e72', 'color2' => '#2d3436'],
        'level_up' => ['label' => 'Level Up!', 'icon' => 'fa-arrow-up', 'color1' => '#00b894', 'color2' => '#55efc4'],
        'gg_wp' => ['label' => 'GG WP', 'icon' => 'fa-crown', 'color1' => '#f1c40f', 'color2' => '#f39c12'],
    ];

    public static function getEmojis(bool $isPremium = false): array
    {
        $data = [
            'standard' => self::STANDARD_EMOJIS,
        ];

        if ($isPremium) {
            $data['stickers'] = self::PREMIUM_STICKERS;
        }

        return $data;
    }

    public static function isValidSticker(string $code): bool
    {
        return isset(self::PREMIUM_STICKERS[$code]);
    }

    public static function renderSticker(string $code): ?string
    {
        $sticker = self::PREMIUM_STICKERS[$code] ?? null;
        if (!$sticker) {
            return null;
        }

        return '<div class="chat-sticker" title="' . htmlspecialchars($sticker['label']) . '">'
            . '<div class="chat-sticker-inner" style="background:linear-gradient(135deg,' . $sticker['color1'] . ',' . $sticker['color2'] . ');">'
            . '<i class="fas ' . $sticker['icon'] . '"></i>'
            . '</div></div>';
    }
}
