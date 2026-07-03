<?php

declare(strict_types=1);

namespace App\Services;

use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Protocol\Version2;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\ProtocolCollection;
use Illuminate\Support\Str;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Exception\RuleViolation;
use ParagonIE\Paseto\Rules\NotExpired;
use Illuminate\Support\Facades\Log;

final class PasetoService
{
    private SymmetricKey $key;

    public function __construct()
    {
        $this->key = new SymmetricKey(base64_decode(config('app.paseto_key')));
    }

    public function generateAccessToken($user)
    {
        try {
            return (new Builder())
                ->setVersion(new Version2())
                ->setPurpose(Purpose::local())
                ->setKey($this->key)
                ->set('jti', Str::uuid()->toString())
                ->set('user_id', $user->id)
                ->setIssuedAt()
                ->setExpiration(now()->addMinutes(10))
                ->toString();
        } catch (\Throwable $e) {
            Log::error('[PasetoService@generateAccessToken] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function generateRefreshToken($user) 
    {
        try {
            return (new Builder())
                ->setVersion(new Version2())
                ->setPurpose(Purpose::local())
                ->setKey($this->key)
                ->set('jti', Str::uuid()->toString())
                ->set('user_id', $user->id)
                ->set('type', 'refresh')
                ->setIssuedAt()
                ->setExpiration(now()->addDays(7))
                ->toString();
        } catch (\Throwable $e) {
            Log::error('[PasetoService@generateRefreshToken] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function parseToken($token)
    {
        try {
            try {
                return Parser::getLocal($this->key, ProtocolCollection::v2())
                    ->addRule(new NotExpired())
                    ->parse($token);
            } catch (RuleViolation $e) {
                throw new \Exception('Token Expired: ' . $e->getMessage());
            } catch (PasetoException $e) {
                throw new \Exception('Invalid token: ' . $e->getMessage());
            }
        } catch (\Throwable $e) {
            Log::error('[PasetoService@parseToken] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
