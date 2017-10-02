<?php

namespace League\OAuth2\Client\Test\Provider;

use Mockery as m;
use League\OAuth2\Client\Provider\Braincube;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class FooBraincubeProvider extends Braincube
{
    protected function fetchResourceOwnerDetails(AccessToken $token)
    {
        return json_decode('{"userEmail": "foo@bar.com", "userFullName": "Foo Bar", "allowedProducts": [{ "id": "cbf23eb1-dc44-4658-a439-fb3227713e05", "name": "demo" }]}', true);
    }
}

class BraincubeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Braincube
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Braincube([
            'clientId' => 'mock_client_id',
            'clientSecret' => 'mock_secret',
            'redirectUri' => 'none'
        ]);
    }

    public function tearDown()
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testGetBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        $this->assertEquals('/sso-server/ws/oauth2/token', $uri['path']);
    }

    public function testResourceOwnerDetailsUrl()
    {
        $token = $this->mockAccessToken();
        $url = $this->provider->getResourceOwnerDetailsUrl($token);
        $uri = parse_url($url);
        $this->assertEquals('/sso-server/ws/oauth2/me', $uri['path']);
        $this->assertNotContains('mock_access_token', $url);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getHeader')
            ->times(1)
            ->andReturn('application/json');
        $response->shouldReceive('getBody')
            ->times(1)
            ->andReturn('{"access_token":"mock_access_token","refresh_token":"mock_refresh_token","token_type":"bearer","expires_in":3600}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals("mock_refresh_token", $token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId(), 'Braincube does not return user ID with access token. Expected null.');
    }

    public function testScopes()
    {
        $this->assertEquals(['api', 'base'], $this->provider->getDefaultScopes());
    }

    public function testUserData()
    {
        $provider = new FooBraincubeProvider();

        $token = m::mock('League\OAuth2\Client\Token\AccessToken');
        $user = $provider->getResourceOwner($token);

        $this->assertEquals('foo@bar.com', $user->getId($token));
        $this->assertEquals('foo@bar.com', $user->getEmail($token));
        $this->assertEquals('Foo Bar', $user->getFullName($token));
        $this->assertEquals(
            array(array('id' => 'cbf23eb1-dc44-4658-a439-fb3227713e05', 'name' => 'demo')),
            $user->getAllowedProducts($token)
        );
    }

    public function testProperlyHandlesErrorResponses()
    {
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getHeader')
                 ->times(1)
                 ->andReturn('application/json');
        $postResponse->shouldReceive('getBody')
                     ->times(1)
                     ->andReturn('{"error":{"message":"Foo auth error","type":"OAuthException","code":191}}');

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $errorMessage = '';
        $errorCode = 0;

        try {
            $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        } catch (IdentityProviderException $e) {
            $errorMessage = $e->getMessage();
            $errorCode = $e->getCode();
        }

        $this->assertEquals('OAuthException: Foo auth error', $errorMessage);
        $this->assertEquals(191, $errorCode);
    }

    /**
     * @return AccessToken
     */
    private function mockAccessToken()
    {
        return new AccessToken([
            'access_token' => 'mock_access_token',
        ]);
    }
}
