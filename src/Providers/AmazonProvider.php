<?php

namespace Happytodev\Authentik\Providers;

use League\OAuth2\Client\Provider\GenericProvider;

use function Tempest\env;

class AmazonProvider extends GenericProvider
{
    public function __construct(array $options = [], array $collaborators = [])
    {
        $options = array_merge([
            'clientId' => env('AMAZON_CLIENT_ID'),
            'clientSecret' => env('AMAZON_CLIENT_SECRET'),
            'redirectUri' => env('AMAZON_REDIRECT_URI', '/auth/amazon/callback'),
            'urlAuthorize' => 'https://www.amazon.com/ap/oa',
            'urlAccessToken' => 'https://api.amazon.com/auth/o2/token',
            'urlResourceOwnerDetails' => 'https://api.amazon.com/user/profile',
            'scopes' => ['profile'], // Scope explicite
            'scopeSeparator' => ' ',
        ], $options);

        parent::__construct($options, $collaborators);
    }
}
