<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlphaLabs\OAuth2Client\Model\Request\Token;

/**
 * API Access token request, with password (users-credentials) grant.
 *
 * Request which can be send to the API to request an access & refresh token,
 * based on the password (user credentials) grant type.
 *
 * @package AlphaLabs\OAuth2Client\Model\Request\Token
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
class UserCredentialsTokenRequest extends TokenRequest
{
    /** @var string User name */
    private $username;
    /** @var string User password */
    private $password;

    /**
     * {@inheritDoc}
     *
     * @param string $username User login
     * @param string $password User password
     */
    public function __construct($uri, $username, $password, $method = 'POST')
    {
        parent::__construct($uri, $method);

        $this->username = $username;
        $this->password = $password;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenRequestParams()
    {
        return array(
            'grant_type' => 'password',
            'username' => $this->username,
            'password' => $this->password,
        );
    }
}
