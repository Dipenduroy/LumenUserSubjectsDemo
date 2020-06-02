<?php
namespace App\Services\Request;

use App\Services\Trace\ZipkinTrace;
// plugin start
use Zipkin\TracingBuilder;
use ZipkinGuzzle\Middleware;
// plugin end
use Zipkin\Propagation\Map;
use Zipkin\Timestamp;

/**
 *
 * @author dipenduroy
 *
 */
class Helper
{
    
    var $ZipkinTrace;
    
    /**
     */
    public function __construct(ZipkinTrace $ZipkinTrace)
    {
        $this->ZipkinTrace = $ZipkinTrace;
    }
    
    public function getServiceResponse($request_api, $method, $path = '', $query = [], $post = [], $returnResponseString = false)
    {
        $ZipkinTrace = $this->ZipkinTrace;
        $tracing = $ZipkinTrace->getTracing();
        
        /* Creates the span for getting the users list */
        // $childSpan = $ZipkinTrace->createChildSpan('users:get_list');
        
        // $headers = [];
        
        /* Injects the context into the wire */
        // $injector = $tracing->getPropagation()->getInjector(new Map());
        // $injector($childSpan->getContext(), $headers);
        
        // add tags dipendu
        // $uri='localhost:9001';
        // $childSpan->tag(\Zipkin\Tags\HTTP_URL, $uri);
        
        /* HTTP Request to the backend */
        // $childSpan->annotate('request_started', Timestamp\now());
        // $client = new \GuzzleHttp\Client();
        $client = new \GuzzleHttp\Client([
            'handler' => Middleware\handlerStack($tracing)
        ]);
        $request_api_key = $request_api . "_SERVICE";
        $request_url = env($request_api_key);
        if (empty($request_url)) {
            return response()->json([
                'message' => 'service not found'
            ], 404);
        }
        try {
            $response = $client->{$method}($request_url . $path . '?' . http_build_query($query), [
                'auth' => [
                    env('SERVICE_USERNAME'),
                    env('SERVICE_PASSWORD')
                ],
                'form_params' => $post
                // 'headers' => $headers
            ]);
            $responseString = $response->getBody();
            
            // $childSpan->tag(\Zipkin\Tags\HTTP_STATUS_CODE, $response->getStatusCode());
            // $childSpan->annotate('request_finished', Timestamp\now());
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $response = $e->getResponse();
            if ($e->hasResponse()) {
                $responseString = $e->getResponse()->getBody(true);
            } else {
                $exception_response = [
                    'message' => $e->getMessage()
                ];
                return $returnResponseString ? json_encode($exception_response) : response()->json($exception_response, 503);
            }
        }
        
        if ($returnResponseString) {
            return $responseString ?? '';
        }
        
        return response()->json(json_decode($responseString, 1), $response->getStatusCode());
    }
}

