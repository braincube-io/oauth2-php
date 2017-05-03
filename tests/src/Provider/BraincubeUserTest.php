<?php

namespace League\OAuth2\Client\Test\Provider;

use League\OAuth2\Client\Provider\BraincubeUser;

class BraincubeUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BraincubeUser
     */
    protected $user;

    protected function setUp()
    {
        $this->user = new BraincubeUser([
            'id' => 'foo@bar.com',
            'userEmail' => 'foo@bar.com',
            'userFullName' => 'Foo Bar',
            'allowedProducts' => array(
                array(
                    'id' => 'cbf23eb1-dc44-4658-a439-fb3227713e05',
                    'name' => 'demo'
                )
            )
        ]);
    }

    public function testGetters()
    {
        $this->assertEquals('foo@bar.com', $this->user->getId());
        $this->assertEquals('foo@bar.com', $this->user->getEmail());
        $this->assertEquals('Foo Bar', $this->user->getFullName());
        $this->assertEquals(
            [['id' => 'cbf23eb1-dc44-4658-a439-fb3227713e05', 'name' => 'demo']],
            $this->user->getAllowedProducts()
        );
    }

    public function testCanGetAllDataBackAsAnArray()
    {
        $data = $this->user->toArray();

        $expectedData = [
            'id' => 'foo@bar.com',
            'userEmail' => 'foo@bar.com',
            'userFullName' => 'Foo Bar',
            'allowedProducts' => [['id' => 'cbf23eb1-dc44-4658-a439-fb3227713e05', 'name' => 'demo']]
        ];

        $this->assertEquals($expectedData, $data);
    }
}
