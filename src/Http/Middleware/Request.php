<?php

namespace Frijj2k\LarAlexa\Http\Middleware;

use Closure;
use Frijj2k\LarAlexa\Contracts\AlexaRequest;
use Frijj2k\LarAlexa\Http\Routing\AlexaRouter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Pipeline\Pipeline;
use Symfony\Component\HttpFoundation\Response;

class Request
{
    /**
     * Application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * Router instance.
     *
     * @var AlexaRouter
     */
    protected $router;

    /**
     * Array of middleware.
     *
     * @var array
     */
    protected $middleware;

    /**
     * @var \Frijj2k\LarAlexa\Contracts\AlexaRequest
     */
    private $alexaRequest;

    /**
     * @param Application  $app
     * @param AlexaRouter  $router
     * @param AlexaRequest $alexaRequest
     * @param array        $middleware
     */
    public function __construct(Application $app, AlexaRouter $router, AlexaRequest $alexaRequest, array $middleware)
    {
        $this->app = $app;
        $this->router = $router;
        $this->middleware = $middleware;
        $this->alexaRequest = $alexaRequest;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->alexaRequest->isAlexaRequest()) {
            return $this->sendRequestThroughRouter($request);
        }

        return $next($request);
    }

    /**
     * Send through our custom router
     *
     * @param $request
     *
     * @return Response
     */
    protected function sendRequestThroughRouter($request)
    {
        $this->app->instance('request', $request);

        return (new Pipeline($this->app))->send($request)->through($this->middleware)->then(function ($request) {
            return $this->router->dispatch($this->alexaRequest);
        });
    }
}
