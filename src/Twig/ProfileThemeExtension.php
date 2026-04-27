<?php

namespace App\Twig;

use App\Service\ProfileThemeService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProfileThemeExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('profile_theme_css', [ProfileThemeService::class, 'cssVariables']),
            new TwigFunction('profile_themes', fn() => ProfileThemeService::THEMES),
        ];
    }
}
