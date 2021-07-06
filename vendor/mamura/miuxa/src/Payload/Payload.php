<?php
namespace miuxa\Payload;

class Payload implements PayloadInterface
{
    protected $status;
    protected $data;
    protected $headers = [];

    public function setData(string $data)
    {
        $this->data = $data;
        return $this;
    }

    public function setHeader(string $name, $value)
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setJSON($data)
    {
        $this->data = json_encode($data);

        return $this;
    }

    public function getData()
    {
        return $this->data ?? '';
    }

    public function getHeaders()
    {
        return $this->headers;
    }
    public function getStatus()
    {
        return $this->status;
    }
}
