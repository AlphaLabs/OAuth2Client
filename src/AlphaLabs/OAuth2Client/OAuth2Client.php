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
use AlphaLabs\OAuth2Client\Model\Request\ResourceRequest;
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
    /** @var string API base URL */
    private $baseUrl;
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
        $this->baseUrl               = $apiBaseUrl;
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
        if ($request instanceof TokenRequest) {
            return $this->sendTokenRequest($request);
        } elseif (!$request instanceof ResourceRequest) {
            throw new \InvalidArgumentException(
                'The request to send must extends TokenRequest or ResourceRequest class.'
            );
        }

        $token = $this->retrieveAdaptedToken($request);

        $guzzleRequest = $this->buildGuzzleRequestFromRequest($request)
            ->setAuth(null)
            ->setHeader('Authorization', 'Bearer ' . $token->getAccessToken());

        try {
            $response = $guzzleRequest->send();

            return $this->deserialize(
                $response,
                $request->getDeserializationTargetClass(),
                $request->getPostDeserializationCallback()
            );

        } catch (BadResponseException $e) {
            if ($tryCount == self::REQUEST_MAX_TRY) {
                $exception = new RequestMaxTryException('Maximun request try reached.', 0, $e);

                throw $exception;
            }

            if ($e->getResponse()->getStatusCode() == 401) {
                // If the server respond with an unauthorized status code,
                // we assumes that the given access token is expired.
                // We'll try to renew the access token based on the refresh token

                $this->requestAccessToken(
                    new RefreshTokenRequest(
                        $token->getRefreshToken(),
                        $token->getUserId()
                    )
                );

                // Then the previous request is replayed.
                return $this->send($request, $tryCount + 1);
            }

            // If the exception doesn't match any previous case, it is thrown again.
            throw $e;
        }
    }

    private function sendTokenRequest(TokenRequest $request)
    {
        $guzzleRequest = $this->buildGuzzleRequestFromRequest($request);
        $guzzleRequest->setAuth($this->clientId, $this->clientSecret);

        $response = $guzzleRequest->send();

        return $this->deserialize(
            $response,
            $request->getDeserializationTargetClass(),
            $request->getPostDeserializationCallback()
        );
    }

    private function buildGuzzleRequestFromRequest(Request $request)
    {
        return $this->httpClient->createRequest(
            $request->getMethod(),
            $request->getUri(),
            $request->getHeaders(),
            $request->getBody(),
            $request->getOptions()
        );
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
     * @return Token
     */
    private function retrieveAdaptedToken(ResourceRequest $request)
    {
        if ($request instanceof UserRequest) {
            $userId = $request->getUserId();

            if (null === $token = $this->getUserToken($userId)) {
                throw new UserAuthenticationRequiredException();
            }
        } elseif ($request instanceof ClientRequest) {
            if (null === $token = $this->getClientToken()) {
                $this->requestAccessToken(
                    new ClientCredentialsTokenRequest()
                );

                $token = $this->clientToken;
            }
        } else {
            throw \LogicException('The ResourceRequests must extends ClientRequest or UserRequest classes');
        }

        return $token;
    }

    public function requestAccessToken(TokenRequest $tokenRequest)
    {
        $tokenRequest->setUri($this->accessTokenRequestUri);

        try {
            $token = $this->buildToken($this->send($tokenRequest));
        } catch (\Guzzle\Http\Exception\BadResponseException $e) {
            throw $e;
        }

        if ($token->isUserToken()) {
            $this->userTokens[$token->getUserId()] = $token;
        } else {
            $this->clientToken = $token;
        }

        $this->tokenManager->save($this->name, $token);
    }

    /**
     * Gets the curent client token
     *
     * @return Token|null
     */
    public function getClientToken()
    {
        if (!$this->clientToken instanceof Token) {
            $this->clientToken = $this->tokenManager->getClientToken($this->name);
        }

        return $this->clientToken;
    }

    /**
     * Gets the current token of the given user
     *
     * @param int $userId
     *
     * @return Token|null
     */
    public function getUserToken($userId)
    {
        if (!array_key_exists($userId, $this->userTokens)) {
            $token = $this->tokenManager->getUserToken($this->name, $userId);

            if ($token instanceof Token) {
                $this->userTokens[$userId] = $token;
            } else {
                $token = null;
            }
        } else {
            $token = $this->userTokens[$userId];
        }

        return $token;
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
    private function deserialize(GuzzleResponse $response, $targetClassNS = null, $postDeserializationCallback = null)
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

        if (null !== $postDeserializationCallback) {
            $postDeserializationCallback($responseData);
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

    /**
     * Gets the baseUrl attribute
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
