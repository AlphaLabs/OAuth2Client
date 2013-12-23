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
 * API Access token request, with client-credentials grant.
 *
 * Request which can be send to the API to request an access & refresh token, based on the client-credentials grant type.
 *
 * @package AlphaLabs\OAuth2Client\Model\Request\Token
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
class ClientCredentialsTokenRequest extends TokenRequest
{
    /**
     * {@inheritDoc}
     */
    protected function getTokenRequestParams()
    {
        return array(
            'grant_type' => 'client_credentials'
        );
    }
}
