<?php
namespace miuxa\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    private const ERRORS = [
        UPLOAD_ERR_OK           => 1,
        UPLOAD_ERR_INI_SIZE     => 1,
        UPLOAD_ERR_FORM_SIZE    => 1,
        UPLOAD_ERR_PARTIAL      => 1,
        UPLOAD_ERR_NO_FILE      => 1,
        UPLOAD_ERR_NO_TMP_DIR   => 1,
        UPLOAD_ERR_CANT_WRITE   => 1,
        UPLOAD_ERR_EXTENSION    => 1,
    ];

    private $clientFileName;
    private $clientMediaType;
    private $error;
    private $file;
    private $moved = false;
    private $size;
    private $stream;

    public function __construct($streamOrFile, $size, $errorStatus, $clientFileName = null, $clientMediaType = null)
    {
        if (is_int($errorStatus) == false || isset(self::ERRORS[$errorStatus])) {
            throw new InvalidArgumentException(
                'Upload file error status must be an integer value and one of "UPLOAD_ERR_* constants.'
            );
        }

        if (is_int($size) == false) {
            throw new InvalidArgumentException('Upload file size must be an integer');
        }

        if ($clientFileName !== null && !is_string($clientFileName)) {
            throw new InvalidArgumentException('Upload file client filename must be a string or null');
        }

        if ($clientMediaType !== null && !is_string($clientMediaType)) {
            throw new InvalidArgumentException('Upload file client media type must be a string or null');
        }

        $this->error            = $errorStatus;
        $this->size             = $size;
        $this->clientFileName   = $clientFileName;
        $this->clientMediaType  = $clientMediaType;

        if ($this->error == UPLOAD_ERR_OK) {
            if (is_string($streamOrFile)) {
                $this->file = $streamOrFile;
            } elseif (is_resource($streamOrFile)) {
                $this->stream = Stream::create($streamOrFile);
            } elseif ($streamOrFile instanceof StreamInterface) {
                $this->stream = $streamOrFile;
            } else {
                throw new InvalidArgumentException('Invalid stream of file provided for UploadedFile');
            }
        }
    }

    public function getStream() : StreamInterface
    {
        $this->validadeActive();

        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        }

        $resource = fopen($this->file, 'r');

        return Stream::create($resource);
    }

    public function moveTo($targetPath) : void
    {
        $this->validadeActive();

        if (!is_string($targetPath) || $targetPath == '') {
            throw new InvalidArgumentException(
                'Invalid path provided fir move operation must be a non-empty string'
            );
        }

        if ($this->file !== null) {
            $this->moved = 'cli' === PHP_SAPI
            ? rename($this->file, $targetPath)
            : move_uploaded_file($this->file, $targetPath);
        } else {
            $stream = $this->getStream();
            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            $dest = Stream::create(fopen($targetPath, 'w'));
            while (!$stream->eof()) {
                if (!$dest->write($stream->read(1048576))) {
                    break;
                }
            }

            $this->moved = true;
        }

        if ($this->moved == false) {
            throw new RuntimeException(sprintf('Uploaded file cold not be moved to %s', $targetPath));
        }
    }

    public function getSize() : int
    {
        return $this->size;
    }

    public function getError() : int
    {
        return $this->error;
    }

    public function getClientFilename() : ?string
    {
        return $this->clientFileName;
    }

    public function getClientMediaType() : ?string
    {
        return $this->clientMediaType;
    }

    private function validadeActive() : void
    {
        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->moved) {
            throw new RuntimeException('Cannor retrieve stream after it has already been moved');
        }
    }
}
