<?php

namespace App\Tests\Unit\Twig;

use App\Twig\AvatarExtension;
use PHPUnit\Framework\TestCase;
use Twig\Markup;

class AvatarExtensionTest extends TestCase
{
    private AvatarExtension $extension;

    protected function setUp(): void
    {
        $this->extension = new AvatarExtension();
    }

    public function testGetFunctionsReturnsArray(): void
    {
        $functions = $this->extension->getFunctions();

        $this->assertCount(2, $functions);
    }

    public function testRenderAvatarWithHttpUrl(): void
    {
        $result = $this->extension->renderAvatar('https://example.com/photo.jpg', 'TestUser', 'sm');

        $this->assertInstanceOf(Markup::class, $result);
        $html = (string) $result;
        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('https://example.com/photo.jpg', $html);
        $this->assertStringContainsString('TestUser', $html);
    }

    public function testRenderAvatarWithAvatarKey(): void
    {
        $result = $this->extension->renderAvatar('avatar:knight', 'Player', 'md');

        $html = (string) $result;
        $this->assertStringContainsString('fa-chess-knight', $html);
        $this->assertStringContainsString('linear-gradient', $html);
        $this->assertStringContainsString('64px', $html);
    }

    public function testRenderAvatarWithNullFallsBackToLetter(): void
    {
        $result = $this->extension->renderAvatar(null, 'Username', 'sm');

        $html = (string) $result;
        $this->assertStringContainsString('U', $html);
        $this->assertStringContainsString('36px', $html);
    }

    public function testRenderAvatarLargeSize(): void
    {
        $result = $this->extension->renderAvatar(null, 'Test', 'lg');

        $html = (string) $result;
        $this->assertStringContainsString('120px', $html);
    }

    public function testRenderAvatarWithInvalidAvatarKeyFallsBack(): void
    {
        $result = $this->extension->renderAvatar('avatar:nonexistent', 'Player', 'sm');

        $html = (string) $result;
        $this->assertStringContainsString('P', $html);
    }

    public function testRenderAvatarWithPremiumAddsClass(): void
    {
        $result = $this->extension->renderAvatar(null, 'Pro', 'sm', true);

        $html = (string) $result;
        $this->assertStringContainsString('premium-avatar', $html);
    }

    public function testRenderAvatarWithoutPremiumNoClass(): void
    {
        $result = $this->extension->renderAvatar(null, 'Free', 'sm', false);

        $html = (string) $result;
        $this->assertStringNotContainsString('premium-avatar', $html);
    }

    public function testGetAvatarListReturnsArray(): void
    {
        $list = $this->extension->getAvatarList();

        $this->assertIsArray($list);
        $this->assertArrayHasKey('knight', $list);
        $this->assertArrayHasKey('wizard', $list);
        $this->assertArrayHasKey('dragon', $list);
    }

    public function testRenderAvatarEscapesHtml(): void
    {
        $result = $this->extension->renderAvatar(null, '<script>alert(1)</script>', 'sm');

        $html = (string) $result;
        $this->assertStringNotContainsString('<script>', $html);
    }
}
