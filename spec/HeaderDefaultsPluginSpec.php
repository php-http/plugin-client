<?php

namespace spec\Http\Client\Plugin;

use PhpSpec\Exception\Example\SkippingException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HeaderDefaultsPluginSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('Http\Client\Plugin\HeaderDefaultsPlugin');
    }

    public function it_is_a_plugin()
    {
        $this->beConstructedWith([]);
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    public function it_sets_the_default_header(RequestInterface $request)
    {
        $this->beConstructedWith([
            'foo' => 'bar',
            'baz' => 'qux'
        ]);

        $request->hasHeader('foo')->shouldBeCalled()->willReturn(false);
        $request->withHeader('foo', 'bar')->shouldBeCalled()->willReturn($request);
        $request->hasHeader('baz')->shouldBeCalled()->willReturn(true);

        $this->handleRequest($request, function () {}, function () {});
    }
}
