<?php

namespace Tests\Feature;

use App\Rules\StrongPassword;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * L21: staff passwords must be at least 10 characters.
 * Includes upper, lower, and a number.
 */
class StrongPasswordTest extends TestCase
{
    private function passes(string $pw): bool
    {
        $v = Validator::make(['pw' => $pw], ['pw' => [new StrongPassword]]);
        return $v->passes();
    }

    public function test_rejects_password_shorter_than_ten(): void
    {
        $this->assertFalse($this->passes('Abcdef1!'), '9 chars must be rejected');
        $this->assertFalse($this->passes('Abc1234!'), '8 chars must be rejected');
    }

    public function test_accepts_password_of_exactly_ten_chars(): void
    {
        $this->assertTrue($this->passes('Abcdefg12!'), '10 chars with upper/lower/digit must pass');
    }

    public function test_accepts_longer_password(): void
    {
        $this->assertTrue($this->passes('Abcdefghij123'));
    }

    public function test_rejects_missing_uppercase(): void
    {
        $this->assertFalse($this->passes('abcdefg12!'));
    }

    public function test_rejects_missing_lowercase(): void
    {
        $this->assertFalse($this->passes('ABCDEFG12!'));
    }

    public function test_rejects_missing_digit(): void
    {
        $this->assertFalse($this->passes('Abcdefgh!!'));
    }

    public function test_failure_message_mentions_ten_characters(): void
    {
        $v = Validator::make(['pw' => 'short1A'], ['pw' => [new StrongPassword]]);
        $this->assertTrue($v->fails());
        $this->assertStringContainsString('10 characters', $v->errors()->first('pw'));
    }
}
