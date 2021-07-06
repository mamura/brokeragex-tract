<?php
namespace miuxa\Http;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    private $uriFactory;
    private $streamFactory;
    private $uploadedFileFactory;

    public function __construct()
    {
        $this->uriFactory           = new UriFactory();
        $this->streamFactory        = new StreamFactory();
        $this->uploadedFileFactory  = new UploadedFileFactory();
    }

    public function createServerRequest(string $method, $uri, array $serverParams = []) : ServerRequest
    {
        return new ServerRequest($method, $uri, [], null, '1.1', $serverParams);
    }

    public function fromGlobals()
    {
        $server = $_SERVER;
        if (false === isset($server['REQUEST_METHOD'])) {
            $server['REQUEST_METHOD'] = 'GET';
        }

        $headers    = function_exists('getallheaders') ? getallheaders() : static::getHeadersFromServer($_SERVER);
        $post       = null;

        if ($this->getMethodFromEnv($server) === 'POST') {
            foreach ($headers as $headerName => $headerValue) {
                if (strtolower($headerName) !== 'content-type') {
                    continue;
                }

                $arrayValue = strtolower(trim(explode(';', $headerValue, 2)[0]));
                if (in_array($arrayValue, ['application/x-www-form-urlencoded', 'multipart/form-data'])) {
                    $post = $_POST;

                    break;
                }
            }
        }

        return $this->fromArrays($server, $headers, $_COOKIE, $_GET, $post, $_FILES, fopen('php://input', 'r') ?: null);
    }

    public static function getHeadersFromServer(array $server) : array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (strpos($key, 'REDIRECT_') === 0) {
                $key = substr($key, 9);

                if (array_key_exists($key, $server)) {
                    continue;
                }
            }

            if ($value && strpos($key, 'HTTP_') === 0) {
                $name           = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[$name] = $value;
                continue;
            }

            if ($value && strpos($key, 'CONTENT_')) {
                $name           = 'content-' . strtolower(substr($key, 8));
                $headers[$name] = $value;
                continue;
            }
        }

        return $headers;
    }

    public function fromArrays(
        array $server,
        array $headers = [],
        array $cookie = [],
        array $get = [],
        ?array $post = null,
        array $files = [],
        $body = null
    ) : ServerRequestInterface {
        $method         = $this->getMethodFromEnv($server);
        $uri            = $this->getUriFromEnvWithHttp($server);
        $protocol       = isset($server['SERVER_PROTOCOL'])
            ? str_replace('HTTP', '', $server['SERVER_PROTOCOL'])
            : '1.1';
        $serverRequest  = $this->createServerRequest($method, $uri, $server);

        foreach ($headers as $name => $value) {
            if (is_int($name)) {
                $name = (string) $name;
            }
            
            $serverRequest = $serverRequest->withAddedHeader($name, $value);
        }

        $serverRequest = $serverRequest
                            ->withProtocolVersion($protocol)
                            ->withCookieParams($cookie)
                            ->withQueryParams($get)
                            ->withParsedBody($post)
                            ->withUploadedFiles($this->normalizeFiles($files));

        if ($body == null) {
            return $serverRequest;
        }

        if (is_resource($body)) {
            $body = $this->streamFactory->createStreamFromResource($body);
        } elseif (!$body instanceof StreamInterface) {
            throw new InvalidArgumentException(
                'The body parameter to ServerRequestCreator::fromArrays must be a string, resource or StreamInterface'
            );
        }

        return $serverRequest->withBody($body);
    }

    private function getMethodFromEnv(array $server) : string
    {
        if (isset($server['REQUEST_METHOD']) === false) {
            throw new InvalidArgumentException('Cannot determine HTTP method.');
        }

        return $server['REQUEST_METHOD'];
    }

    private function getUriFromEnvWithHttp(array $server) : UriInterface
    {
        $uri = $this->createUriFromArray($server);
        if (empty($uri->getScheme())) {
            $uri = $uri->withScheme('http');
        }

        return $uri;
    }

    private function createUriFromArray(array $server) : UriInterface
    {
        $uri = $this->uriFactory->createUri('');

        if (isset($server['HTTP_X_FORWARDED_PROTO'])) {
            $uri = $uri->withScheme($server['HTTP_X_FORWARDED_PROTO']);
        } else {
            if (isset($server['REQUEST_SCHEME'])) {
                $uri = $uri->withScheme($server['REQUEST_SCHEME']);
            } elseif (isset($server['HTTPS'])) {
                $uri = $uri->withScheme($server['HTTPS'] === 'on' ? 'https' : 'http');
            }

            if (isset($server['SERVER_PORT'])) {
                $uri = $uri->withPort($server['SERVER_PORT']);
            }

            if (isset($server['QUERY_STRING'])) {
                $uri = $uri->withQuery($server['QUERY_STRING']);
            }
        }

        return $uri;
    }

    private function normalizeFiles(array $files) : array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = $this->createUploadedFileFromSpec($value);
            } elseif (is_array($value)) {
                $normalized[$key] = $this->normalizeFiles($value);
            } else {
                throw new InvalidArgumentException('Invalid value in file specification.');
            }
        }

        return $normalized;
    }

    private function createUploadedFileFromSpec(array $value)
    {
        if (is_array($value['tmp_name'])) {
            return $this->normalizeNestedFileSpec($value);
        }

        try {
            $stream = $this->streamFactory->createStreamFromFile($value['tmp_name']);
        } catch (RuntimeException $e) {
            $stream = $this->streamFactory->createStream();
        }

        return $this->uploadedFileFactory->createUploadedFile(
            $stream,
            (int) $value['size'],
            (int) $value['error'],
            $value['name'],
            $value['type']
        );
    }

    private function normalizeNestedFileSpec(array $files = []) : array
    {
        $normalizedFiles = [];

        foreach (array_keys($files['tpm_name']) as $key) {
            $spec = [
                'tmp_name'  => $files['tmp_name'][$key],
                'size'      => $files['size'][$key],
                'error'     => $files['error'][$key],
                'name'      => $files['name'][$key],
                'type'      => $files['type'][$key]
            ];
        
            $normalizedFiles[$key] = $this->createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }
}
