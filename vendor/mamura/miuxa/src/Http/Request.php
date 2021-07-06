<?php
namespace miuxa\Http;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

class Request implements RequestInterface
{
    use MessageTrait;

    protected $params = [];
    protected $method;
    protected $pathInfo;
    protected $requestUri;
    protected $baseUrl;
    protected $basePath;

    public function __construct()
    {
        $this->method   = $_SERVER['REQUEST_METHOD'];
        $this->pathInfo = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $dirname        = dirname($_SERVER['SCRIPT_NAME']);
        
        if ($dirname != '/') {
            $this->pathInfo = str_replace($dirname, '', $this->pathInfo);
        }

        if (empty($this->pathInfo)) {
            $this->pathInfo = '/';
        } elseif (!$this->pathInfo != '/') {
            $this->pathInfo = '/' . $this->pathInfo . '/';
        }
        
        $this->pathInfo = preg_replace('/\/+/', '/', $this->pathInfo);
        
        switch ($this->method) {
            case 'GET':
                $this->params = $_GET;
                break;

            case 'POST':
                $this->params = $_POST;

                if (!empty($_GET)) {
                    $this->params = array_merge($_GET, $this->params);
                }
                break;
        }
    }

    public function url()
    {
        //$page
    }

    public function bindParams(array $parameters)
    {
        $this->params = array_merge($this->params, $parameters);

        return $this;
    }

    public function getParam(string $name, $default = null)
    {
        $result = $this->params[$name] ?? $default;
        return is_string($result) ? trim($result) : $result;
    }

    public function getParams()
    {
        $result = [];
        foreach ($this->params as $param => $value) {
            if (is_string($this->params[$param])) {
                $this->params[$param] = trim($value);
            }
        }

        return $this->params;
    }

    public function getRequestTarget()
    {
        //to do
    }

    public function withRequestTarget($requestTarget)
    {
        //to do
    }
    
    public function getMethod()
    {
        //to do
    }
    
    public function withMethod($method)
    {
        //to do
    }

    public function getUri()
    {
        //to do
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        //to do
    }

    /** Message Interface */
    public function getProtocolVersion()
    {
        //to do
    }

    public function withProtocolVersion($version)
    {
        //to do
    }

    public function getHeaders()
    {
        //to do
    }

    public function hasHeader($name)
    {
        //to do
    }

    public function getHeader($name)
    {
        //to do
    }

    public function getHeaderLine($name)
    {
        //to do
    }

    public function withHeader($name, $value)
    {
        //to do
    }

    public function withAddedHeader($name, $value)
    {
        //to do
    }

    public function withoutHeader($name)
    {
        //to do
    }

    public function getBody()
    {
        //to do
    }

    /**
     * Set a valid HTTP method
     */
    private function setMethod($method) : void
    {
        if (!is_string($method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method; must be a string, received %s',
                is_object($method) ? get_class($method) : gettype($method)
            ));
        }

        if (! preg_match('/^[!#$%&\'*+.^_`\|~0-9a-z-]+$/i', $method)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported HTTP method "%s" provided',
                $method
            ));
        }
        $this->method = $method;
    }

    /**
     * Create a new URI instance
     */
    private function createUri($uri) : UriInterface
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }

        if (is_string($uri)) {
            return new Uri($uri);
        }

        if ($uri === null) {
            return new Uri();
        }

        throw new InvalidArgumentException(
            'Invalid URI provided; must be null, a string, or a Psr\Http\Message\UriInterface instance'
        );
    }
}
