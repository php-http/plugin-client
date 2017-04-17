# Implement your own

When writing your own Plugin, you need to be aware that the Plugin Client is async first.
This means that every plugin must be written with Promises. More about this later.

Each plugin must implement the `Http\Client\Plugin\Plugin` interface.

This interface defines the `handleRequest` method that allows to modify behavior of the call:

```php
/**
 * Handles the request and returns the response coming from the next callable.
 *
 * @param RequestInterface $request Request to use.
 * @param callable         $next    Callback to call to have the request, it muse have the request as it first argument.
 * @param callable         $first   First element in the plugin chain, used to to restart a request from the beginning.
 *
 * @return Promise
 */
public function handleRequest(RequestInterface $request, callable $next, callable $first);
```

The `$request` comes from an upstream plugin or Plugin Client itself.
You can replace it and pass a new version downstream if you need.

!!! note "Note:"
    Be aware that the request is immutable.


The `$next` callable is the next plugin in the execution chain. When you need to call it, you must pass the `$request`
as the first argument of this callable.

For example a simple plugin setting a header would look like this:

``` php
public function handleRequest(RequestInterface $request, callable $next, callable $first)
{
    $newRequest = $request->withHeader('MyHeader', 'MyValue');

    return $next($newRequest);
}
```

The `$first` callable is the first plugin in the chain. It allows you to completely reboot the execution chain, or send
other request if needed, while still going through all the defined plugins.
Like in case of the `$next` callable, you must pass the `$request` as the first argument.

```
public function handleRequest(RequestInterface $request, callable $next, callable $first)
{
    if ($someCondition) {
        $newRequest = new Request();
        $promise = $first($newRequest);

        // Use the promise do some jobs ...
    }

    return $next($request);
}
```

!!! warning "Warning:"
    In this example the condition is not superfluous:
    you need to have some way to not call the `$first` callable each time
    or you will end up in an infinite execution loop.

The `$next` and `$first` callable will return a Promise (defined in `php-http/promise`).
You can manipulate the `ResponseInterface` or the `Exception` by using the `then` method of the promise.

```
public function handleRequest(RequestInterface $request, callable $next, callable $first)
{
    $newRequest = $request->withHeader('MyHeader', 'MyValue');

    return $next($request)->then(function (ResponseInterface $response) {
        return $response->withHeader('MyResponseHeader', 'value');
    }, function (Exception $exception) {
        echo $exception->getMessage();

        throw $exception;
    });
}
```

!!! warning "Warning:"
    Contract for the `Http\Promise\Promise` is temporary until
    [PSR is released](https://groups.google.com/forum/?fromgroups#!topic/php-fig/wzQWpLvNSjs).
    Once it is out, we will use this PSR in HTTPlug and deprecate the old contract.


To better understand the whole process check existing implementations in the
[plugin repository](https://github.com/php-http/plugins).
