<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlphaLabs\OAuth2Client\Model\Request\User;

use AlphaLabs\OAuth2Client\Model\Request\Request;

/**
 * API User-oriented Request.
 *
 * Request which can be send to the API and request resource which is user related.
 * (user-credentials/authorization-code grant)
 *
 * @package AlphaLabs\OAuth2Client\Model\Http
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
class UserRequest extends Request
{
    /** @var int Related user id */
    private $userId;

    /**
     * Sets the related user Id
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
     * Gets the related user Id
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }
}
