<?php
namespace Flatten;

use Closure;
use Symfony\Component\HttpKernel\TerminableInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FlattenMiddleware implements TerminableInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var EventHandler
     */
    private $events;

    /**
     * @param Context      $context
     * @param EventHandler $events
     */
    public function __construct(Context $context, EventHandler $events)
    {
        $this->context = $context;
        $this->events = $events;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Cancel if Flatten shouldn't run here
        if (!$this->context->shouldRun()) {
            return $next($request);
        }

        // Launch startup event
        if ($response = $this->events->onApplicationBoot()) {
            return $response;
        }

        return $next($request);
    }

    /**
     * @inheritdoc
     */
    public function terminate(Request $request, Response $response)
    {
        if ($this->context->shouldRun()) {
            $this->events->onApplicationDone($response);
        }
    }
}

