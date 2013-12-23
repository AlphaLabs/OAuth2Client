<?php
/*
 * This file licensed under the MIT license.
 *
 * (c) Sylvain Mauduit <swop@swop.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AlphaLabs\OAuth2Client\Model\Request;

/**
 * API Request.
 *
 * Represents a request which can be send to the OAuth2-secured API.
 *
 * @package AlphaLabs\OAuth2Client\Model\Request
 *
 * @author  Sylvain Mauduit <swop@swop.io>
 */
abstract class Request
{
    /** @var string HTTP method */
    private $method;
    /** @var string URI of the resource */
    private $uri;
    /** @var array Additional headers */
    private $headers;
    /** @var mixed Request body (string/array of post data) */
    private $body;
    /** @var array Additional options (Guzzle Request options) */
    private $options;
    /** @var string Fully classified class name of the target object */
    private $deserializationTargetClass;

    /**
     * @param string $method
     * @param string $uri
     */
    public function __construct($method, $uri)
    {
        $this->method  = $method;
        $this->uri     = $uri;
        $this->options = array();
    }

    /**
     * Sets the body attribute
     *
     * @param mixed $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Gets the body attribute
     *
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the headers attribute
     *
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Adds an additional header to the headers bag
     *
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * Gets the headers attribute
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Gets the method attribute
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the options attribute
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Gets the options attribute
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Gets the uri attribute
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * If the response to this request must be deserialized into an object, this method should return
     * the fully classified class name of the target object.
     *
     * If the response should be return as an array, the method should return null.
     *
     * @return string|null
     */
    public function getDeserializationTargetClass()
    {
        return $this->deserializationTargetClass;
    }

    /**
     * Sets the deserializationTargetClass attribute
     *
     * @param string $deserializationTargetClass
     *
     * @return $this
     */
    public function setDeserializationTargetClass($deserializationTargetClass)
    {
        $this->deserializationTargetClass = $deserializationTargetClass;

        return $this;
    }
}
