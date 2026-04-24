<?php

namespace App\Service;

class AvatarService
{
    public const AVATARS = [
        'knight' => ['icon' => 'fa-chess-knight', 'color1' => '#6c5ce7', 'color2' => '#a855f7', 'label' => 'Лицар'],
        'wizard' => ['icon' => 'fa-hat-wizard', 'color1' => '#0984e3', 'color2' => '#74b9ff', 'label' => 'Маг'],
        'dragon' => ['icon' => 'fa-dragon', 'color1' => '#d63031', 'color2' => '#ff7675', 'label' => 'Дракон'],
        'skull' => ['icon' => 'fa-skull-crossbones', 'color1' => '#2d3436', 'color2' => '#636e72', 'label' => 'Пірат'],
        'robot' => ['icon' => 'fa-robot', 'color1' => '#00cec9', 'color2' => '#81ecec', 'label' => 'Робот'],
        'ghost' => ['icon' => 'fa-ghost', 'color1' => '#a29bfe', 'color2' => '#dfe6e9', 'label' => 'Привид'],
        'fire' => ['icon' => 'fa-fire', 'color1' => '#e17055', 'color2' => '#fdcb6e', 'label' => 'Вогонь'],
        'bolt' => ['icon' => 'fa-bolt', 'color1' => '#fdcb6e', 'color2' => '#ffeaa7', 'label' => 'Блискавка'],
        'shield' => ['icon' => 'fa-shield-halved', 'color1' => '#00b894', 'color2' => '#55efc4', 'label' => 'Щит'],
        'crown' => ['icon' => 'fa-crown', 'color1' => '#f39c12', 'color2' => '#f1c40f', 'label' => 'Корона'],
        'star' => ['icon' => 'fa-star', 'color1' => '#e84393', 'color2' => '#fd79a8', 'label' => 'Зірка'],
        'sword' => ['icon' => 'fa-wand-magic-sparkles', 'color1' => '#6c5ce7', 'color2' => '#fd79a8', 'label' => 'Чари'],
        'cat' => ['icon' => 'fa-cat', 'color1' => '#ff7675', 'color2' => '#fab1a0', 'label' => 'Кіт'],
        'headset' => ['icon' => 'fa-headset', 'color1' => '#00b894', 'color2' => '#0984e3', 'label' => 'Геймер'],
        'crosshair' => ['icon' => 'fa-crosshairs', 'color1' => '#d63031', 'color2' => '#e17055', 'label' => 'Снайпер'],
        'meteor' => ['icon' => 'fa-meteor', 'color1' => '#e17055', 'color2' => '#d63031', 'label' => 'Метеор'],
        'gem' => ['icon' => 'fa-gem', 'color1' => '#0984e3', 'color2' => '#a29bfe', 'label' => 'Діамант'],
        'heart' => ['icon' => 'fa-heart', 'color1' => '#e84393', 'color2' => '#fd79a8', 'label' => 'Серце'],
        'biohazard' => ['icon' => 'fa-biohazard', 'color1' => '#00b894', 'color2' => '#fdcb6e', 'label' => 'Біохазард'],
        'dice' => ['icon' => 'fa-dice-d20', 'color1' => '#6c5ce7', 'color2' => '#e84393', 'label' => 'D20'],
    ];

    public static function getAvatarKeys(): array
    {
        return array_keys(self::AVATARS);
    }

    public static function getAvatar(string $key): ?array
    {
        return self::AVATARS[$key] ?? null;
    }
}
