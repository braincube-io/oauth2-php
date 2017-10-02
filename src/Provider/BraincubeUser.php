<?php

namespace League\OAuth2\Client\Provider;

/**
 * Class BraincubeUser: Represents an Authenticated Braincube user with OAuth2.0.
 * @package League\OAuth2\Client\Provider
 */
class BraincubeUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @param  array $response
     */
    public function __construct(array $response)
    {
        $this->data = $response;
    }

    /**
     * Returns the ID for the user as a string if present.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->getField('userEmail');
    }

    /**
     * Returns the email for the user as a string if present.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getField('userEmail');
    }

    /**
     * Returns the full name for the user as a string if present
     *
     * @return mixed|null
     */
    public function getFullName()
    {
        return $this->getField('userFullName');
    }

    /**
     * Returns the allowed products for the user as an array if present
     *
     * @return array
     */
    public function getAllowedProducts()
    {
        return $this->getField('allowedProducts');
    }

    /**
     * Returns all the data obtained about the user.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Returns a field from the User node data.
     *
     * @param string $key
     *
     * @return mixed|null
     */
    private function getField($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }
}
