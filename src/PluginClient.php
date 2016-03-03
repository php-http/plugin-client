<?php

namespace Http\Client\Plugin;

use Http\Client\Exception;
use Http\Client\Plugin\Exception\LoopException;
use Psr\Http\Message\RequestInterface;

/**
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since version 1.1, to be removed in 2.0. Use {@link \Http\Client\Common\PluginClient} instead.
 */
final class PluginClient extends \Http\Client\Common\PluginClient
{
    /**
     * {@inheritdoc}
     *
     * Throw the correct loop exception.
     */
    protected function createLoopException(RequestInterface $request)
    {
        return new LoopException('Too many restarts in plugin client', $request);
    }
}
