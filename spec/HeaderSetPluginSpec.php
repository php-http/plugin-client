<?php

namespace spec\Http\Client\Plugin;

use PhpSpec\Exception\Example\SkippingException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HeaderSetPluginSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith([]);
        $this->shouldHaveType('Http\Client\Plugin\HeaderSetPlugin');
    }

    public function it_is_a_plugin()
    {
        $this->beConstructedWith([]);
        $this->shouldImplement('Http\Client\Plugin\Plugin');
    }

    public function it_set_the_header(RequestInterface $request)
    {
        $this->beConstructedWith([
            'foo'=>'bar',
            'baz'=>'qux'
        ]);

        $request->withHeader('foo', 'bar')->shouldBeCalled()->willReturn($request);
        $request->withHeader('baz', 'qux')->shouldBeCalled()->willReturn($request);

        $this->handleRequest($request, function () {}, function () {});
    }
}
