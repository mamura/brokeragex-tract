<?php
namespace miuxa\Http;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response implements ResponseInterface
{
    protected $body;
    protected $statusCode     = 200;
    protected $headers        = [];
    protected $headerNames    = [];
    protected $removeHeaders  = [];
    protected $protocol       = '1.1';

    public function getProtocolVersion() : string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version) : MessageInterface
    {
        $new            = clone $this;
        $new->protocol  = $version;
        return $new;
    }

    public function hasHeader($name) : bool
    {
        return isset($this->headersNames[strtolower($name)]);
    }

    public function getHeader($name) : array
    {
        return $this->hasHeader($name) ? $this->headers[$this->headersNames[strtolower($name)]] : [];
    }

    public function getHeaderLine($name) : string
    {
        $value = $this->getHeader($name);
        return empty($value) ? '' : implode(',', $value);
    }

    public function withHeader($name, $value) : MessageInterface
    {
        $new = clone $this;
        if ($new->hasHeader($name)) {
            unset($new->headers[$new->headerNames[strtolower($value)]]);
        }

        $new->headerNames[strtolower($value)]   = $name;
        $new->headers[$name]                    = $value;

        return $new;
    }

    public function withAddedHeader($name, $value) : MessageInterface
    {
        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $header = $this->headerNames[strtolower($name)];
                            
        $new                    = clone $this;
        $new->headers[$name]    = array_merge($this->headers[$name], $value);
        
        return $new;
    }

    public function withoutHeader($name) : MessageInterface
    {
        $new = clone $this;

        if ($this->hasHeader($name)) {
            $original = $this->headerNames[strtolower($name)];
            unset($new->headers[$original], $new->headerNames[strtolower($name)]);
        }

        return $new;
    }
    public function getBody()
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body) : MessageInterface
    {
        $new = clone $this;
        $new->setBody($body);
        return $new;
    }

    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $new = clone $this;
        $new->statusCode = $code;
        return $new;
    }
    
    public function getReasonPhrase()
    {
        return null;
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }
    
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function withJson($body)
    {
        $this->withHeader('Content-Type', 'application/json');
        $this->body = json_encode($body);

        return $this;
    }

    public function removeHeader($name)
    {
        $this->removeHeaders[] = $name;
        return $this;
    }

    public function send()
    {
        foreach ($this->removeHeaders as $headerName) {
            header_remove($headerName);
        }

        header(sprintf(
            'HTTP/%s %s %s',
            '1.1',
            $this->getStatusCode(),
            ''
        ));

        foreach ($this->getHeaders() as $header) {
            header($header[0] . ': ' . $header[1]);
        }

        echo $this->getBody();
        return $this->getBody();
    }

    public function __toString()
    {
        return $this->send();
    }
}
