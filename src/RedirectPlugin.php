<?php

namespace Http\Client\Plugin;

use Http\Client\Exception\HttpException;
use Http\Client\Plugin\Exception\CircularRedirectionException;
use Http\Client\Plugin\Exception\MultipleRedirectionException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since version 1.1, to be removed in 2.0. Use {@link \Http\Client\Common\Plugin\RedirectPlugin} instead.
 */
class RedirectPlugin extends \Http\Client\Common\Plugin\RedirectPlugin implements Plugin
{
    /**
     * {@inheritdoc}
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        // Check in storage
        if (array_key_exists($request->getRequestTarget(), $this->redirectStorage)) {
            $uri = $this->redirectStorage[$request->getRequestTarget()]['uri'];
            $statusCode = $this->redirectStorage[$request->getRequestTarget()]['status'];
            $redirectRequest = $this->buildRedirectRequest($request, $uri, $statusCode);

            return $first($redirectRequest);
        }

        return $next($request)->then(function (ResponseInterface $response) use ($request, $first) {
            $statusCode = $response->getStatusCode();

            if (!array_key_exists($statusCode, $this->redirectCodes)) {
                return $response;
            }

            $uri = $this->createUri($response, $request);
            $redirectRequest = $this->buildRedirectRequest($request, $uri, $statusCode);
            $chainIdentifier = spl_object_hash((object) $first);

            if (!array_key_exists($chainIdentifier, $this->circularDetection)) {
                $this->circularDetection[$chainIdentifier] = [];
            }

            $this->circularDetection[$chainIdentifier][] = $request->getRequestTarget();

            if (in_array($redirectRequest->getRequestTarget(), $this->circularDetection[$chainIdentifier])) {
                throw new CircularRedirectionException('Circular redirection detected', $request, $response);
            }

            if ($this->redirectCodes[$statusCode]['permanent']) {
                $this->redirectStorage[$request->getRequestTarget()] = [
                    'uri' => $uri,
                    'status' => $statusCode,
                ];
            }

            // Call redirect request in synchrone
            $redirectPromise = $first($redirectRequest);

            return $redirectPromise->wait();
        });
    }

    /**
     * Creates a new Uri from the old request and the location header.
     *
     * @param ResponseInterface $response The redirect response
     * @param RequestInterface  $request  The original request
     *
     * @throws HttpException                If location header is not usable (missing or incorrect)
     * @throws MultipleRedirectionException If a 300 status code is received and default location cannot be resolved (doesn't use the location header or not present)
     *
     * @return UriInterface
     */
    private function createUri(ResponseInterface $response, RequestInterface $request)
    {
        if ($this->redirectCodes[$response->getStatusCode()]['multiple'] && (!$this->useDefaultForMultiple || !$response->hasHeader('Location'))) {
            throw new MultipleRedirectionException('Cannot choose a redirection', $request, $response);
        }

        if (!$response->hasHeader('Location')) {
            throw new HttpException('Redirect status code, but no location header present in the response', $request, $response);
        }

        $location = $response->getHeaderLine('Location');
        $parsedLocation = parse_url($location);

        if (false === $parsedLocation) {
            throw new HttpException(sprintf('Location %s could not be parsed', $location), $request, $response);
        }

        $uri = $request->getUri();

        if (array_key_exists('scheme', $parsedLocation)) {
            $uri = $uri->withScheme($parsedLocation['scheme']);
        }

        if (array_key_exists('host', $parsedLocation)) {
            $uri = $uri->withHost($parsedLocation['host']);
        }

        if (array_key_exists('port', $parsedLocation)) {
            $uri = $uri->withPort($parsedLocation['port']);
        }

        if (array_key_exists('path', $parsedLocation)) {
            $uri = $uri->withPath($parsedLocation['path']);
        }

        if (array_key_exists('query', $parsedLocation)) {
            $uri = $uri->withQuery($parsedLocation['query']);
        } else {
            $uri = $uri->withQuery('');
        }

        if (array_key_exists('fragment', $parsedLocation)) {
            $uri = $uri->withFragment($parsedLocation['fragment']);
        } else {
            $uri = $uri->withFragment('');
        }

        return $uri;
    }
}
