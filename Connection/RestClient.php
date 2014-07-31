<?PHP
namespace Weboxphp\Connection;

require_once 'Weboxphp/Connection/Exceptions/GenericHTTPError.php';
require_once 'Weboxphp/Connection/Exceptions/InvalidCredentials.php';
require_once 'Weboxphp/Connection/Exceptions/NoDomainsConfigured.php';
require_once 'Weboxphp/Connection/Exceptions/MissingRequiredParameters.php';
require_once 'Weboxphp/Connection/Exceptions/MissingEndpoint.php';


use Guzzle\Http\Client as Guzzle;
use Weboxphp\WeboxClient;

use Weboxphp\Connection\Exceptions\GenericHTTPError;
use Weboxphp\Connection\Exceptions\InvalidCredentials;
use Weboxphp\Connection\Exceptions\NoDomainsConfigured;
use Weboxphp\Connection\Exceptions\MissingRequiredParameters;
use Weboxphp\Connection\Exceptions\MissingEndpoint;

class RestClient{

	private $apiKey;
	protected $wbxClient;

	public function __construct($apiKey, $apiEndpoint, $apiVersion, $ssl){
		$this->apiKey = $apiKey;
		$this->wbxClient = new Guzzle($this->generateEndpoint($apiEndpoint, $apiVersion, $ssl));
		$this->wbxClient->setDefaultOption('curl.options', array('CURLOPT_FORBID_REUSE' => true));
		$this->wbxClient->setDefaultOption('auth', array (API_USER, $this->apiKey));	
		$this->wbxClient->setDefaultOption('exceptions', false);
		$this->wbxClient->setUserAgent(SDK_USER_AGENT . '/' . SDK_VERSION);
	}

	public function post($endpointUrl, $postData = array(), $files = array()) {
		$request = $this->wbxClient->post($endpointUrl, array(), $postData);		
		$response = $request->send();
		return $this->responseHandler($response);
	}

	public function get($endpointUrl, $queryString = array()) {
		$request = $this->wbxClient->get($endpointUrl);
		if(isset($queryString)){
			foreach($queryString as $key=>$value) {
				$request->getQuery()->set($key, $value);
			}	
		}
		$request->setHeader('Authorization', 'Bearer ' . $this->apiKey);
		$response = $request->send();
		return $this->responseHandler($response);
	}

	public function delete($endpointUrl) {
		$request = $this->wbxClient->delete($endpointUrl);
		$request->setHeader('Authorization', 'Bearer ' . $this->apiKey);
		$response = $request->send();
		return $this->responseHandler($response);	
	}

	public function put($endpointUrl, $putData) {
		$request = $this->wbxClient->put($endpointUrl, array(), $putData);
		$request->getPostFields()->setAggregator(new DuplicateAggregator());
		$request->setHeader('Authorization', 'Bearer ' . $this->apiKey);
		$response = $request->send();
		return $this->responseHandler($response);
	}

	public function responseHandler($responseObj){
		$httpResponseCode = $responseObj->getStatusCode();
		if($httpResponseCode === 200){
			$jsonResponseData = json_decode($responseObj->getBody(), true);
			$result = new \stdClass();
			$result->http_response_body = $jsonResponseData;
		}
		elseif($httpResponseCode == 400){
			throw new MissingRequiredParameters(EXCEPTION_MISSING_REQUIRED_PARAMETERS);
		}
		elseif($httpResponseCode == 401){
			throw new InvalidCredentials(EXCEPTION_INVALID_CREDENTIALS);
		}
		elseif($httpResponseCode == 404){
			throw new MissingEndpoint(EXCEPTION_MISSING_ENDPOINT);
		}
		else{
			throw new GenericHTTPError(EXCEPTION_GENERIC_HTTP_ERROR);
		}
		$result->http_response_code = $httpResponseCode;
		return $result;
	}

	private function generateEndpoint($apiEndpoint, $apiVersion, $ssl){
		if(!$ssl){
			return "http://" . $apiEndpoint . "/" . $apiVersion . "/";
		}
		else{
			return "https://" . $apiEndpoint . "/" . $apiVersion . "/";
		}
	}
}