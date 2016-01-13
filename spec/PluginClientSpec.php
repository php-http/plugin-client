<?php

namespace spec\Http\Client\Plugin;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Client\Plugin\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;

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

    function it_sends_request_with_underlying_client(HttpClient $client, RequestInterface $request, ResponseInterface $response)
    {
        $client->sendRequest($request)->willReturn($response);

        $this->sendRequest($request)->shouldReturn($response);
    }

    function it_sends_async_request_with_underlying_client(HttpAsyncClient $asyncClient, RequestInterface $request, Promise $promise)
    {
        $asyncClient->sendAsyncRequest($request)->willReturn($promise);

        $this->beConstructedWith($asyncClient);
        $this->sendAsyncRequest($request)->shouldReturn($promise);
    }

    function it_sends_async_request_if_no_send_request(HttpAsyncClient $asyncClient, RequestInterface $request, ResponseInterface $response, Promise $promise)
    {
        $this->beConstructedWith($asyncClient->getWrappedObject());
        $asyncClient->sendAsyncRequest($request)->willReturn($promise);
        $promise->wait()->willReturn($response);

        $this->sendRequest($request)->shouldReturn($response);
    }

    function it_prefers_send_request(StubClient $client, RequestInterface $request, ResponseInterface $response)
    {
        $client->sendRequest($request)->willReturn($response);

        $this->sendRequest($request)->shouldReturn($response);
    }

    function it_throws_loop_exception(HttpClient $client, RequestInterface $request)
    {
        $this->beConstructedWith($client, [new LoopPlugin()]);

        $this->shouldThrow('Http\Client\Plugin\Exception\LoopException')->duringSendRequest($request);
    }

    function it_adds_a_plugin(Plugin $plugin)
    {
        $this->addPlugin($plugin);

        $this->getPlugins()->shouldReturn([$plugin]);
    }

    function it_sets_new_plugins()
    {
        $plugins = [new LoopPlugin];
        $this->setPlugins($plugins);

        $this->getPlugins()->shouldReturn($plugins);
    }
}
