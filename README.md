# Authentik

A package for TempestPHP to handle OAuth authentication with providers like 
- [x] GitHub
- [ ] Google
- [ ] X
- [x] Amazon

## Prerequisites

- TempestPHP installed
- TempestPHP Auth package installed
- Database setup
- OAuth provider credentials (Client ID, Client Secret, Redirect URI)

## Installation

You can install the package via composer:

```bash
composer require happytodev/authentik
```

As Tempest Auth User's model is final, you need to extend it and add the OauthFieldsTrait to your User model.

Just add the following `use OauthFieldsTrait;` to your User model:

```php
final class User implements CanAuthenticate, CanAuthorize
{
    use IsDatabaseModel;
    use OauthFieldsTrait; // Add the trait here
    ...
```

## Migration

Run the following command to add the necessary database fields:

```bash
php tempest migrate:up
```


## .env settings

Configure your .env file with the necessary settings:

```env
# Possible values: local, staging, production, ci, testing, other
ENVIRONMENT=local

# The base URI that's used for all generated URIs
BASE_URI=https://mytempestsite.test


GITHUB_CLIENT_ID=Ov23liz.............
GITHUB_CLIENT_SECRET=14a...................................
GITHUB_REDIRECT_URI=${BASE_URI}/auth/github/callback

AMAZON_CLIENT_ID='amzn1.application-oa2-client.ca50............................'
AMAZON_CLIENT_SECRET='amzn1.oa2-cs.v1.ed9a............................................................'
AMAZON_REDIRECT_URI=${BASE_URI}/auth/amazon/callback

# After success oauth authentication, route to redirect to
AUTHENTIK_REDIRECT_URI=${BASE_URI}/admin
```


## Usage

Create link to the login page in your view by calling the following address :

- [https://mytempestsite.test/auth/github](https://mytempestsite.test/auth/github)
- [https://mytempestsite.test/auth/amazon](https://mytempestsite.test/auth/amazon)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [Happytodev](https://github.com/happytodev)

