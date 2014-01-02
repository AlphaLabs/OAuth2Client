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

use AlphaLabs\OAuth2Client\Model\Security\Token;

/**
 * Class UserTokenRequest
 *
 * @package AlphaLabs\OAuth2Client\Model\Request\Token
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
abstract class UserTokenRequest extends TokenRequest
{
    /** @var int User id */
    private $userId;

    /**
     * Constructor
     *
     * @param string $uri
     * @param int    $userId
     * @param string $method
     */
    public function __construct($userId, $method = 'POST', $uri = '/')
    {
        parent::__construct($method, $uri);

        $this->userId = $userId;

        $this->setPostDeserializationCallback(function (Token $token) use ($userId) {
            $token->setUserId($userId);
        });
    }
}
