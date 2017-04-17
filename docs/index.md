# HTTPlug Plugins

[HTTPlug](http://httplug.io) is an HTTP Client abstraction layer for PHP.

The plugin system allows to wrap a Client and add some processing logic prior to and/or after sending the actual
request or you can even start a completely new request. This gives you full control over what happens in your workflow.


## Install

Install the plugin client in your project with [Composer](https://getcomposer.org/):

``` bash
$ composer require "php-http/plugins"
```


## How it works

In the plugin package, you can find the following content:

- the Plugin Client itself which acts as a wrapper around any kind of HTTP Client (sync/async)
- a Plugin interface
- a set of core plugins (see the full list in the left side navigation)

The Plugin Client accepts an HTTP Client implementation and an array of plugins.

Let's see an example:

``` php
use Http\Discovery\HttpClientDiscovery;
use Http\Client\Plugin\PluginClient;
use Http\Client\Plugin\RetryPlugin;
use Http\Client\Plugin\RedirectPlugin;

$retryPlugin = new RetryPlugin();
$redirectPlugin = new RedirectPlugin();

$pluginClient = new PluginClient(
    HttpClientDiscovery::find(),
    [
        $retryPlugin,
        $redirectPlugin,
    ]
);
```

The Plugin Client accepts and implements both `Http\Client\HttpClient` and `Http\Client\HttpAsyncClient`, so you can use
both ways to send a request. In case the passed client implements only one of these interfaces, the Plugin Client
"emulates" the other behavior as a fallback.

It is important, that the order of plugins matter. During the request, plugins are called in the order they have
been added, from first to last. Once a response has been received, they are called again in a reversed order,
from last to first.

In case of our previous example, the execution chain will look like this:

```
Request  ---> PluginClient ---> RetryPlugin ---> RedirectPlugin ---> HttpClient ----
                                                                                   | (processing call)
Response <--- PluginClient <--- RetryPlugin <--- RedirectPlugin <--- HttpClient <---
```

In order to have correct behavior over the global process, you need to understand well how each plugin used,
and manage a correct order when passing the array to the Plugin Client.

Retry Plugin will be best at the end to optimize the retry process, but it can also be good
to have it as the first plugin, if one of the plugins is inconsistent and may need a retry.

The recommended way to order plugins is the following:

 1. Plugins that modify the request should be at the beginning (like Authentication or Cookie Plugin)
 2. Plugins which intervene in the workflow should be in the "middle" (like Retry or Redirect Plugin)
 3. Plugins which log information should be last (like Logger or History Plugin)

!!! note "Note:"
    There can be exceptions to these rules. For example,
    for security reasons you might not want to log the authentication information (like `Authorization` header)
    and choose to put the Authentication Plugin after the Logger Plugin.
