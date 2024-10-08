<?php

declare (strict_types=1);
namespace LassoLiteVendor\Http\Client\Common;

use LassoLiteVendor\Psr\Http\Client\ClientInterface;
use LassoLiteVendor\Psr\Http\Message\RequestInterface;
use LassoLiteVendor\Psr\Http\Message\ResponseInterface;
/**
 * Decorates an HTTP Client.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
trait HttpClientDecorator
{
    /**
     * @var ClientInterface
     */
    protected $httpClient;
    /**
     * {@inheritdoc}
     *
     * @see ClientInterface::sendRequest
     */
    public function sendRequest(RequestInterface $request) : ResponseInterface
    {
        return $this->httpClient->sendRequest($request);
    }
}
