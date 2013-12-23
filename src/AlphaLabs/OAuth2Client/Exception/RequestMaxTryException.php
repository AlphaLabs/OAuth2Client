<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlphaLabs\OAuth2Client\Exception;

/**
 * Exception thrown when the client try to send a request too many times.
 *
 * @package AlphaLabs\OAuth2Client\Exception
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
class RequestMaxTryException extends \RuntimeException
{
}
