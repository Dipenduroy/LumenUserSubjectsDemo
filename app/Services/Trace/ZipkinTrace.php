<?php
namespace App\Services\Trace;

use Zipkin\Propagation\DefaultSamplingFlags;
use Zipkin\Propagation\Map;
use Zipkin\Timestamp;

use Zipkin\Endpoint;
use Zipkin\Samplers\BinarySampler;
use Zipkin\TracingBuilder;

/**
 *
 * @author dipenduroy
 *        
 */
class ZipkinTrace
{
    
    private $tracing,$defaultSamplingFlags,$tracer,$createRootSpan;
    private $root_span,$current_span,$extractedContext;
    private $createdRootSpan=false;
    private $span_array=[];

    /**
     */
    public function __construct($createRootSpan=false)
    {
        $this->tracing = self::create_tracing(env('APP_NAME'),$_SERVER['SERVER_ADDR']);
        $this->tracer= $this->tracing->getTracer();
//         $this->createRootSpan=$createRootSpan;
//         if($this->createRootSpan && !$this->createdRootSpan) {
//             /* Always sample traces */
//             $this->defaultSamplingFlags = DefaultSamplingFlags::createAsSampled();
//             $this->root_span = $this->current_span=$this->tracer->newTrace($this->defaultSamplingFlags);
//             /* Creates the main span */
//             $this->current_span->start(Timestamp\now());
//         } else {
//             $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
//             $carrier = array_map(function ($header) {
//                 return $header[0];
//             }, $request->headers->all());
//             $extractor = $this->tracing->getPropagation()->getExtractor(new Map());
//             $this->extractedContext = $extractor($carrier);
//             /* Creates the extracted main span */
//             $this->current_span=$this->tracer->nextSpan($this->extractedContext );
//             $this->current_span->start();
//         }
//         $this->current_span->setKind(\Zipkin\Kind\SERVER);
//         $this->current_span->setName('parse_request');
//         $this->span_array[]=$this->current_span;
        
    }
    
    public function getTracing() {
        return $this->tracing;
    }
    
    public function createChildSpan($name='client_call') {
        $this->current_span=$this->tracer->newChild($this->current_span->getContext());
        $this->current_span->start();
        $this->current_span->setKind(\Zipkin\Kind\CLIENT);
        $this->current_span->setName($name);
        $this->span_array[]=$this->current_span;
        return $this->current_span;
    }
    
    public function flushTracer() {
        while(!empty($this->span_array)){
            $span = array_pop($this->span_array);
            $span->finish();
        }
        $this->getTracing()->getTracer()->flush();
    } 
    
    /**
     * create_tracing function is a handy function that allows you to create a tracing
     * component by just passing the local service information. If you need to pass a
     * custom zipkin server URL use the HTTP_REPORTER_URL env var.
     */
    public static function create_tracing($localServiceName, $localServiceIPv4, $localServicePort = null)
    {
        $httpReporterURL = getenv('HTTP_REPORTER_URL');
        if ($httpReporterURL === false) {
            $httpReporterURL = 'http://localhost:9411/api/v2/spans';
        }
        
        $endpoint = Endpoint::create($localServiceName, $localServiceIPv4, null, $localServicePort);
        
        $reporter = new \Zipkin\Reporters\Http(
            \Zipkin\Reporters\Http\CurlFactory::create(),
            ['endpoint_url' => $httpReporterURL]
            );
        $sampler = BinarySampler::createAsAlwaysSample();
        return TracingBuilder::create()
        ->havingLocalEndpoint($endpoint)
        ->havingSampler($sampler)
        ->havingReporter($reporter)
        ->build();
    }

    /**
     */
    function __destruct()
    {}
}

