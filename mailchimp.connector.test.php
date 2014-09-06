<?php

//Require the MailChimp Connector Class
require_once('mailchimp.connector.class.php');

//Create the Connector Object
$base_url = "https://us9.api.mailchimp.com/2.0/";
$apikey = "<enter api key here>";
$mcConnector = new mailChimpConnector($base_url,$apikey);

//Get the New Subscribers List from MailChimp
$newSubscribersList = $mcConnector->getListByName("New Subscribers");

if(isset($newSubscribersList->id)){

	//Add email address to selected MailChimp List
	$subscribeResults = $mcConnector->subscribeEmailToList($newSubscribersList->id,"mark.ev.sugar@gmail.com");

	//Process Results
	if($subscribeResults->email){
		echo"Email address {$subscribeResults->email} has been successfully subscribed";
	}elseif($subscribeResults->status=="error"){
		echo"There was an error subscribing the email address: <br><br>{$subscribeResults->error}";
	}else{
		echo"There was an unknown error subscribing the email address. Results: <br><br><pre>".print_r($subscribeResults,true)."</pre>";
	}

}else{
	echo"Error. MailChimp List not Found.";
}

?>
