<?php

namespace App\Services;

use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Protocol\Version2;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\ProtocolCollection;
use Illuminate\Support\Str;

class PasetoService
{
    private SymmetricKey $key;

    public function __construct()
    {
        $this->key = new SymmetricKey(base64_decode(config('app.paseto_key')));
    }

    public function generateAccessToken($user)
    {
        return (new Builder())
            ->setVersion(new Version2())
            ->setPurpose(Purpose::local())
            ->setKey($this->key)
            ->set('jti', Str::uuid()->toString())
            ->set('user_id', $user->id)
            ->setIssuedAt()
            ->setExpiration(now()->addMinutes(10))
            ->toString();
    }

    public function generateRefreshToken($user) 
    {
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
    }

    public function parseToken($token)
    {
        return Parser::getLocal($this->key, ProtocolCollection::v2())
            ->parse($token);
    }
}
