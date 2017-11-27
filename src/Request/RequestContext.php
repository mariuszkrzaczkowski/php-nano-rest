<?php

namespace GinoPane\NanoRest\Request;

use GinoPane\NanoRest\Supplemental\Headers;
use GinoPane\NanoRest\Exceptions\RequestContextException;

/**
 * Class RequestContext
 *
 * @package GinoPane\NanoRest\Request
 * @author Sergey <Gino Pane> Karavay
 */
class RequestContext
{

    /**
     * Default values for timeouts
     */
    const TIMEOUT_DEFAULT               = 10;
    const CONNECTION_TIMEOUT_DEFAULT    = 5;

    /**
     * Default values for charsets
     */
    const CHARSET_UTF8      = 'UTF-8';
    const CHARSET_ISO88591  = 'ISO-8859-1';

    /**
     * Sample HTTP Methods
     */
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
    const METHOD_PATCH   = 'PATCH';

    /**
     * Sample content types
     */
    const CONTENT_TYPE_FORM         = 'multipart/form-data';
    const CONTENT_TYPE_FORM_URLENCODED  = 'application/x-www-form-urlencoded';
    const CONTENT_TYPE_TEXT_PLAIN   = 'text/plain';
    const CONTENT_TYPE_JSON         = 'application/json';
    const CONTENT_TYPE_JAVASCRIPT   = 'application/javascript';
    const CONTENT_TYPE_APP_XML      = 'application/xml';
    const CONTENT_TYPE_TEXT_XML     = 'text/xml';
    const CONTENT_TYPE_TEXT_HTML    = 'text/html';

    /**
     * The list of supported HTTP methods
     *
     * @var array
     */
    private static $availableMethods = array(
         self::METHOD_OPTIONS,
         self::METHOD_GET,
         self::METHOD_HEAD,
         self::METHOD_POST,
         self::METHOD_PUT,
         self::METHOD_DELETE,
         self::METHOD_TRACE,
         self::METHOD_CONNECT,
         self::METHOD_PATCH
    );

    /**
     * Default content type for requests
     */
    private $contentType = self::CONTENT_TYPE_TEXT_PLAIN;

    /**
     * Default charset for requests
     *
     * @var string
     */
    private $charset = self::CHARSET_UTF8;

    /**
     * Preferred HTTP method
     *
     * @var string
     */
    private $method = self::METHOD_GET;

    /**
     * List of headers for a request
     *
     * @var Headers
     */
    private $headers = null;

    /**
     * Generic data to be sent
     *
     * @var mixed
     */
    private $data = null;

    /**
     * Parameters that should be appended to request URI
     *
     * @var array
     */
    private $requestParameters = [];

    /**
     * Options for transport
     *
     * @var array
     */
    private $transportOptions = [];

    /**
     * URI string for request
     *
     * @var string
     */
    private $uri = '';

    /**
     * Address of proxy server
     *
     * @var string
     */
    private $proxy = '';

    /**
     * URL prefix
     *
     * @var string
     */
    private $proxyScript = '';

    /**
     * Connection timeout
     *
     * @var int
     */
    private $connectionTimeout = self::CONNECTION_TIMEOUT_DEFAULT;

    /**
     * General timeout value to be used with the request
     *
     * @var
     */
    private $timeout = self::TIMEOUT_DEFAULT;

    /**
     * RequestContext constructor
     *
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->setUri($uri);
    }

    /**
     * Retrieve RequestContext's headers
     *
     * @return Headers
     */
    public function headers(): Headers
    {
        return $this->headers;
    }

    /**
     * Get headers prepared for request with Content-type assigned if it was not already set
     *
     * @return array
     */
    public function getRequestHeaders(): array
    {
        $headers = clone $this->headers();

        if (!$headers->headerExists('Content-type')) {
            $contentType = $this->getContentType();

            if ($charset = $this->getCharset()) {
                $contentType .= "; charset={$charset}";
            }

            $headers->setHeader('Content-type', $contentType);
        }

        return $headers->getHeadersForRequest();
    }

    /**
     * Set data for request
     *
     * @param mixed $data
     *
     * @return RequestContext
     */
    public function setData($data): RequestContext
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get previously set data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Override default HTTP method
     *
     * @param string $method
     *
     * @throws RequestContextException
     *
     * @return RequestContext
     */
    public function setMethod(string $method): RequestContext
    {
        if (!in_array($method, self::$availableMethods)) {
            throw new RequestContextException('Supplied HTTP method is not supported');
        }

        $this->method = $method;

        return $this;
    }

    /**
     * Get URI string
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Set URI string
     *
     * @param string $uri
     *
     * @return RequestContext
     */
    public function setUri(string $uri): RequestContext
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get URI string with request parameters applied
     *
     * @return string
     */
    public function getRequestUri(): string
    {
        $uri = $this->getUri();

        if ($this->getRequestParameters()) {
            $uri .= (strpos($uri, '?') === false ? '?' : '') . http_build_query($this->getRequestParameters());
        }

        return $uri;
    }

    /**
     * Get request params
     *
     * @return array
     */
    public function getRequestParameters(): array
    {
        return $this->requestParameters;
    }

    /**
     * Set an array of request params
     *
     * @param array $requestParameters
     *
     * @return RequestContext
     */
    public function setRequestParameters(array $requestParameters = array()): RequestContext
    {
        $this->requestParameters = $requestParameters;

        return $this;
    }

    /**
     * Get transport options
     *
     * @return array
     */
    public function getTransportOptions(): array
    {
        return $this->transportOptions;
    }

    /**
     * Set a single transport option for context
     *
     * @param $optionName
     * @param $optionValue
     *
     * @return RequestContext
     */
    public function setTransportOption($optionName, $optionValue): RequestContext
    {
        $this->transportOptions[$optionName] = $optionValue;

        return $this;
    }

    /**
     * Set an array of transport options for context
     *
     * @param array $transportOptions
     *
     * @return RequestContext
     */
    public function setTransportOptions(array $transportOptions): RequestContext
    {
        $this->transportOptions = $transportOptions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     *
     * @return RequestContext
     */
    public function setContentType($contentType): RequestContext
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Get charset for current request
     *
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Set charset for current request
     *
     * @param string $charset
     *
     * @return RequestContext
     */
    public function setCharset(string $charset): RequestContext
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Get string representation of RequestContext object
     *
     * @return string
     */
    public function __toString(): string
    {
        $headers = $this->headers()->getHeadersForRequest()
            ? print_r($this->headers()->getHeadersForRequest(), true)
            : "No headers were set";

        $data = $this->getData() ? print_r($this->getData(), true) : "No data was set";

        $requestParameters = $this->getRequestParameters()
            ? print_r($this->getRequestParameters(), true)
            : "No request parameters were set";

        return <<<DEBUG
===================        
Method: {$this->getMethod()}
URI: {$this->getUri()}
===================
Headers:

{$headers}
===================
Data:

{$data}
===================
Request Parameters:

{$requestParameters}
===================

DEBUG;
    }
}
