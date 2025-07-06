<?php

namespace Happytodev\Authentik\Traits;

trait OauthFieldsTrait
{
    public ?string $oauth_provider = null;
    public ?string $oauth_user_id = null;
    public ?string $oauth_user_email = null;
    public ?string $oauth_user_data = null;
    public ?string $oauth_access_token = null;
    public ?string $oauth_refresh_token = null;
}
