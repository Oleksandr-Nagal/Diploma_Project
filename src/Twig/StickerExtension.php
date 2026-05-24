<?php

namespace App\Twig;

use App\Service\EmojiService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\Markup;

class StickerExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('render_stickers', [$this, 'renderStickers'], ['is_safe' => ['html']]),
        ];
    }

    public function renderStickers(string $content): string
    {
        return preg_replace_callback('/\[sticker:([a-z_]+)\]/', function ($matches) {
            $rendered = EmojiService::renderSticker($matches[1]);
            return $rendered ?? htmlspecialchars($matches[0]);
        }, htmlspecialchars($content));
    }
}
