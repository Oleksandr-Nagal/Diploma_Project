<?php

namespace App\Tests\Unit\Service;

use App\Service\AvatarService;
use PHPUnit\Framework\TestCase;

class AvatarServiceTest extends TestCase
{
    public function testGetAvatarKeysReturnsNonEmptyArray(): void
    {
        $keys = AvatarService::getAvatarKeys();

        $this->assertNotEmpty($keys);
        $this->assertContains('knight', $keys);
        $this->assertContains('wizard', $keys);
    }

    public function testGetAvatarReturnsDataForExistingKey(): void
    {
        $avatar = AvatarService::getAvatar('knight');

        $this->assertNotNull($avatar);
        $this->assertArrayHasKey('icon', $avatar);
        $this->assertArrayHasKey('color1', $avatar);
        $this->assertArrayHasKey('color2', $avatar);
        $this->assertArrayHasKey('label', $avatar);
    }

    public function testGetAvatarReturnsNullForInvalidKey(): void
    {
        $this->assertNull(AvatarService::getAvatar('nonexistent'));
    }

    public function testAllAvatarsHaveRequiredFields(): void
    {
        foreach (AvatarService::getAvatarKeys() as $key) {
            $avatar = AvatarService::getAvatar($key);
            $this->assertArrayHasKey('icon', $avatar, "Avatar '$key' missing 'icon'");
            $this->assertArrayHasKey('color1', $avatar, "Avatar '$key' missing 'color1'");
            $this->assertArrayHasKey('color2', $avatar, "Avatar '$key' missing 'color2'");
            $this->assertArrayHasKey('label', $avatar, "Avatar '$key' missing 'label'");
        }
    }
}
