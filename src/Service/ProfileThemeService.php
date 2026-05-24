<?php

namespace App\Service;

class ProfileThemeService
{
    public const THEMES = [
        'default' => [
            'label' => 'Типова (фіолетова)',
            'preview' => ['#6c5ce7', '#00cec9'],
        ],
        'sunset' => [
            'label' => 'Захід сонця',
            'vars' => [
                '--gf-primary' => '#ff6b6b',
                '--gf-secondary' => '#ffa502',
                '--gf-card' => '#2d1b2e',
                '--gf-dark' => '#1a0f1f',
                '--gf-darker' => '#120a15',
                '--gf-border' => '#3d2840',
                '--gf-text' => '#ffe5d9',
                '--gf-muted' => '#c9a5a5',
                '--gf-input-bg' => '#1a0f1f',
                '--gf-input-border' => '#3d2840',
            ],
            'preview' => ['#ff6b6b', '#ffa502'],
        ],
        'ocean' => [
            'label' => 'Океан',
            'vars' => [
                '--gf-primary' => '#0984e3',
                '--gf-secondary' => '#00cec9',
                '--gf-card' => '#0d2439',
                '--gf-dark' => '#081826',
                '--gf-darker' => '#050f19',
                '--gf-border' => '#173556',
                '--gf-text' => '#d6ecff',
                '--gf-muted' => '#8fb3d9',
                '--gf-input-bg' => '#081826',
                '--gf-input-border' => '#173556',
            ],
            'preview' => ['#0984e3', '#00cec9'],
        ],
        'forest' => [
            'label' => 'Ліс',
            'vars' => [
                '--gf-primary' => '#00b894',
                '--gf-secondary' => '#55efc4',
                '--gf-card' => '#152820',
                '--gf-dark' => '#0d1a14',
                '--gf-darker' => '#07110b',
                '--gf-border' => '#1f3a2c',
                '--gf-text' => '#d8f3dc',
                '--gf-muted' => '#8fb8a0',
                '--gf-input-bg' => '#0d1a14',
                '--gf-input-border' => '#1f3a2c',
            ],
            'preview' => ['#00b894', '#55efc4'],
        ],
        'cherry' => [
            'label' => 'Вишня',
            'vars' => [
                '--gf-primary' => '#e84393',
                '--gf-secondary' => '#fd79a8',
                '--gf-card' => '#2a0f1f',
                '--gf-dark' => '#1a0812',
                '--gf-darker' => '#12050c',
                '--gf-border' => '#3d1a2c',
                '--gf-text' => '#ffe0ec',
                '--gf-muted' => '#d9a5b8',
                '--gf-input-bg' => '#1a0812',
                '--gf-input-border' => '#3d1a2c',
            ],
            'preview' => ['#e84393', '#fd79a8'],
        ],
        'mono' => [
            'label' => 'Моно (сіра)',
            'vars' => [
                '--gf-primary' => '#636e72',
                '--gf-secondary' => '#b2bec3',
                '--gf-card' => '#1e1e1e',
                '--gf-dark' => '#141414',
                '--gf-darker' => '#0a0a0a',
                '--gf-border' => '#2e2e2e',
                '--gf-text' => '#e0e0e0',
                '--gf-muted' => '#909090',
                '--gf-input-bg' => '#141414',
                '--gf-input-border' => '#2e2e2e',
            ],
            'preview' => ['#636e72', '#b2bec3'],
        ],
        'light' => [
            'label' => 'Світла',
            'vars' => [
                '--gf-primary' => '#6c5ce7',
                '--gf-secondary' => '#0984e3',
                '--gf-card' => '#ffffff',
                '--gf-dark' => '#f0f0f5',
                '--gf-darker' => '#e8e8f0',
                '--gf-border' => '#d0d0e0',
                '--gf-text' => '#1a1a2e',
                '--gf-muted' => '#666688',
                '--gf-input-bg' => '#ffffff',
                '--gf-input-border' => '#c0c0d8',
            ],
            'preview' => ['#ffffff', '#6c5ce7'],
        ],
        'neon' => [
            'label' => 'Неон ★',
            'premium' => true,
            'vars' => [
                '--gf-primary' => '#00ff88',
                '--gf-secondary' => '#00e5ff',
                '--gf-card' => '#0a0f1a',
                '--gf-dark' => '#060b12',
                '--gf-darker' => '#030609',
                '--gf-border' => '#0f2030',
                '--gf-text' => '#e0fff0',
                '--gf-muted' => '#80bfa0',
                '--gf-input-bg' => '#060b12',
                '--gf-input-border' => '#0f2030',
            ],
            'preview' => ['#00ff88', '#00e5ff'],
        ],
        'gold' => [
            'label' => 'Золото ★',
            'premium' => true,
            'vars' => [
                '--gf-primary' => '#f1c40f',
                '--gf-secondary' => '#f39c12',
                '--gf-card' => '#1a1500',
                '--gf-dark' => '#120e00',
                '--gf-darker' => '#0a0800',
                '--gf-border' => '#2e2500',
                '--gf-text' => '#fff8e0',
                '--gf-muted' => '#bfa860',
                '--gf-input-bg' => '#120e00',
                '--gf-input-border' => '#2e2500',
            ],
            'preview' => ['#f1c40f', '#f39c12'],
        ],
        'cyberpunk' => [
            'label' => 'Кіберпанк ★',
            'premium' => true,
            'vars' => [
                '--gf-primary' => '#ff00ff',
                '--gf-secondary' => '#00ffff',
                '--gf-card' => '#0d0015',
                '--gf-dark' => '#08000d',
                '--gf-darker' => '#040008',
                '--gf-border' => '#1a0030',
                '--gf-text' => '#ffe0ff',
                '--gf-muted' => '#b080b0',
                '--gf-input-bg' => '#08000d',
                '--gf-input-border' => '#1a0030',
            ],
            'preview' => ['#ff00ff', '#00ffff'],
        ],
    ];

    public static function getChoices(bool $includePremium = true): array
    {
        $choices = [];
        foreach (self::THEMES as $key => $data) {
            if (!$includePremium && !empty($data['premium'])) {
                continue;
            }
            $choices[$data['label']] = $key;
        }
        return $choices;
    }

    public static function isPremiumTheme(string $key): bool
    {
        return !empty(self::THEMES[$key]['premium']);
    }

    public static function cssVariables(?string $key): string
    {
        if (!$key || !isset(self::THEMES[$key]) || empty(self::THEMES[$key]['vars'])) {
            return '';
        }

        $parts = [];
        foreach (self::THEMES[$key]['vars'] as $name => $value) {
            $parts[] = $name . ':' . $value;
        }

        return implode(';', $parts) . ';';
    }
}
