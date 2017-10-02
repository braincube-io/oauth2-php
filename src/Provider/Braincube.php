<?php

namespace League\OAuth2\Client\Provider;

use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Braincube: Represents a Braincube provider (authorization server)
 * @package League\OAuth2\Client\Provider
 */
class Braincube extends AbstractProvider
{
    protected $baseBraincubeUrl;

    public function getBaseAuthorizationUrl()
    {
        return $this->getBaseBraincubeUrl().'/vendors/braincube/authorize.jsp';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->getBaseBraincubeUrl().'/ws/oauth2/token';
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return $this->getBaseBraincubeUrl().'/ws/oauth2/me';
    }

    public function getDefaultScopes()
    {
        return ['api', 'base'];
    }

    protected function getAuthorizationHeaders($token = null)
    {
        return ['Authorization' => 'Bearer ' . $token];
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new BraincubeUser($response);
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $error = $data['error'];

            if (is_array($error)) {
                $message = $error['type'] . ': ' . $error['message'];
                throw new IdentityProviderException($message, $data['error']['code'], $data);
            } else {
                throw new IdentityProviderException($error, -1, $data);
            }
        }
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    /**
     * Get the base Braincube URL.
     *
     * @return string
     */
    private function getBaseBraincubeUrl()
    {
        return $this->baseBraincubeUrl;
    }

    public function __construct(array $options = [], array $collaborators = [])
    {
        if (empty($options['baseBraincubeUrl'])) {
            $options['baseBraincubeUrl'] = 'https://mybraincube.com/sso-server';
        }

        parent::__construct($options, $collaborators);
    }
}
