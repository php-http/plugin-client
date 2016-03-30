<?php

namespace Http\Client\Plugin;

use Http\Client\Plugin\Exception\ClientErrorException;
use Http\Client\Plugin\Exception\ServerErrorException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since version 1.1, to be removed in 2.0. Use {@link \Http\Client\Common\Plugin\ErrorPlugin} instead.
 */
class ErrorPlugin extends \Http\Client\Common\Plugin\ErrorPlugin implements Plugin
{
    /**
     * {@inheritdoc}
     */
    protected function transformResponseToException(RequestInterface $request, ResponseInterface $response)
    {
        if ($response->getStatusCode() >= 400 && $response->getStatusCode() < 500) {
            throw new ClientErrorException($response->getReasonPhrase(), $request, $response);
        }

        if ($response->getStatusCode() >= 500 && $response->getStatusCode() < 600) {
            throw new ServerErrorException($response->getReasonPhrase(), $request, $response);
        }

        return $response;
    }
}
