<?php

require_once(dirname(__DIR__, 2) . "/classes/Autoload.php");
require_once(dirname(__DIR__, 2) . "/lib/xsrf.php");
require_once("/etc/apache2/redrovr-conf/encrypted-config.php");

use Redrovr\TeamCuriosity\Image;


/**
 * REST api for the Image class
 *
 * @author Jordan Vinson <jvinson3@cnm.edu>
 * @author Kai Garrott <kai@kaigarrott.com>
 **/

//verify the session, start if not active
if(session_status() !== PHP_SESSION_ACTIVE) {
	session_start();
}

//prepare an empty reply
$reply = new stdClass();
$reply->status = 200;
$reply->data = null;

try {
	//grab my mySQL Connection
	$pdo = connectToEncryptedMySQL("/etc/apache2/redrovr-conf/mars.ini");

	//determine which HTTP method was used
	$method = array_key_exists("HTTP_X_HTTP_METHOD", $_SERVER) ? $_SERVER["HTTP_X_HTTP_METHOD"] : $_SERVER["REQUEST_METHOD"];

	//sanitize input
	$imageId = filter_input(INPUT_GET, "imageId", FILTER_VALIDATE_INT);
	$imageCamera = filter_input(INPUT_GET, "imageCamera", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$imageDescription = filter_input(INPUT_GET, "imageDescription", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$imageEarthDate = filter_input(INPUT_GET, "imageEarthDate", FILTER_VALIDATE_INT);
	$imageSol = filter_input(INPUT_GET, "imageSol", FILTER_VALIDATE_INT);
	$imageTitle = filter_input(INPUT_GET, "imageTitle", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
	$imageUrl = filter_input(INPUT_GET, "imageUrl", FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);


	//make sure the id is valid for methods that require it
	if(($method === "DELETE" || $method === "PUT") && (empty($imageId) === true || $imageId <= 0)) {
		throw(new InvalidArgumentException("Image id cannot be empty or negative", 405));
	}


	//handle GET request - if id is present, that image is returned, otherwise all images are returned
	if($method === "GET") {
		//set XSRF cookie
		setXsrfCookie();

		// if the parameter exists, proceed
		if(isset($_GET["top25"]) === true) {

			// check when NASA api was last called
			$lf = "/var/www/html/conf/last-ran.txt";

			function getLastRan($lf) {
				global $time;
				$fh = fopen($lf, "r+");
				$time = fgets($fh);
				fclose($fh);
				return $time;
			}

			getLastRan($lf);
			$now = time();
			global $time;
			// proceed only if API not called within last hour (to avoid unnecessary calls & optimize retrieval speed)
			if(($now - $time) > 10) {


				// mark the time that the API call is being run
				function setTimeRan($lf) {
					$timeRan = time();
					$fh = fopen($lf, "w+");
					fwrite($fh, $timeRan);
					fclose($fh);
				}

				setTimeRan($lf);

				// grab json with last 25 items (NASA default/maximum per page)
				function NasaCall() {
					global $pdo;
					$baseUrl = "https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos?";
					$config = readConfig("/etc/apache2/redrovr-conf/mars.ini");
					$json = json_decode($config['authkeys']);
					$apiKey = $json->nasa->clientSecret;
					$pdo = connectToEncryptedMySQL("/etc/apache2/redrovr-conf/mars.ini");

					// to get most recent items, we need the highest sol value available
					// initial API call is only for this purpose
					// please, let there be a better way to do this...
					$pars = array(
						'sol' => '0',
						'api_key' => $apiKey
					);
					$suffix = http_build_query($pars, '', '&');
					$qUrl = $baseUrl . $suffix;
					$query = file_get_contents($qUrl);
					$queryResult = json_decode($query, true);
					$maxSol = $queryResult["photos"][0]["rover"]["max_sol"];

					// now we make the actual call to retrieve the most recent images
					$pars = array(
						'sol' => $maxSol,
						'api_key' => $apiKey
					);
					$suffix = http_build_query($pars, '', '&');
					$call = file_get_contents($baseUrl . $suffix);
					$callResult = json_decode($call, true);

					// iterate through json to inspect items
					foreach($callResult["photos"] as $item) {
						$imageUrl = $item["img_src"];

						// check if image already exists locally
						$duplicate = Image::getImageByImageUrl($pdo, $imageUrl);
						if($duplicate === null) {

							// grab data fields
							$imageSol = $item["sol"];
							$imageCamera = $item["camera"]["name"];
							$res = strpos("MAHLI FHAZ RHAZ NAVCAM MAST", $imageCamera);
							if(is_numeric($res)) {
								/*global $imageEarthDate;
								global $imageCamera;*/
								global $imageDescription;
								$imageEarthDate = $item["earth_date"];
								$imageEarthDate = \DateTime::createFromFormat("Y-m-d", (string)trim($imageEarthDate));
								// extract filepath to facilitate grabbing the extension
								$chunk = explode('.gov', $imageUrl);
								$chunkSuffix = $chunk[1];
								// split at extension
								$ext = explode('.', $chunkSuffix);
								$ext = strtolower($ext[1]);

								if($ext === "jpg" || $ext === "jpeg") {
									$imageType = "image/jpeg";

								} else {
									continue;
								}
								$imageTitle = md5($chunkSuffix);

								if($imageTitle !== null) {

									// resample image @ width: 800px & quality: 90%
									$w = 800;
									header('Content-type: image/jpeg');
									list($width, $height) = getimagesize($imageUrl);
									$prop = $w / $width;
									$newWidth = $width * $prop;
									$newHeight = $height * $prop;
									ob_start();
									$image_p = imagecreatetruecolor($newWidth, $newHeight);
									$image = imagecreatefromjpeg($imageUrl);
									imagecopyresampled($image_p, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

									imagejpeg($image_p, null, 90);

									// place image into nested directories based on first 3 characters of hashed name
									// this is to optimize file system access time
									$file = ob_get_contents();
									ob_end_clean();
									$baseDir = "/var/www/html/media/";
									$dir0 = (substr($imageTitle, 0, 1) . "/");
									$dir1 = (substr($imageTitle, 1, 1) . "/");
									$dir2 = (substr($imageTitle, 2, 1) . "/");
									$dirPath = $baseDir . $dir0 . $dir1 . $dir2;
									if(!file_exists($dirPath)) {
										mkdir(($dirPath), 0777, true);
									}
									// save image to filesystem
									$filePath = $dirPath . $imageTitle . "." . $ext;
									$f = fopen($filePath, 'w');
									fwrite($f, $file);
									fclose($f);
									imagedestroy($image_p);
									imagedestroy($image);
									$imagePath = ("../../../../media/" . $dir0 . $dir1 . $dir2 . $imageTitle . "." . $ext);
									// add to database
									$imageRow = new Image(null, $imageCamera, $imageDescription, $imageEarthDate, $imagePath, $imageSol, $imageTitle, $imageType, $imageUrl);
									$imageRow->insert($pdo);

								} else {
									continue;
								}

							} else {
								continue;
							}
						} else {
							continue;
						}

					}
					global $reply;
					$images = Image::getImages($pdo);
					if($images !== null) {
						$reply->data = $images;
					}
				}

				NasaCall();
			}
		} //get a specific image and update reply
		else if(empty($imageId) === false) {
			$image = Image::getImageByImageId($pdo, $imageId);
			if($image !== null) {
				$reply->data = $image;
			}

		} else if(empty($imageCamera) === false) {
			$images = Image::getImageByImageCamera($pdo, $imageCamera);
			if($images !== null) {
				$reply->data = $images;
			}
		} else if(empty($imageDescription) === false) {
			$images = Image::getImageByImageDescription($pdo, $imageDescription);
			if($images !== null) {
				$reply->data = $images;
			}
		} else if(empty($imageEarthDate) === false) {
			$actualDate = DateTime::createFromFormat("U", $imageEarthDate / 1000);
			$images = Image::getImagesByImageEarthDate($pdo, $actualDate);
			if($images !== null) {
				$reply->data = $images;
			}

		} else if(empty($imageSol) === false) {
			$images = Image::getImagesByImageSol($pdo, $imageSol);
			if($images !== null) {
				$reply->data = $images;
			}

		} else if(empty($imageTitle) === false) {
			$images = Image::getImageByImageTitle($pdo, $imageTitle);
			if($images !== null) {
				$reply->data = $images;
			}

		} else if(empty($imageUrl) === false) {
			$images = Image::getImageByImageUrl($pdo, $imageUrl);
			if($images !== null) {
				$reply->data = $images;
			}

		} else {
			$images = Image::getAllImages($pdo);
			if($images !== null) {
				$reply->data = $images;
			}
		}
	} else if($method === "PUT" || $method === "POST") {
		verifyXsrf();
		$requestContent = file_get_contents("php://input");
		$requestObject = json_decode($requestContent);

		//make sure image url is available
		if(empty($requestObject->imageUrl) === true) {
			throw(new \InvalidArgumentException ("No url for this Image.", 405));
		}

		//make sure image title is available
		if(empty($requestObject->imageTitle) === true) {
			throw(new \InvalidArgumentException ("No title for this Image.", 405));
		}

		//make sure image camera is available
		if(empty($requestObject->imageCamera) === true) {
			throw(new \InvalidArgumentException ("No camera for this Image.", 405));
		}

		//make sure image type is available
		if(empty($requestObject->imageType) === true) {
			throw(new \InvalidArgumentException ("No MIME type for this Image.", 405));
		}

		//make sure image earth date is available
		if(empty($requestObject->imageEarthDate) === true) {
			throw(new \InvalidArgumentException ("No Earth date for this Image.", 405));
		}

		//make sure image local path is available
		if(empty($requestObject->imagePath) === true) {
			throw(new \InvalidArgumentException ("No file path for this Image.", 405));
		}


		//perform the actual put or post
		if($method === "PUT") {
			// retrieve the Image to update
			$image = Redrovr\TeamCuriosity\Image::getImageByImageId($pdo, $imageId);
			if($image === null) {
				throw(new RuntimeException("Image does not exist", 404));
			}

			// put the new image functions
			$image->setImageTitle($requestObject->imageTitle);
			$image->setImageDescription($requestObject->imageDescription);
			$image->setImageCamera($requestObject->imageCamera);
			$image->setImageEarthDate($requestObject->imageEarthDate);
			$image->setImageSol($requestObject->imageSol);
			$image->setImagePath($requestObject->imagePath);
			$image->setImageType($requestObject->imageType);
			$image->setImageUrl($requestObject->imageUrl);
			$image->update($pdo);

			//update reply
			$reply->message = "Image Updated Ok";

		} else if($method === "POST") {
			//create new Image and insert into the database
			$image = new Image(null, $requestObject->imageCamera, $requestObject->imageDescription, null, $requestObject->imagePath, $requestObject->imageSol, $requestObject->imageTitle, $requestObject->imageType, $requestObject->imageUrl);
			$image->insert($pdo);

			//update reply
			$reply->message = "Image created OK";

		} else if($method === "DELETE") {
			verifyXsrf();

			// retrieve the image to be deleted
			$image = Image::getImageByImageId($pdo, $imageId);
			if($image === null) {
				throw(new RuntimeException("Image does not exist", 404));
			}

			//delete the Image
			$image->delete($pdo);

			//update reply
			$reply->message = "Image deleted OK";
		} else {
			throw (new InvalidArgumentException("Invalid HTTP method request"));
		}
		// update reply with exception information
	}
} catch(Exception $exception) {
	$reply->status = $exception->getCode();
	$reply->message = $exception->getMessage();
	$reply->trace = $exception->getTraceAsString();
} catch(TypeError $typeError) {
	$reply->status = $typeError->getCode();
	$reply->message = $typeError->getMessage();
}


header("Content-Type: application/json");
if($reply->data === null) {
	unset($reply->data);
}

//encode and return reply to front end caller
echo json_encode($reply);