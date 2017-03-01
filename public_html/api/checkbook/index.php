<?php

require_once dirname(__DIR__, 3) . "/php/classes/autoload.php";
require_once dirname(__DIR__, 3) . "/php/lib/xsrf.php";
require_once("/etc/apache2/capstone-mysql/encrypted-config.php");

use Edu\Cnm\AbqVast\Checkbook;

/**
 * api for checkbook class
 *
 * @author Taylor McCarthy <oresshi@gmail.com>
 **/

// check the session status, if it is not active, start the session.
if(session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

//prepare an empty reply
$reply = new stdClass();
$reply->status = 200;
$reply->data = null;
//create stdClass named $reply. this object will be used to store the results of the call to the api. sets status to 200 (success). creates data state variable, holds the result of the api call.

try {
	//grab the mySQL database connection
	$pdo = connectToEncryptedMySQL("/etc/apache2/capstone-mysql/abqvast.ini");

	//determines which HTTP Method needs to be processed and stores the result in $method
	$method = array_key_exists("HTTP_x_HTTP_METHOD", $_SERVER) ? $_SERVER["HTTP_X_HTTP_METHOD"] : $_SERVER["REQUEST_METHOD"];

	//stores the Primary key for the GET methods in $id, This key will come in the URL sent by the front end. If no key is present $id will remain empty. Note that the input filtered.
	$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	$newCheckbookInvoiceAmount = filter_input(INPUT_GET, "checkbookInvoiceAmount", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$newCheckbookInvoiceLowAmount = filter_input(INPUT_GET, "checkbookInvoiceLowAmount", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$newCheckbookInvoiceHighAmount = filter_input(INPUT_GET, "checkbookInvoiceHighAmount", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
	$newCheckbookInvoiceDate = filter_input(INPUT_GET, "checkbookInvoiceDate");
	$newCheckbookInvoiceSunriseDate = filter_input(INPUT_GET, "checkbookInvoiceSunriseDate");
	$newCheckbookInvoiceSunsetDate = filter_input(INPUT_GET, "checkbookInvoiceSunsetDate");
	$newCheckbookInvoiceNum = filter_input(INPUT_GET, "checkbookInvoiceNum", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$newCheckbookPaymentDate = filter_input(INPUT_GET, "checkbookPaymentDate");
	$newCheckbookPaymentSunriseDate = filter_input(INPUT_GET, "checkbookPaymentSunriseDate");
	$newCheckbookPaymentSunsetDate = filter_input(INPUT_GET, "checkbookPaymentSunsetDate");
	$newCheckbookReferenceNum = filter_input(INPUT_GET, "checkbookReferenceNum", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$newCheckbookVendor = filter_input(INPUT_GET, "checkbookVendor", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	/** Shouldn't be needed due to checkbook only requiring get and get all.
	 * //here we check and make sure that we have the Primary key for the DELETE and PUT requests. If the request is a PUT or DELETE and no key is present in $id an exception is thrown
	 * if(($method === "DELETE" || $method === "PUT" || $method === "POST") && (empty($id) === true || $id < 0)) {
	 * throw(new InvalidArgumentException("id cannot be empty or negative", 405));
	 * }
	 **/

//here we determine if the request received is a GET request
	if($method === "GET") {
		//set XSRF cookie
		setXsrfCookie("/");
		//handle GET request - if id is present, that checkbook value is present, that checkbook value is returned, otherwise all values are returned.

		//determine is a Key was sent in the URL by checking $id. if so we pull the requested checkbook value by checkbook ID from the database and store it in $checkbook
		if(empty($id) === false) {
			$reply->data = Checkbook::getCheckbookByCheckbookId($pdo, $id);
		} elseif(empty($newCheckbookInvoiceHighAmount) === false && empty($newCheckbookInvoiceLowAmount) === false) {
			$reply->data = Checkbook::getCheckbookByCheckbookInvoiceAmount($pdo, $newCheckbookInvoiceLowAmount, $newCheckbookInvoiceHighAmount)->toArray();
		} elseif(empty($newCheckbookInvoiceSunriseDate) === false && empty($newCheckbookInvoiceSunsetDate) === false) {
			$newCheckbookInvoiceSunriseDate = \DateTime::createFromFormat("U", floor($newCheckbookInvoiceSunriseDate / 1000));
			$newCheckbookInvoiceSunsetDate = \DateTime::createFromFormat("U", ceil($newCheckbookInvoiceSunsetDate / 1000));
			$reply->data = Checkbook::getCheckbookByCheckbookInvoiceDate($pdo, $newCheckbookInvoiceSunriseDate, $newCheckbookInvoiceSunsetDate)->toArray();
		} elseif(empty($newCheckbookInvoiceNum) === false) {
			$reply->data = Checkbook::getCheckbookByCheckbookInvoiceNum($pdo, $newCheckbookInvoiceNum)->toArray();
		} elseif(empty($newCheckbookPaymentSunriseDate) === false && empty($newCheckbookPaymentSunsetDate) === false) {
			$newCheckbookPaymentSunriseDate = \DateTime::createFromFormat("U", floor($newCheckbookPaymentSunriseDate / 1000));
			$newCheckbookPaymentSunsetDate = \DateTime::createFromFormat("U", ceil($newCheckbookPaymentSunsetDate / 1000));
			$reply->data = Checkbook::getCheckbookByCheckbookPaymentDate($pdo, $newCheckbookPaymentSunriseDate, $newCheckbookPaymentSunsetDate)->toArray();
		} elseif(empty($newCheckbookReferenceNum) === false) {
			$reply->data = Checkbook::getCheckbookByCheckbookReferenceNum($pdo, $newCheckbookReferenceNum)->toArray();
		} elseif(empty($newCheckbookVendor) === false) {
			$reply->data = Checkbook::getCheckbookByCheckbookVendor($pdo, $newCheckbookVendor)->toArray();
		} else {
			$checkbook = Checkbook::getAllCheckbooks($pdo);
			if($checkbook !== null) {
				$reply->data = $checkbook;
			}
		}
	}

} catch
(Exception $exception) {
	$reply->status = $exception->getCode();
	$reply->message = $exception->getMessage();
} catch(TypeError $typeError) {
	$reply->status = $typeError->getCode();
	$reply->message = $typeError->getMessage();
}
// in these lines the Exceptions are caught and the $reply object is updated with the data from the caught exception. Note that $reply->status will be updated with the correct error code in the case of an Exception

header("Content-type: application/json");
//sets up the response header.
if($reply->data === null) {
	unset($reply->data);
}


echo json_encode($reply);
//finally - JSON encodes the $reply object and sends it back to the front end.