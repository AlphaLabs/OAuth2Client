<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlphaLabs\OAuth2Client;

use AlphaLabs\OAuth2Client\Exception\UserAuthenticationRequiredException;
use AlphaLabs\OAuth2Client\Model\Request\Token\RefreshTokenRequest;
use AlphaLabs\OAuth2Client\Exception\RequestMaxTryException;
use AlphaLabs\OAuth2Client\Model\Request\Client\ClientRequest;
use AlphaLabs\OAuth2Client\Model\Request\Request;
use AlphaLabs\OAuth2Client\Model\Request\Token\ClientCredentialsTokenRequest;
use AlphaLabs\OAuth2Client\Model\Request\Token\TokenRequest;
use AlphaLabs\OAuth2Client\Model\Request\User\UserRequest;
use AlphaLabs\OAuth2Client\Model\Security\Token;
use AlphaLabs\OAuth2Client\Model\Security\TokenManager;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;
use Guzzle\Http\Message\RequestInterface as GuzzleRequestInterface;
use Guzzle\Http\Message\Response as GuzzleResponse;

/**
 * Class OAuth2Client
 *
 * @package WishPad\OAuth2ClientBundle\Service
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
class OAuth2Client
{
    const REQUEST_MAX_TRY = 1;

    /** @var Client name */
    private $name;
    /** @var Client HTTP client */
    private $httpClient;
    /** @var string OAuth2 client id */
    private $clientId;
    /** @var string OAuth2 client secret */
    private $clientSecret;
    /** @var string URI which be used to request new access tokens */
    private $accessTokenRequestUri;
    /** @var TokenManager Token manager */
    private $tokenManager;
    /** @var Token Client API token */
    private $clientToken;
    /** @var array User API tokens */
    private $userTokens;
    /** @var SerializerInterface Serializer */
    private $serializer;
    /** @var string Serialization format*/
    private $format;

    /**
     * Constructor
     *
     * @param string       $name                  Client name
     * @param string       $apiBaseUrl            API base url
     * @param string       $clientId              Client identifier
     * @param string       $clientSecret          Client secret key
     * @param TokenManager $tokenManager          Token manager
     * @param string       $accessTokenRequestUri URI which be used to request new access tokens
     * @param string       $format                API request/response format (default: json)
     */
    public function __construct(
        $name,
        $apiBaseUrl,
        $clientId,
        $clientSecret,
        TokenManager $tokenManager,
        $accessTokenRequestUri,
        $format = 'json'
    ) {
        $this->name                  = $name;
        $this->httpClient            = new Client($apiBaseUrl);
        $this->clientId              = $clientId;
        $this->clientSecret          = $clientSecret;
        $this->tokenManager          = $tokenManager;
        $this->accessTokenRequestUri = $accessTokenRequestUri;
        $this->userTokens            = array();
        $this->format                = $format;
    }

    /**
     * Send a request to the API
     *
     * @param Request $request API Request to send
     *
     * @param int     $tryCount
     *
     * @throws Exception\RequestMaxTryException
     * @throws \Exception
     * @throws \Guzzle\Http\Exception\BadResponseException
     *
     * @return mixed Response data
     */
    public function send(Request $request, $tryCount = 0)
    {
        $guzzleRequest = $this->httpClient->createRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody(),
            $request->getOptions()
        );

        $this->addAuthorization($request, $guzzleRequest);

        try {
            $guzzleRequest->send();
        } catch (BadResponseException $e) {
            if ($tryCount == self::REQUEST_MAX_TRY) {
                $exception = new RequestMaxTryException('Maximun request try reached.', 0, $e);

                throw $exception;
            }

            if (!$request instanceof TokenRequest && $e->getResponse()->getStatusCode() == 401) {
                // If the request was a resource request (not an acces token request) and the server respond
                // with an unauthorized status code, we assumes that the given access code is expired.

                $this->refreshAccessToken($request);

                return $this->send($request, $tryCount + 1);
            }

            throw $e;
        }

        $response = $guzzleRequest->getResponse();

        $responseData = $this->deserialize($response, $request->getDeserializationTargetClass());

        return $responseData;
    }

    /**
     * Adds Authorization headers on the request
     *
     * @param Request                $request       Initial API request
     * @param GuzzleRequestInterface $guzzleRequest Forged Guzzle request
     */
    private function addAuthorization(Request $request, GuzzleRequestInterface $guzzleRequest)
    {
        if ($request instanceof TokenRequest) {
            $guzzleRequest->setAuth($this->clientId, $this->clientSecret);
        } elseif ($request instanceof UserRequest || $request instanceof ClientRequest) {
            $guzzleRequest->setAuth(null);

            $token = $this->getToken($request);

            $guzzleRequest->setHeader('Authorization', 'Bearer ' . $token->getAccessToken());
        }
    }

    /**
     * Gets the adapted token based on the request type.
     * Try to use the current oauth client tokens if they exists, otherwise use the token manager to retrieve
     * previously stored tokens.
     *
     * If no token was storred previously, try to request a new token form the server.
     *
     * @param Request $request Initial request
     *
     * @throws Exception\UserAuthenticationRequiredException If no User access token was previously stored.
     *
     * @return null|Token
     */
    private function getToken(Request $request)
    {
        if ($request instanceof UserRequest) {
            $token = null;
            $userId = $request->getUserId();

            if (!array_key_exists($userId, $this->userTokens)) {
                $token = $this->tokenManager->getUserToken($this->name, $userId);

                if ($token instanceof Token) {
                    $this->userTokens[$userId] = $token;
                } else {
                    throw new UserAuthenticationRequiredException();
                }
            }

            return $token;
        } elseif ($request instanceof ClientRequest) {
            if (!$this->clientToken instanceof Token) {
                $this->clientToken = $this->tokenManager->getClientToken($this->name);

                if (is_null($this->clientToken)) {
                    $this->requestAccessToken($request);
                }
            }

            return $this->clientToken;
        }

        return null;
    }

    /**
     * Try to request a fresh new Access token.
     *
     * @param Request $initialRequest Initial request. Used to get the request type to request the new token with an
     *                                adapted grant type.
     *
     * @throws \LogicException If an access token request is made from a non ClientRequest initial request
     */
    private function requestAccessToken(Request $initialRequest)
    {
        if (!$initialRequest instanceof ClientRequest) {
            throw new \LogicException('A new request access token must be initiated for client-credentials grants only');
        }

        $request = new ClientCredentialsTokenRequest($this->accessTokenRequestUri);

        $this->clientToken = $this->buildToken($this->send($request));

        $this->tokenManager->save($this->name, $this->clientToken);
    }

    /**
     * Try to request a new Access token, by using the current refresh token.
     *
     * @param Request $initialRequest Initial request. Used to get the request type to request the new token with an
     *                                adapted grant type.
     *
     * @throws \RuntimeException If no current Token object is available
     */
    private function refreshAccessToken(Request $initialRequest)
    {
        $token = null;

        if ($initialRequest instanceof UserRequest) {
            $userId = $initialRequest->getUserId();

            if (!array_key_exists($userId, $this->userTokens)) {
                throw new \RuntimeException('Current token must exists to be refreshed.');
            }

            $token = $this->userTokens[$userId];
        } elseif ($initialRequest instanceof ClientRequest) {
            if (!$this->clientToken instanceof Token) {
                throw new \RuntimeException('Current token must exists to be refreshed.');
            }

            $token = $this->clientToken;
        }

        if (!$token instanceof Token) {
            throw new \RuntimeException('Current token must exists to be refreshed.');
        }

        $request = new RefreshTokenRequest($this->accessTokenRequestUri, $token->getRefreshToken());

        try {
            $retreivedToken = $this->send($request);

            $newToken = $this->buildToken($retreivedToken);

            if ($initialRequest instanceof UserRequest) {
                $newToken->setUserId($initialRequest->getUserId());
                $this->userTokens[$initialRequest->getUserId()] = $newToken;
            } elseif ($initialRequest instanceof ClientRequest) {
                $this->clientToken = $newToken;
            }

            $this->tokenManager->save($this->name, $newToken);

        } catch (BadResponseException $e) {
            if ($e->getResponse()->isClientError()) {
                // If the server respond with an unauthorized status code, we assumes that the given refresh code
                // is expired or invalid. The current token is cleared and we try to get a new token

                $this->requestAccessToken($initialRequest);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Build a new Token object based on the type of the given token data.
     *
     * If $tokenData is already a Token instance, it will be directly returned.
     * Otherwise, a ne Token object is instanciate with the array values of $tokenData.
     *
     * @param Token|array $tokenData
     *
     * @return Token
     */
    private function buildToken($tokenData)
    {
        if ($tokenData instanceof Token) {
            return $tokenData;
        } else {
            // If the serializer isn't used, we build a new Token instance based on the reponse array
            $token = new Token();
            $token
                ->setAccessToken($tokenData['access_token'])
                ->setExpirationDate($tokenData['expires_in'])
                ->setType($tokenData['token_type'])
                ->setScope($tokenData['scope'])
                ->setRefreshToken($tokenData['refresh_token']);
        }

        return $token;
    }

    /**
     * Deserialize the response.
     *
     * If a serializer is set on the client, this method will try to deserialize the response into the target object
     * if thes class of this one is specified in the parameters.
     *
     * Otherwise, the response is deserialized into an associative array.
     *
     * @param GuzzleResponse $response
     * @param string|null    $targetClassNS
     *
     * @return mixed
     */
    private function deserialize(GuzzleResponse $response, $targetClassNS = null)
    {
        if (!is_null($this->serializer) && !is_null($targetClassNS)) {
            $responseData = $this->serializer->deserialize(
                $response->getBody(true),
                $targetClassNS,
                $this->format
            );
        } else {
            $responseData = json_decode($response->getBody(true), true);
        }

        return $responseData;
    }

    /**
     * Sets the serializer
     *
     * @param $serializer
     *
     * @throws \LogicException
     * @return $this
     */
    public function setSerializer($serializer)
    {
        if (!is_subclass_of($serializer, '\JMS\Serializer\SerializerInterface')) {
            throw new \LogicException('The serializer must implements \JMS\Serializer\SerializerInterface.');
        }

        $this->serializer = $serializer;

        return $this;
    }
}
