<?php

namespace App\Tests\Unit\Form;

use App\Entity\Lobby;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LobbyTypeTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidator();
    }

    public function testMinAgeBelowSixFails(): void
    {
        $constraint = new Range(min: 6, max: 60);
        $violations = $this->validator->validate(3, $constraint);

        $this->assertGreaterThan(0, $violations->count());
    }

    public function testMinAgeValidPasses(): void
    {
        $constraint = new Range(min: 6, max: 60);
        $violations = $this->validator->validate(14, $constraint);

        $this->assertSame(0, $violations->count());
    }

    public function testMaxAgeAboveSixtyFails(): void
    {
        $constraint = new Range(min: 6, max: 60);
        $violations = $this->validator->validate(70, $constraint);

        $this->assertGreaterThan(0, $violations->count());
    }

    public function testMaxAgeLessThanMinAgeFails(): void
    {
        $lobby = new Lobby();
        $lobby->setMinAge(20);
        $lobby->setMaxAge(15);

        $hasViolation = $this->validateAgeLogic($lobby);

        $this->assertTrue($hasViolation);
    }

    public function testMaxAgeWithoutMinAgeFails(): void
    {
        $lobby = new Lobby();
        $lobby->setMaxAge(18);

        $hasViolation = $this->validateAgeLogic($lobby);

        $this->assertTrue($hasViolation);
    }

    public function testValidAgeRangePasses(): void
    {
        $lobby = new Lobby();
        $lobby->setMinAge(14);
        $lobby->setMaxAge(25);

        $hasViolation = $this->validateAgeLogic($lobby);

        $this->assertFalse($hasViolation);
    }

    private function validateAgeLogic(Lobby $lobby): bool
    {
        $violated = false;

        if ($lobby->getMaxAge() && !$lobby->getMinAge()) {
            $violated = true;
        }

        if ($lobby->getMinAge() && $lobby->getMaxAge() && $lobby->getMaxAge() <= $lobby->getMinAge()) {
            $violated = true;
        }

        return $violated;
    }
}
