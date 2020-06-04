<?php
namespace App\Services\Request;

use App\Services\Trace\ZipkinTrace;

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
        $request_api_key = $request_api . "_SERVICE";
        $host_url = env($request_api_key);
        $url=$host_url . $path . ( !empty($query)?'?' . http_build_query($query):'');
        if (empty($host_url)) {
            return response()->json([
                'message' => 'service not found'
            ], 404);
        }
        
        /* Creates the span for getting the guzzle client call */
        $childSpan = $this->ZipkinTrace->createChildClientSpan($request_api_key);
        $childSpan->tag(\Zipkin\Tags\HTTP_HOST, $host_url);
        $childSpan->tag(\Zipkin\Tags\HTTP_URL, $url);
        $childSpan->tag(\Zipkin\Tags\HTTP_PATH, $path);
        $childSpan->tag(\Zipkin\Tags\HTTP_METHOD, strtoupper($method));
        $childSpan->annotate('request_started', \Zipkin\Timestamp\now());
        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->{$method}($url, [
                'auth' => [
                    env('SERVICE_USERNAME'),
                    env('SERVICE_PASSWORD')
                ],
                'form_params' => $post,
                'headers' => $this->ZipkinTrace->getHeaders()
            ]);
            $responseString = $response->getBody();
        } catch (\GuzzleHttp\Exception\TransferException $e) {
            $response = $e->getResponse();
            if ($e->hasResponse()) {
                $responseString = $e->getResponse()->getBody(true);
            } else {
                $childSpan->tag(\Zipkin\Tags\HTTP_STATUS_CODE, 503);
                $childSpan->tag(\Zipkin\Tags\ERROR, 503);
                $childSpan->annotate('request_failed', \Zipkin\Timestamp\now());
                $exception_response = [
                    'message' => $e->getMessage()
                ];
                return $returnResponseString ? json_encode($exception_response) : response()->json($exception_response, 503);
            }
        }
        $childSpan->tag(\Zipkin\Tags\HTTP_STATUS_CODE, $response->getStatusCode());
        $childSpan->annotate('request_finished', \Zipkin\Timestamp\now());
        if ($returnResponseString) {
            return $responseString ?? '';
        }
        return response()->json(json_decode($responseString, 1), $response->getStatusCode());
    }
}

