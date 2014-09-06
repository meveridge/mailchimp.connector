<?php

/**
 * mailChimpConnector class
 *
 * Connect via CURL to MailChimp's REST API
 *
 * Created By Mark Everidge
 * September 5th 2014
 * For www.thenosemilk.com
 */

class mailChimpConnector {

	public $base_url = "";
	public $apikey = "";

/**
 * __construct
 * Builds the connector object with base parameters
 *
 * @param string $base_url -- MailChimp API DataCenter (E.g. https://us9.api.mailchimp.com/2.0/)
 * @param string $apikey -- MailChimp API Key for Account
 * 
 */
	public function __construct($base_url,$apikey){
		$this->base_url = $base_url;
		$this->apikey = $apikey;

	}

/**
 * execute
 * Calls REST API using CURL
 *
 * @param string $method -- MailChimp API Endpoint (E.g. list/lists)
 * @param string $type -- HTTP Verb (E.g. GET, POST)
 * @param array $arguments -- Method parameters to pass to CURL
 * @param boolean $encodeData
 * @param boolean $returnHeaders
 *
 * @return json $response
 * 
 */
	private function execute(
		$method,
		$type,
		$arguments,
		$encodeData=true,
   		$returnHeaders=false
	){

	    $type = strtoupper($type);
	    $url = $this->base_url . $method;

	    if ($type == 'GET')
	    {
	        $url .= "?" . http_build_query($arguments);
	    }

	    $curl_request = curl_init($url);

	    if ($type == 'POST')
	    {
	        curl_setopt($curl_request, CURLOPT_POST, 1);
	    }
	    elseif ($type == 'PUT')
	    {
	        curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "PUT");
	    }
	    elseif ($type == 'DELETE')
	    {
	        curl_setopt($curl_request, CURLOPT_CUSTOMREQUEST, "DELETE");
	    }

	    curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
	    curl_setopt($curl_request, CURLOPT_HEADER, $returnHeaders);
	    curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
	    curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

	    if (!empty($oauthtoken))
	    {
	        $token = array("oauth-token: {$oauthtoken}");
	        curl_setopt($curl_request, CURLOPT_HTTPHEADER, $token);
	    }

	    if (!empty($arguments) && $type !== 'GET')
	    {
	        if ($encodeData)
	        {
	            //encode the arguments as JSON
	            $arguments = json_encode($arguments);
	        }
	        curl_setopt($curl_request, CURLOPT_POSTFIELDS, $arguments);
	    }

	    $result = curl_exec($curl_request);

	    if ($returnHeaders)
	    {
	        //set headers from response
	        list($headers, $content) = explode("\r\n\r\n", $result ,2);
	        foreach (explode("\r\n",$headers) as $header)
	        {
	            header($header);
	        }

	        //return the nonheader data
	        return trim($content);
	    }

	    curl_close($curl_request);

	    //decode the response from JSON
	    $response = json_decode($result);

	    return $response;

	}

/**
 * getListByName
 * Executes /list/list endpoint returning a single MailChimp list
 *
 * @param string $listName -- MailChimp List Name to filter on
 *
 * @return array $listResult
 * 
 */
	public function getListByName($listName){
		$method = "lists/list";

		$list_arguments = array(
    		"apikey" => $this->apikey,
    		"filters" => array(
    			"list_name" => $listName,
    			"exact" => true
    		),
		);

		$listResults = $this->execute($method, 'POST', $list_arguments);
		if($listResults->total=="1"){
			return $listResults->data[0];
		}else{
			return array("error"=>"More than one list was returned...");
		}
	}

/**
 * subscribeEmailToList
 * Adds email address to specified MailChimp List
 *
 * @param string $listId -- MailChimp List ID Returned from getListByName($listName)->id;
 * @param string $email_address -- Email Address to add to the list
 * @param boolean $sendWelcome
 *
 * @return array $subscribeResults 
 *
 */
	public function subscribeEmailToList($listId,$email_address,$sendWelcome = true){
		$method = "lists/subscribe";

		$subscribe_arguments = array(
		    "apikey" => $this->apikey,
		    "id" => $listId,
		    "email" => array(
		        "email" => $email_address,
		    ),
		    "send_welcome" => $sendWelcome
		);

		$subscribeResults = $this->execute($method, 'POST', $subscribe_arguments);

		return $subscribeResults;	
	}
}