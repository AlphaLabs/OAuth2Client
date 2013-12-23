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
 * API Access token request, with auth-code grant.
 *
 * Request which can be send to the API to request an access & refresh token, based on the auth-code grant type.
 *
 * @package AlphaLabs\OAuth2Client\Model\Request\Token
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
class AuthorizationCodeTokenRequest extends TokenRequest
{
    /** @var string */
    private $authCode;
    /** @var string */
    private $redirectUri;

    /**
     * {@inheritDoc}
     *
     * @param string $authCode    Authorization code
     * @param string $redirectUri Optional redirect URI. The URI is optional but can be required by the server,
     *                            depending on the security strategy.
     */
    public function __construct($uri, $authCode, $redirectUri = null, $method = 'POST')
    {
        parent::__construct($uri, $method);

        $this->authCode    = $authCode;
        $this->redirectUri = $redirectUri;
    }

    /**
     * {@inheritDoc}
     */
    protected function getTokenRequestParams()
    {
        $params = array(
            'grant_type' => 'authorization_code',
            'code' => $this->authCode
        );

        if (!is_null($this->redirectUri)) {
            $params = array_merge($params, array('redirect_uri' => $this->redirectUri));
        }

        return $params;
    }
}
