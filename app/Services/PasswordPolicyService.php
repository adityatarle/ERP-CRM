<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PasswordPolicyService
{
    /**
     * Password policy rules
     */
    private const MIN_LENGTH = 8;
    private const REQUIRE_UPPERCASE = true;
    private const REQUIRE_LOWERCASE = true;
    private const REQUIRE_NUMBERS = true;
    private const REQUIRE_SYMBOLS = true;
    private const MAX_AGE_DAYS = 90;

    /**
     * Validate password against policy
     */
    public function validatePassword(string $password, ?string $currentPassword = null): array
    {
        $errors = [];

        // Check minimum length
        if (strlen($password) < self::MIN_LENGTH) {
            $errors[] = "Password must be at least " . self::MIN_LENGTH . " characters long.";
        }

        // Check for uppercase letters
        if (self::REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter.";
        }

        // Check for lowercase letters
        if (self::REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter.";
        }

        // Check for numbers
        if (self::REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number.";
        }

        // Check for symbols
        if (self::REQUIRE_SYMBOLS && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character.";
        }

        // Check if password is same as current
        if ($currentPassword && $password === $currentPassword) {
            $errors[] = "New password must be different from current password.";
        }

        // Check for common weak passwords
        if ($this->isWeakPassword($password)) {
            $errors[] = "Password is too common or weak. Please choose a stronger password.";
        }

        return $errors;
    }

    /**
     * Check if password is weak
     */
    private function isWeakPassword(string $password): bool
    {
        $weakPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey',
            '1234567890', 'password1', 'qwerty123', 'dragon', 'master'
        ];

        $lowerPassword = strtolower($password);
        
        foreach ($weakPasswords as $weak) {
            if ($lowerPassword === $weak || strpos($lowerPassword, $weak) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get password strength score
     */
    public function getPasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Length scoring
        if (strlen($password) >= 8) $score += 1;
        if (strlen($password) >= 12) $score += 1;
        if (strlen($password) >= 16) $score += 1;

        // Character variety scoring
        if (preg_match('/[a-z]/', $password)) $score += 1;
        if (preg_match('/[A-Z]/', $password)) $score += 1;
        if (preg_match('/[0-9]/', $password)) $score += 1;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score += 1;

        // Determine strength level
        if ($score <= 2) {
            $strength = 'Very Weak';
            $color = 'danger';
        } elseif ($score <= 4) {
            $strength = 'Weak';
            $color = 'warning';
        } elseif ($score <= 6) {
            $strength = 'Fair';
            $color = 'info';
        } elseif ($score <= 8) {
            $strength = 'Good';
            $color = 'success';
        } else {
            $strength = 'Very Strong';
            $color = 'success';
        }

        return [
            'score' => $score,
            'max_score' => 8,
            'strength' => $strength,
            'color' => $color,
            'percentage' => ($score / 8) * 100
        ];
    }

    /**
     * Check if password needs to be changed
     */
    public function shouldChangePassword(\DateTime $lastChanged): bool
    {
        $now = new \DateTime();
        $diff = $now->diff($lastChanged);
        
        return $diff->days >= self::MAX_AGE_DAYS;
    }

    /**
     * Get password policy description
     */
    public function getPolicyDescription(): string
    {
        $requirements = [];
        
        $requirements[] = "At least " . self::MIN_LENGTH . " characters";
        
        if (self::REQUIRE_UPPERCASE) {
            $requirements[] = "One uppercase letter (A-Z)";
        }
        
        if (self::REQUIRE_LOWERCASE) {
            $requirements[] = "One lowercase letter (a-z)";
        }
        
        if (self::REQUIRE_NUMBERS) {
            $requirements[] = "One number (0-9)";
        }
        
        if (self::REQUIRE_SYMBOLS) {
            $requirements[] = "One special character (!@#$%^&*)";
        }

        return "Password must contain: " . implode(", ", $requirements) . ".";
    }

    /**
     * Generate a secure random password
     */
    public function generateSecurePassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $password = '';
        
        // Ensure at least one character from each required set
        if (self::REQUIRE_UPPERCASE) {
            $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        }
        
        if (self::REQUIRE_LOWERCASE) {
            $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        }
        
        if (self::REQUIRE_NUMBERS) {
            $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        }
        
        if (self::REQUIRE_SYMBOLS) {
            $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        }

        // Fill remaining length with random characters
        $allChars = $uppercase . $lowercase . $numbers . $symbols;
        $remainingLength = $length - strlen($password);
        
        for ($i = 0; $i < $remainingLength; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle the password
        return str_shuffle($password);
    }
}
