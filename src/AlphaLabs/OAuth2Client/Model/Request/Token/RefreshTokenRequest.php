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
 * API Access token request, with refresh_token grant.
 *
 * Request which can be send to the API to request an access & refresh token, based on the refresh_token grant type.
 *
 * @package AlphaLabs\OAuth2Client\Model\Http
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
class RefreshTokenRequest extends TokenRequest
{
    /** @var string */
    private $refreshToken;

    /**
     * {@inheritDoc}
     *
     * @param string $refreshToken Refresh token
     */
    public function __construct($uri, $refreshToken, $method = 'POST')
    {
        parent::__construct($uri, $method);

        $this->refreshToken = $refreshToken;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenRequestParams()
    {
        return array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refreshToken
        );
    }
}
