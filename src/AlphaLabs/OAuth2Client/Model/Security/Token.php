<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlphaLabs\OAuth2Client\Model\Security;

/**
 * Class which represents token information.
 *
 * A Token is an object with several objects/info: Current access token & refresh token,
 * the scopes accessible with the current access token, the token authorization type (Bearer, Basic...), the expiration
 * time of the access token and the user id linked to the token (for user-related API calls).
 *
 * @package AlphaLabs\OAuth2Client\Model\Security
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
class Token
{
    /**
     * @var string
     * @JMS\Serializer\Annotation\Type("string")
     * @JMS\Serializer\Annotation\SerializedName("access_token")
     */
    private $accessToken;
    /**
     * @var int
     * @JMS\Serializer\Annotation\Type("integer")
     * @JMS\Serializer\Annotation\SerializedName("expires_in")
     */
    private $expirationDate;
    /**
     * @var string
     * @JMS\Serializer\Annotation\Type("string")
     * @JMS\Serializer\Annotation\SerializedName("token_type")
     */
    private $type;
    /**
     * @var string
     * @JMS\Serializer\Annotation\Type("string")
     */
    private $scope;
    /**
     * @var string
     * @JMS\Serializer\Annotation\Type("string")
     * @JMS\Serializer\Annotation\SerializedName("refresh_token")
     */
    private $refreshToken;

    /** @var int */
    private $userId;

    /**
     * Sets the accessToken attribute
     *
     * @param string $accessToken
     *
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Gets the accessToken attribute
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Sets the expirationDate attribute
     *
     * @param \DateTime $expirationDate
     *
     * @return $this
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Gets the expirationDate attribute
     *
     * @return \DateTime
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Sets the refreshToken attribute
     *
     * @param string $refreshToken
     *
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Gets the refreshToken attribute
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Sets the scope attribute
     *
     * @param string $scope
     *
     * @return $this
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Gets the scope attribute
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Sets the type attribute
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets the type attribute
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the userId attribute
     *
     * @param int $userId
     *
     * @return $this
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Gets the userId attribute
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
