<?php

namespace App\Service;

class ProfileThemeService
{
    public const THEMES = [
        'default' => [
            'label' => 'Фіолетова',
            'vars' => [
                '--gf-primary' => '#7c6cf7',
                '--gf-secondary' => '#a78bfa',
                '--gf-card' => '#1a1035',
                '--gf-dark' => '#110a28',
                '--gf-darker' => '#0a061a',
                '--gf-border' => '#2e1f5e',
                '--gf-text' => '#ede5ff',
                '--gf-muted' => '#a99cc9',
                '--gf-input-bg' => '#110a28',
                '--gf-input-border' => '#2e1f5e',
            ],
            'preview' => ['#7c6cf7', '#a78bfa'],
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
        'arctic' => [
            'label' => 'Арктика',
            'vars' => [
                '--gf-primary' => '#74b9ff',
                '--gf-secondary' => '#a3d8f4',
                '--gf-card' => '#101828',
                '--gf-dark' => '#0b1120',
                '--gf-darker' => '#060a14',
                '--gf-border' => '#1c2d44',
                '--gf-text' => '#e8f4fd',
                '--gf-muted' => '#8eafc4',
                '--gf-input-bg' => '#0b1120',
                '--gf-input-border' => '#1c2d44',
            ],
            'preview' => ['#74b9ff', '#a3d8f4'],
        ],
        'volcano' => [
            'label' => 'Вулкан',
            'vars' => [
                '--gf-primary' => '#e55039',
                '--gf-secondary' => '#f39c12',
                '--gf-card' => '#1f1008',
                '--gf-dark' => '#150b05',
                '--gf-darker' => '#0d0703',
                '--gf-border' => '#3a1c0a',
                '--gf-text' => '#ffe8d6',
                '--gf-muted' => '#c49070',
                '--gf-input-bg' => '#150b05',
                '--gf-input-border' => '#3a1c0a',
            ],
            'preview' => ['#e55039', '#f39c12'],
        ],
        'mono' => [
            'label' => 'Моно',
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
                '--gf-primary' => '#39ff14',
                '--gf-secondary' => '#00e5ff',
                '--gf-card' => '#040d08',
                '--gf-dark' => '#020a05',
                '--gf-darker' => '#010602',
                '--gf-border' => '#0a2e15',
                '--gf-text' => '#d0ffe0',
                '--gf-muted' => '#5cb87a',
                '--gf-input-bg' => '#020a05',
                '--gf-input-border' => '#0a2e15',
                '--gf-premium-glow' => '0 0 20px rgba(57, 255, 20, 0.3)',
            ],
            'preview' => ['#39ff14', '#00e5ff'],
        ],
        'gold' => [
            'label' => 'Золото ★',
            'premium' => true,
            'vars' => [
                '--gf-primary' => '#ffd700',
                '--gf-secondary' => '#ff8c00',
                '--gf-card' => '#1a1400',
                '--gf-dark' => '#110d00',
                '--gf-darker' => '#0a0800',
                '--gf-border' => '#3d2e00',
                '--gf-text' => '#fff8dc',
                '--gf-muted' => '#c9a84c',
                '--gf-input-bg' => '#110d00',
                '--gf-input-border' => '#3d2e00',
                '--gf-premium-glow' => '0 0 20px rgba(255, 215, 0, 0.25)',
            ],
            'preview' => ['#ffd700', '#ff8c00'],
        ],
        'cyberpunk' => [
            'label' => 'Кіберпанк ★',
            'premium' => true,
            'vars' => [
                '--gf-primary' => '#ff003c',
                '--gf-secondary' => '#00fff0',
                '--gf-card' => '#0a000f',
                '--gf-dark' => '#06000a',
                '--gf-darker' => '#030006',
                '--gf-border' => '#2a0035',
                '--gf-text' => '#fff0f5',
                '--gf-muted' => '#c060a0',
                '--gf-input-bg' => '#06000a',
                '--gf-input-border' => '#2a0035',
                '--gf-premium-glow' => '0 0 20px rgba(255, 0, 60, 0.3)',
            ],
            'preview' => ['#ff003c', '#00fff0'],
        ],
        'aurora' => [
            'label' => 'Аврора ★',
            'premium' => true,
            'vars' => [
                '--gf-primary' => '#7f5af0',
                '--gf-secondary' => '#2cb67d',
                '--gf-card' => '#0e0b1a',
                '--gf-dark' => '#090712',
                '--gf-darker' => '#05040a',
                '--gf-border' => '#1f1540',
                '--gf-text' => '#fffffe',
                '--gf-muted' => '#94a1b2',
                '--gf-input-bg' => '#090712',
                '--gf-input-border' => '#1f1540',
                '--gf-premium-glow' => '0 0 20px rgba(127, 90, 240, 0.3)',
            ],
            'preview' => ['#7f5af0', '#2cb67d'],
        ],
        'bloodmoon' => [
            'label' => 'Кривавий місяць ★',
            'premium' => true,
            'vars' => [
                '--gf-primary' => '#dc143c',
                '--gf-secondary' => '#8b0000',
                '--gf-card' => '#150008',
                '--gf-dark' => '#0e0005',
                '--gf-darker' => '#080003',
                '--gf-border' => '#300010',
                '--gf-text' => '#ffd6e0',
                '--gf-muted' => '#a05070',
                '--gf-input-bg' => '#0e0005',
                '--gf-input-border' => '#300010',
                '--gf-premium-glow' => '0 0 20px rgba(220, 20, 60, 0.3)',
            ],
            'preview' => ['#dc143c', '#8b0000'],
        ],
        'galaxy' => [
            'label' => 'Галактика ★',
            'premium' => true,
            'vars' => [
                '--gf-primary' => '#e040fb',
                '--gf-secondary' => '#536dfe',
                '--gf-card' => '#0d0520',
                '--gf-dark' => '#080315',
                '--gf-darker' => '#04010a',
                '--gf-border' => '#1a0a3e',
                '--gf-text' => '#f3e5ff',
                '--gf-muted' => '#a070c8',
                '--gf-input-bg' => '#080315',
                '--gf-input-border' => '#1a0a3e',
                '--gf-premium-glow' => '0 0 20px rgba(224, 64, 251, 0.3)',
            ],
            'preview' => ['#e040fb', '#536dfe'],
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
