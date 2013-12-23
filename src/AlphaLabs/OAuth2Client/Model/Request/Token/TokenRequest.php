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

use AlphaLabs\OAuth2Client\Model\Request\Request;

/**
 * API Access token request.
 *
 * Request which can be send to the API to request an access & refresh token.
 *
 * @package AlphaLabs\OAuth2Client\Model\Http
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
abstract class TokenRequest extends Request
{
    /**
     * Constructor
     *
     * @param string $uri
     * @param string $method
     */
    public function __construct($uri, $method = 'POST')
    {
        parent::__construct($method, $uri);

        $this->setBody($this->getTokenRequestParams());
    }

    /**
     * {@inheritDoc}
     */
    public function getDeserializationTargetClass()
    {
        return 'AlphaLabs\OAuth2Client\Model\Security\Token';
    }

    /**
     * Gets the query parameters used in the token request
     *
     * @return array
     */
    abstract protected function getTokenRequestParams();
}
