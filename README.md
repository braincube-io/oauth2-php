# Braincube Provider for OAuth 2.0 Client

This package provides Braincube OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

This package is compliant with [PSR-1][], [PSR-2][], [PSR-4][], and [PSR-7][]. If you notice compliance oversights,
please send a patch via pull request.

[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[PSR-4]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md
[PSR-7]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md


## Requirements

* [Composer](https://getcomposer.org/) 1.4.1
* PHP 7.0

## Installation

Add the following to your `composer.json` file.

```json
{
    "require": {
        "braincube-io/oauth2-php": "1.0.0"
    }
}
```

## Usage

### Authorization Code Flow

```php
session_start();

require __DIR__ . '/vendor/autoload.php';

$provider = new \League\OAuth2\Client\Provider\Braincube([
    'clientId'          => '{braincube-app-id}', // The client ID assigned to you by the provider
    'clientSecret'      => '{braincube-app-secret}', // The client password assigned to you by the provider
    'redirectUri'       => 'https://example.com/callback-url'
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    if (isset($_SESSION['token'])) {
        unset($_SESSION['token']);
    }

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    echo '<a href="'.$authorizationUrl.'">Log in with Braincube!</a>';
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

    if (isset($_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
    }

    exit('Invalid state');

} else {

    try {

        if (isset($_SESSION['token'])) {
            echo 'Access Token: ' . $_SESSION['token'] . "<br>";
            echo '<a href="/">Logout!</a>';
        } else {
            // Try to get an access token using the authorization code grant.
            $accessToken = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
    
            // We have an access token, which we may use in authenticated
            // requests against the service provider's API.
            echo 'Access Token: ' . $accessToken->getToken() . "<br>";
            echo 'Refresh Token: ' . $accessToken->getRefreshToken() . "<br>";
            echo 'Expired in: ' . $accessToken->getExpires() . "<br>";
            echo 'Already expired? ' . ($accessToken->hasExpired() ? 'expired' : 'not expired') . "<br>";
    
            // Using the access token, we may look up details about the
            // resource owner.
            $resourceOwner = $provider->getResourceOwner($accessToken);
    
            prettify($resourceOwner->toArray());
    
            // The provider provides a way to get an authenticated API request for
            // the service, using the access token; it returns an object conforming
            // to Psr\Http\Message\RequestInterface.
            $request = $provider->getAuthenticatedRequest(
                'GET',
                'https://yourapi',
                $accessToken
            );
     
            $response = $provider->getParsedResponse($request);
            prettify($response);
    
            $_SESSION['token'] = $accessToken->getToken();
        }

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }

}

function prettify($code) {
    echo "<pre>";
    var_export($code);
    echo "</pre>";
}

```

### The BraincubeUser Entity

When using the `getResourceOwner()` method to obtain the user node, it will be returned as a `BraincubeUser` entity.

```php
$user = $provider->getResourceOwner($token);

$email = $user->getEmail();
var_dump($email);
# string(15) "thezuck@foo.com"

$fullName = $user->getFullName();
var_dump($fullName);
# string(8) "The Zuck"

$allowedProducts = $user->getAllowedProducts();
var_dump($allowedProducts);
# array
```

You can also get all the data from the User node as a plain-old PHP array with `toArray()`.

```php
$userData = $user->toArray();
```

## Testing

``` bash
$ ./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/braincube-io/oauth2-php/blob/master/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](https://github.com/braincube-io/oauth2-php/blob/master/LICENSE) for more information.
