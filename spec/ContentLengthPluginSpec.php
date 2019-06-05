<?php

namespace spec\Http\Client\Plugin;

use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class ContentLengthPluginSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Http\Client\Plugin\ContentLengthPlugin');
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    function it_adds_content_length_header(RequestInterface $request, StreamInterface $stream)
    {
        $request->hasHeader('Content-Length')->shouldBeCalled()->willReturn(false);
        $request->getBody()->shouldBeCalled()->willReturn($stream);
        $stream->getSize()->shouldBeCalled()->willReturn(100);
        $request->withHeader('Content-Length', 100)->shouldBeCalled()->willReturn($request);

        $this->handleRequest($request, function () {}, function () {});
    }

    function it_streams_chunked_if_no_size(RequestInterface $request, StreamInterface $stream)
    {
        $request->hasHeader('Content-Length')->shouldBeCalled()->willReturn(false);
        $request->getBody()->shouldBeCalled()->willReturn($stream);

        $stream->getSize()->shouldBeCalled()->willReturn(null);
        $request->withBody(Argument::type('Http\Message\Encoding\ChunkStream'))->shouldBeCalled()->willReturn($request);
        $request->withAddedHeader('Transfer-Encoding', 'chunked')->shouldBeCalled()->willReturn($request);

        $this->handleRequest($request, function () {}, function () {});
    }
}
