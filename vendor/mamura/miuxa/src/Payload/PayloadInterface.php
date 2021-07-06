<?php
namespace Miuxa\Payload;

interface PayloadInterface
{
    public function setData(string $data);
    public function setHeader(string $name, $value);
    public function setHeaders(array $headers);
    public function setStatus(int $status);
    public function getData();
    public function getHeaders();
    public function getStatus();
}
