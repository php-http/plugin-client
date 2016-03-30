<?php

namespace Http\Client\Plugin;

use Http\Message\Authentication;

/**
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 *
 * @deprecated since version 1.1, to be removed in 2.0. Use {@link \Http\Client\Common\Plugin\AuthenticationPlugin} instead.
 */
class AuthenticationPlugin extends \Http\Client\Common\Plugin\AuthenticationPlugin implements Plugin
{
}
