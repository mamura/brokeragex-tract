<?php
namespace miuxa;

use miuxa\Http\Response;
use miuxa\Payload\PayloadInterface;

class ViewResponder
{
    protected $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function respond(PayloadInterface $payload)
    {
        foreach ($payload->getHeaders() as $headerName => $headerValue) {
            $this->response->withHeader($headerName, $headerValue);
        }

        $this->response->withStatus(200);
        $this->response->setBody($payload->getData());

        return $this->response;
    }
}
