<?php

namespace spec\Http\Client\Plugin;

use Http\Client\HttpClient;
use Http\Client\Plugin\Plugin;
use Psr\Http\Message\RequestInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PluginClientSpec extends ObjectBehavior
{
    function let(HttpClient $client)
    {
        $this->beConstructedWith($client);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Http\Client\Plugin\PluginClient');
    }

    function it_is_an_http_client()
    {
        $this->shouldImplement('Http\Client\HttpClient');
    }

    function it_is_an_http_async_client()
    {
        $this->shouldImplement('Http\Client\HttpAsyncClient');
    }

    function it_throws_loop_exception(HttpClient $client, RequestInterface $request, Plugin $plugin)
    {
        $plugin
            ->handleRequest(
                $request,
                Argument::type('callable'),
                Argument::type('callable')
            )
            ->will(function ($args) {
                return $args[2]($args[0]);
            })
        ;

        $this->beConstructedWith($client, [$plugin]);

        $this->shouldThrow('Http\Client\Plugin\Exception\LoopException')->duringSendRequest($request);
    }
}
