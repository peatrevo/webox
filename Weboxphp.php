<?php
namespace Weboxphp;

require_once 'Constants/Constants.php';
require_once 'Connection/RestClient.php';

use Weboxphp\Parcels\Exceptions;
use Weboxphp\Connection\RestClient;

class Weboxphp {

	public function __construct($apiKey = null, $apiEndpoint = "api-hu.easypack24.net", $apiVersion = "v4", $ssl = true) {
		$this->restClient = new RestClient($apiKey, $apiEndpoint, $apiVersion, $ssl);
    }
	public function post($endpointUrl, $postData = array(), $files = array()){
		return $this->restClient->post($endpointUrl, $postData, $files);
	}

	public function get($endpointUrl, $queryString = array()){
		return $this->restClient->get($endpointUrl, $queryString);
	}

	public function delete($endpointUrl){
		return $this->restClient->delete($endpointUrl);
	}

	public function put($endpointUrl, $putData){
		return $this->restClient->put($endpointUrl, $putData);
	}
}
?>