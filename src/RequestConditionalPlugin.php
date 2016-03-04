<?php

namespace Http\Client\Plugin;

use Http\Message\Authentication;
use Http\Message\RequestMatcher;
use Psr\Http\Message\RequestInterface;

/**
 * Apply a delegated plugin based on a request match.
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class RequestConditionalPlugin implements Plugin
{
    /**
     * @var RequestMatcher
     */
    private $requestMatcher;

    /**
     * @var Plugin
     */
    private $delegatedPlugin;

    /**
     * @param RequestMatcher $requestMatcher
     * @param Plugin         $delegatedPlugin
     */
    public function __construct(RequestMatcher $requestMatcher, Plugin $delegatedPlugin)
    {
        $this->requestMatcher = $requestMatcher;
        $this->delegatedPlugin = $delegatedPlugin;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        if ($this->requestMatcher->matches($request)) {
            return $this->delegatedPlugin->handleRequest($request, $next, $first);
        }

        return $next($request);
    }
}
