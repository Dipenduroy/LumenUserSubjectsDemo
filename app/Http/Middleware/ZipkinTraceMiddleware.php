<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Trace\ZipkinTrace;

class ZipkinTraceMiddleware
{
    var $ZipkinTrace;
    
    public function __construct(ZipkinTrace $ZipkinTrace)
    {
        $this->ZipkinTrace = $ZipkinTrace;
    }
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
    
    public function terminate($request, $response)
    {
        /* Sends the trace to zipkin once the response is served */
        $this->ZipkinTrace->flushTracer();
    }
}
