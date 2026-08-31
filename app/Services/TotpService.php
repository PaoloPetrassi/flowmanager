<?php

namespace App\Services;

use InvalidArgumentException;

class TotpService
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public function generateSecret(int $length = 32): string
    {
        $bytes = random_bytes((int) ceil($length * 5 / 8));

        return substr($this->base32Encode($bytes), 0, $length);
    }

    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';

        if (strlen($code) !== 6) {
            return false;
        }

        $counter = (int) floor(time() / 30);

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals($this->at($secret, $counter + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    public function provisioningUri(string $secret, string $email): string
    {
        $issuer = rawurlencode((string) config('app.name', 'FlowManager'));
        $label = rawurlencode(config('app.name', 'FlowManager').':'.$email);

        return "otpauth://totp/{$label}?secret={$secret}&issuer={$issuer}&digits=6&period=30";
    }

    public function at(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);
        $binaryCounter = pack('N2', ($counter >> 32) & 0xFFFFFFFF, $counter & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $binaryCounter, $key, true);
        $offset = ord($hash[19]) & 0x0F;
        $binary = (
            ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF)
        );

        return str_pad((string) ($binary % 1_000_000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $data): string
    {
        $bits = '';

        foreach (str_split($data) as $char) {
            $bits .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }

        $result = '';

        foreach (str_split($bits, 5) as $chunk) {
            $chunk = str_pad($chunk, 5, '0');
            $result .= self::ALPHABET[bindec($chunk)];
        }

        return $result;
    }

    private function base32Decode(string $secret): string
    {
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/i', '', $secret) ?? '');

        if ($secret === '') {
            throw new InvalidArgumentException('Invalid TOTP secret.');
        }

        $bits = '';

        foreach (str_split($secret) as $char) {
            $position = strpos(self::ALPHABET, $char);

            if ($position === false) {
                throw new InvalidArgumentException('Invalid TOTP secret.');
            }

            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $result = '';

        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $result .= chr(bindec($byte));
            }
        }

        return $result;
    }
}
