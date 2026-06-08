<?php

namespace App\Tests\Unit\Service;

use App\Service\ProfileThemeService;
use PHPUnit\Framework\TestCase;

class ProfileThemeServiceTest extends TestCase
{
    public function testGetChoicesReturnsAllThemes(): void
    {
        $choices = ProfileThemeService::getChoices();

        $this->assertNotEmpty($choices);
        $this->assertContains('default', $choices);
        $this->assertContains('ocean', $choices);
        $this->assertContains('forest', $choices);
    }

    public function testGetChoicesKeysAreLabels(): void
    {
        $choices = ProfileThemeService::getChoices();

        $this->assertArrayHasKey('Фіолетова', $choices);
        $this->assertArrayHasKey('Океан', $choices);
        $this->assertArrayHasKey('Ліс', $choices);
    }

    public function testCssVariablesReturnsStringForValidTheme(): void
    {
        $css = ProfileThemeService::cssVariables('ocean');

        $this->assertNotEmpty($css);
        $this->assertStringContainsString('--gf-primary', $css);
        $this->assertStringContainsString('--gf-secondary', $css);
        $this->assertStringEndsWith(';', $css);
    }

    public function testCssVariablesReturnsValidForDefault(): void
    {
        $css = ProfileThemeService::cssVariables('default');

        $this->assertNotEmpty($css);
        $this->assertStringContainsString('--gf-primary', $css);
    }

    public function testCssVariablesReturnsEmptyForNull(): void
    {
        $css = ProfileThemeService::cssVariables(null);

        $this->assertEmpty($css);
    }

    public function testCssVariablesReturnsEmptyForInvalidKey(): void
    {
        $css = ProfileThemeService::cssVariables('nonexistent_theme');

        $this->assertEmpty($css);
    }

    public function testAllThemesWithVarsProduceCss(): void
    {
        foreach (ProfileThemeService::THEMES as $key => $data) {
            if (!empty($data['vars'])) {
                $css = ProfileThemeService::cssVariables($key);
                $this->assertNotEmpty($css, "Theme '$key' should produce CSS variables");
            }
        }
    }

    public function testIsPremiumThemeReturnsTrueForPremium(): void
    {
        $this->assertTrue(ProfileThemeService::isPremiumTheme('neon'));
        $this->assertTrue(ProfileThemeService::isPremiumTheme('gold'));
        $this->assertTrue(ProfileThemeService::isPremiumTheme('cyberpunk'));
        $this->assertTrue(ProfileThemeService::isPremiumTheme('aurora'));
        $this->assertTrue(ProfileThemeService::isPremiumTheme('bloodmoon'));
        $this->assertTrue(ProfileThemeService::isPremiumTheme('galaxy'));
    }

    public function testIsPremiumThemeReturnsFalseForFree(): void
    {
        $this->assertFalse(ProfileThemeService::isPremiumTheme('default'));
        $this->assertFalse(ProfileThemeService::isPremiumTheme('ocean'));
        $this->assertFalse(ProfileThemeService::isPremiumTheme('forest'));
    }

    public function testIsPremiumThemeReturnsFalseForInvalid(): void
    {
        $this->assertFalse(ProfileThemeService::isPremiumTheme('nonexistent'));
    }

    public function testGetChoicesExcludesPremiumWhenFalse(): void
    {
        $choices = ProfileThemeService::getChoices(false);

        $this->assertNotContains('neon', $choices);
        $this->assertNotContains('gold', $choices);
        $this->assertNotContains('cyberpunk', $choices);
        $this->assertNotContains('aurora', $choices);
        $this->assertNotContains('bloodmoon', $choices);
        $this->assertNotContains('galaxy', $choices);
        $this->assertContains('ocean', $choices);
    }

    public function testGetChoicesIncludesPremiumByDefault(): void
    {
        $choices = ProfileThemeService::getChoices();

        $this->assertContains('neon', $choices);
        $this->assertContains('gold', $choices);
        $this->assertContains('cyberpunk', $choices);
        $this->assertContains('aurora', $choices);
        $this->assertContains('bloodmoon', $choices);
        $this->assertContains('galaxy', $choices);
    }
}
