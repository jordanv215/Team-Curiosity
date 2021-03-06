<?php

require_once(dirname(__DIR__, 2) . "/classes/Autoload.php");
require_once(dirname(__DIR__, 2) . "/lib/xsrf.php");
require_once("/etc/apache2/redrovr-conf/encrypted-config.php");

use Redrovr\TeamCuriosity;

/**
 * api for the CommentNewsArticle class
 *
 * @author Ellen Liu <eliu1@cnm.edu>
 **/

// verify the session, start if not active
if(session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
}

// prepare an empty reply
$reply = new stdClass();
$reply->status = 200;
$reply->data = null;

try {
	// grab the mySQL connection
	$pdo = connectToEncryptedMySQL("/etc/apache2/redrovr-conf/mars.ini");

	// determine which HTTP method was used
	$method = array_key_exists("HTTP_X_HTTP_METHOD", $_SERVER) ? $_SERVER["HTTP_X_HTTP_METHOD"] : $_SERVER["REQUEST_METHOD"];

	// sanitize input
	$commentNewsArticleId = filter_input(INPUT_GET, "commentNewsArticleId", FILTER_VALIDATE_INT);
	$commentNewsArticleContent = filter_input(INPUT_GET, "commentNewsArticleContent", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$commentNewsArticleNewsArticleId = filter_input(INPUT_GET, "commentNewsArticleNewsArticleId", FILTER_VALIDATE_INT);
	$commentNewsArticleUserId = filter_input(INPUT_GET, "commentNewsArticleUserId", FILTER_VALIDATE_INT);

	// make sure the id is valid for methods that require it
	if(($method === "DELETE" || $method === "PUT") && (empty($commentNewsArticleId) === true || $commentNewsArticleId < 0)) {
		throw(new InvalidArgumentException("commentNewsArticleId cannot be empty or negative", 405));
	}

	// handle GET request
	if($method === "GET") {
		// set XSRF cookie
		setXsrfCookie();

		// get a specific commentNewsArticle, all comments on an article, all comments by a user, or all commentNewsArticles, and update reply
		if(empty($commentNewsArticleId) === false) {
			$commentNewsArticle = TeamCuriosity\CommentNewsArticle::getCommentNewsArticleByCommentNewsArticleId($pdo, $commentNewsArticleId);
			if($commentNewsArticle !== null) {
				$reply->data = $commentNewsArticle;
			}

		} else if(empty($commentNewsArticleContent) === false) {
			$commentNewsArticles = TeamCuriosity\CommentNewsArticle::getCommentNewsArticleByCommentNewsArticleContent($pdo, $commentNewsArticleContent);
			if($commentNewsArticles !== null) {
				$reply->data = $commentNewsArticles;
			}

		} else if(empty($commentNewsArticleNewsArticleId) === false) {
			$commentNewsArticles = TeamCuriosity\CommentNewsArticle::getCommentNewsArticleByCommentNewsArticleNewsArticleId($pdo, $commentNewsArticleNewsArticleId);
			if($commentNewsArticles !== null) {
				$reply->data = $commentNewsArticles;
			}
		} else if(empty($commentNewsArticleUserId) === false) {
			$commentNewsArticles = TeamCuriosity\CommentNewsArticle::getCommentNewsArticleByCommentNewsArticleUserId($pdo, $commentNewsArticleUserId);
			if($commentNewsArticles !== null) {
				$reply->data = $commentNewsArticles;
			}
		} else {
				$commentNewsArticles = TeamCuriosity\CommentNewsArticle::getAllCommentNewsArticles($pdo);
				if($commentNewsArticles !== null) {
					$reply->data = $commentNewsArticles;
				}
			}
		} else if($method === "PUT" || $method === "POST") {

		verifyXsrf();
		$requestContent = file_get_contents("php://input");
		$requestObject = json_decode($requestContent);

		// make sure commentNewsArticle is available
		if(empty($requestObject->commentNewsArticleId) === true) {
			throw(new \InvalidArgumentException("No CommentNewsArticle to update", 405));
		}

		// perform the actual put or post
		if($method === "PUT") {

			// retrieve the commentNewsArticle to update
			$commentNewsArticle = TeamCuriosity\CommentNewsArticle::getCommentNewsArticleByCommentNewsArticleId($pdo, $commentNewsArticleId);
			if($commentNewsArticle === null) {
				throw(new RuntimeException("CommentNewsArticle does not exist", 404));
			}

			// edit the fields in the commentNewsArticle and update the table
			$commentNewsArticle->setCommentNewsArticleContent($requestObject->commentNewsArticleContent);
			$commentNewsArticle->setCommentNewsArticleDateTime($requestObject->commentNewsArticleDateTime);
			$commentNewsArticle->setCommentNewsArticleNewsArticleId($requestObject->commentNewsArticleNewsArticleId);
			$commentNewsArticle->setCommentNewsArticleUserId($requestObject->commentNewsArticleUserId);
			$commentNewsArticle->update($pdo);
			// update reply
			$reply->message = "CommentNewsArticle has been updated";
			
		} else if($method === "POST") {

			// create new commentNewsArticle and insert into the database
			$commentNewsArticle = new TeamCuriosity\CommentNewsArticle(null, $requestObject->commentNewsArticleContent, null, $requestObject->commentNewsArticleNewsArticleId, $requestObject->commentNewsArticleUserId);
			$commentNewsArticle->insert($pdo);

			// update reply
			$reply->message = "CommentNewsArticle has been created";
		}
		} else if($method === "DELETE") {
					verifyXsrf();

					// retrieve the CommentNewsArticle to be deleted
					$commentNewsArticle = TeamCuriosity\CommentNewsArticle::getCommentNewsArticleByCommentNewsArticleId($pdo, $commentNewsArticleId);
					if($commentNewsArticle === null) {
						throw(new RuntimeException("CommentNewsArticle does not exist", 404));
					}

					// delete commentNewsArticle
					$commentNewsArticle->delete($pdo);

					// update reply
					$reply->message = "CommentNewsArticle has been deleted";
		} else {
					throw(new InvalidArgumentException("Invalid HTTP method request"));
		}

		// update reply with exception information
} catch(Exception $exception) {
		$reply->status = $exception->getCode();
		$reply->message = $exception->getMessage();
		$reply->trace = $exception->getTraceAsString();
} catch(TypeError $typeError) {
		$reply->status = $typeError->getCode();
		$reply->message = $typeError->getMessage();
}

header("Content-type: applicaton/json");
if($reply->data === null) {
		unset($reply->data);
}

// encode and return reply to front end caller
echo json_encode($reply);