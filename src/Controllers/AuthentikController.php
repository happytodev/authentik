<?php

namespace Happytodev\Authentik\Controllers;

use Tempest\Router\Get;
use App\Auth\User;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Container\Proxy;
use Tempest\Http\Responses\Redirect;
use Happytodev\Authentik\Providers\AmazonProvider;
use Happytodev\Authentik\Providers\GitHubProvider;
use Tempest\Auth\Authenticator;

use function Tempest\env;

class AuthentikController
{
    public function __construct(
        #[Proxy] private AmazonProvider $amazonProvider,
        #[Proxy] private GitHubProvider $githubProvider,
    ) {}

    // Amazon authentication route
    #[Get('/auth/amazon')]
    public function amazonLogin(): Response
    {
        $authorizationUrl = $this->amazonProvider->getAuthorizationUrl();
        return new Redirect($authorizationUrl);
    }

    // Amazon callback route
    #[Get('/auth/amazon/callback')]
    public function amazonCallback(Request $request, Authenticator $authenticator): Response
    {
        return $this->handleOAuth2Callback('amazon', $request, $authenticator);
    }

    // GitHub authentication routes
    #[Get('/auth/github')]
    public function githubLogin(): Response
    {
        $authorizationUrl = $this->githubProvider->getAuthorizationUrl();
        return new Redirect($authorizationUrl);
    }

    // Github callback route
    #[Get('/auth/github/callback')]
    public function githubCallback(Request $request, Authenticator $authenticator): Response
    {
        return $this->handleOAuth2Callback('github', $request, $authenticator);
    }


    // Handle OAuth 2.0 callback for GitHub and Amazon
    private function handleOAuth2Callback(string $provider, Request $request, Authenticator $authenticator): Response
    {
        $providerInstance = match ($provider) {
            'github' => $this->githubProvider,
            'amazon' => $this->amazonProvider,
            default => throw new \Exception("Invalid provider: {$provider}"),
        };

        $accessToken = $providerInstance->getAccessToken('authorization_code', [
            'code' => $request->get('code'),
        ]);

        $userData = $providerInstance->getResourceOwner($accessToken)->toArray();

        $user = $this->findOrCreateUser($provider, $userData, $accessToken);

        $authenticator->login($user);

        return new Redirect(env('AUTHENTIK_REDIRECT_URI', '/dashboard'));
    }

    // Find or create user based on email
    private function findOrCreateUser(string $provider, array $userData, $accessToken): User
    {
        // Normalize user data for consistency
        $normalizedData = $this->normalizeUserData($provider, $userData);

        // Find user by email
        $user = User::select()
            ->where('email == ?', $normalizedData['email'])
            ->first();

        if (!$user) {
            // Create new user if none exists
            $user = new User(
                name: $normalizedData['name'] ?? 'Unknown',
                email: $normalizedData['email'] ?? null
            );
            $user->password = ''; // No password for OAuth users
        }

        // Update OAuth information
        $user->oauth_provider = $provider;
        $user->oauth_user_id = $normalizedData['id'];
        $user->oauth_user_email = $normalizedData['email'] ?? null;
        $user->oauth_user_data = json_encode($userData);
        $user->oauth_access_token = $this->getAccessTokenForProvider($provider, $accessToken);
        $user->oauth_refresh_token = $this->getRefreshTokenForProvider($provider, $accessToken);

        // Save user (create or update)
        $user->save();

        return $user;
    }

    // Normalize user data for different providers
    private function normalizeUserData(string $provider, array $userData): array
    {
        return match ($provider) {
            'amazon' => [
                'name' => $userData['name'] ?? 'Inconnu',
                'email' => $userData['email'] ?? null,
                'id' => $userData['user_id'] ?? null,
            ],
            'github' => [
                'name' => $userData['name'] ?? $userData['login'],
                'email' => $userData['email'] ?? null,
                'id' => (string) $userData['id'],
            ],
            default => throw new \Exception("Invalid provider: {$provider}"),
        };
    }

    // Get access token based on provider
    private function getAccessTokenForProvider(string $provider, $accessToken): ?string
    {
        if ($provider === 'x') {
            return $accessToken['oauth_token'] ?? null;
        }
        return $accessToken->getToken();
    }

    // Get refresh token based on provider
    private function getRefreshTokenForProvider(string $provider, $accessToken): ?string
    {
        if ($provider === 'x') {
            return $accessToken['oauth_token_secret'] ?? null;
        }
        return $accessToken->getRefreshToken() ?? null;
    }
}
