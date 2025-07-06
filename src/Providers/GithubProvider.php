<?php
namespace Happytodev\Authentik\Providers;

use League\OAuth2\Client\Provider\GenericProvider;

use function Tempest\env;

class GitHubProvider extends GenericProvider
{
    public function __construct(array $options = [], array $collaborators = [])
    {
        $options = array_merge([
            'clientId' => env('GITHUB_CLIENT_ID'),
            'clientSecret' => env('GITHUB_CLIENT_SECRET'),
            'redirectUri' => env('GITHUB_REDIRECT_URI', '/auth/github/callback'),
            'urlAuthorize' => 'https://github.com/login/oauth/authorize',
            'urlAccessToken' => 'https://github.com/login/oauth/access_token',
            'urlResourceOwnerDetails' => 'https://api.github.com/user',
        ], $options);

        parent::__construct($options, $collaborators);
    }
}
