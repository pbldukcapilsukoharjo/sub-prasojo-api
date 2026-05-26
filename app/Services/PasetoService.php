<?php

namespace App\Services;

use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Protocol\Version2;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\ProtocolCollection;

class PasetoService
{
    protected $key;

    public function __construct()
    {
        $this->key = new SymmetricKey(base64_decode(str_replace('base64:', '', env('PASETO_KEY'))));
    }

    public function generateToken($user)
    {
        return (new Builder())
            ->setVersion(new Version2())
            ->setPurpose(Purpose::local())
            ->setKey($this->key)
            ->setIssuedAt()
            ->setExpiration((new \DateTime())->modify('+1 day'))
            ->set('user_id', $user->id)
            ->toString();
    }

    public function parseToken($token)
    {
        return Parser::getLocal($this->key, ProtocolCollection::v2())
            ->parse($token);
    }
}
