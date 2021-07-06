<?php
namespace miuxa\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return Stream::create($content);
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = @fopen($filename, $mode);
        if ($resource === false) {
            if ($mode == '' || in_array($mode[0], ['r', 'w', 'a', 'x', 'c'])) {
                throw new InvalidArgumentException('The mode ' . $mode . ' is invalid.');
            }

            throw new RuntimeException('The file ' . $filename . 'cannot be opened.');
        }

        return Stream::create($resource);
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return Stream::create($resource);
    }
}
