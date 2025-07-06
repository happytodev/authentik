<?php

namespace Happytodev\Authentik\Controllers;

use Tempest\Router\Get;
use function Tempest\map;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Mapper\Mapper;
use Tempest\Container\Proxy;
use Tempest\Http\Responses\Ok;
use Tempest\Auth\Authenticator;
use Tempest\Http\Responses\Redirect;
use Happytodev\Authentik\Models\User;

use Happytodev\Authentik\Providers\GitHubProvider;

class AuthentikController
{
    public function __construct(
        #[Proxy] private GitHubProvider $githubProvider
    ) {}

    #[Get('/auth/github')]
    public function githubLogin(): Response
    {
        $authorizationUrl = $this->githubProvider->getAuthorizationUrl();
        return new Redirect($authorizationUrl);
    }

    #[Get('/auth/github/callback')]
    public function githubCallback(Request $request, Authenticator $authenticator): Response
    {
        // Retrieve the access token
        $accessToken = $this->githubProvider->getAccessToken('authorization_code', [
            'code' => $request->get('code'),
        ]);

        // Retrieve user data from GitHub
        $userData = $this->githubProvider->getResourceOwner($accessToken)->toArray();
        ll($userData);

        // Find or create the user
        $user = $this->findOrCreateUser('github', $userData, $accessToken);

        // Connect the user with the Tempest authenticator
        $authenticator->login($user);

        return new Redirect('/admin');
    }

    private function findOrCreateUser(string $provider, array $userData, $accessToken): User
    {
        // Search for an existing user
        $user = User::select()
            ->where('oauth_provider == ?', $provider)
            ->andWhere('oauth_user_id == ?', (string) $userData['id'])
            ->first();


        if (!$user) {
            // Create a new user if none exists
            $user = new User(
                name: $userData['name'],
                email: $userData['email']
            );
            $user->password = ''; // No password for OAuth users
            $user->oauth_provider = $provider;
            $user->oauth_user_id = (string) $userData['id'];
            $user->oauth_user_email = $userData['email'] ?? null;
            $user->oauth_user_data = json_encode($userData);
            $user->oauth_access_token = $accessToken->getToken();
            $user->oauth_refresh_token = $accessToken->getRefreshToken() ?? null;
            $user->save();
        } else {
            // Update tokens if the user exists
            $user->oauth_access_token = $accessToken->getToken();
            $user->oauth_refresh_token = $accessToken->getRefreshToken() ?? $user->oauth_refresh_token;
            $user->save();
        }

        return $user;
    }
}
