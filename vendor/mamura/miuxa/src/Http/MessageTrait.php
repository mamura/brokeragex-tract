<?php
namespace miuxa\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;

use function strtolower;
use function is_int;

trait MessageTrait
{
    private $headers        = [];
    private $headerNames    = [];
    private $protocol       = '1.1';
    private $stream;

    public function getProtocolVersion() : string
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version) : self
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }

    public function hasHeader($header) : bool
    {
        return isset($this->headerNames[strtolower($header)]);
    }

    public function getHeader($header) : array
    {
        $header = strtolower($header);
        if (!isset($headerNames[$header])) {
            return [];
        }

        $header = $this->headerNames[$header];
        return $this->headers[$header];
    }

    public function getHeaderLine($header) : string
    {
        $value = $this->getHeader($header);
        if (empty($value)) {
            return '';
        }

        return implode(', ', $value);
    }

    public function withHeader($header, $value) : self
    {
        $value = strtolower($this->validateHeader($header, $value));
        $new = clone $this;

        if (isset($new->headerNames[$value])) {
            unset($new->headers[$new->headerNames[$value]]);
        }

        $new->headerNames[$value] = $header;
        $new->headers[$header] = $value;

        return $new;
    }

    public function withAddedHeader($header, $value) : self
    {
        if (!is_string($header) || '' === $header) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        $new = clone $this;
        $new->setHeaders([$header => $value]);

        return $new;
    }

    public function withoutHeader($header) : self
    {
        $normalized = strtolower($header);
        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $header = $this->headerNames[$normalized];
        $new = clone $this;
        unset($new->headers[$header], $new->headerNames[$normalized]);

        return $new;
    }

    public function getBody() : StreamInterface
    {
        if (null === $this->stream) {
            $this->stream = Stream::create('');
        }

        return $this->stream;
    }

    public function withBody(StreamInterface $body) : self
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;

        return $new;
    }
    

    private function setHeaders(array $originalHeaders) : void
    {
        $headerNames = $headers = [];

        foreach ($originalHeaders as $header => $value) {
            if (is_int($header)) {
                $header = (string) $header;
            }

            $value = $this->validateHeader($header, $value);

            $headerNames[strtolower($header)]   = $header;
            $headers[$header]                   = $value;
        }

        $this->headerNames  = $headerNames;
        $this->headers      = $headers;
    }

    private function validateHeader($header, $values) : array
    {
        if (!is_string($header) || 1 !== preg_match("@^[!#$%&'*+.^_`|~0-9A-Za-z-]+$@", $header)) {
            throw new InvalidArgumentException('Header name must be an RFC 7230 compatible string.');
        }

        if (!is_array($values)) {
            if ((!is_numeric($values)
                && !is_string($values))
                || 1 !== preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $values)
            ) {
                throw new InvalidArgumentException('Header values must be RFC 7230 compatible strings.');
            }

            return [trim((string) $values, " \t")];
        }

        if (empty($values)) {
            throw new InvalidArgumentException(
                'Header values must be a string or an array of strings, empty array given.'
            );
        }

        $returnValues = [];

        foreach ($values as $v) {
            if (!is_numeric($v) && !is_string($v) || 1 !== preg_match("@^[ \t\x21-\x7E\x80-\xFF]*$@", (string) $v)) {
                throw new InvalidArgumentException(
                    'Header values must be RFC 7230 compatible strings.'
                );
            }

            $returnValues[] = trim((string) $v, " \t");
        }

        return $returnValues;
    }
}
