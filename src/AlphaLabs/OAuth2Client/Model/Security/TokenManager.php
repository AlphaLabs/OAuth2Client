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
 * Interface which describe the methods required for the token management.
 *
 * A token manager needs to retrieve the last client token (for client-oriented requests), the last user token (for
 * user-oriented requests) and should be able to persist a new Token object.
 *
 * @package AlphaLabs\OAuth2Client\Model\Security
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
interface TokenManager
{
    /**
     * Retrieve the current user-based API token, for user-oriented API calls usage.
     *
     * @param string $clientName
     * @param int    $userId
     *
     * @return Token|null
     */
    public function getUserToken($clientName, $userId);

    /**
     * Retrieve the current client-based token, for client credentials API calls usage.
     *
     * @param string $clientName
     *
     * @return Token|null
     */
    public function getClientToken($clientName);

    /**
     * Save the updated token
     *
     * @param string $clientName
     * @param Token  $token
     *
     * @return mixed
     */
    public function save($clientName, Token $token);
}
