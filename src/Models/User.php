<?php

namespace Happytodev\Authentik\Models;

use App\Auth\User as BaseUser;
use Tempest\Database\IsDatabaseModel;

class User extends BaseUser
{
    use IsDatabaseModel;

    public ?string $oauth_provider;
    public ?string $oauth_user_id;
    public ?string $oauth_user_email;
    public ?string $oauth_user_data;
    public ?string $oauth_access_token;
    public ?string $oauth_refresh_token;
}