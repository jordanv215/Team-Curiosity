<?php
namespace Redrovr\TeamCuriosity;

require_once("Autoload.php");

/**
 * Image Class
 *
 * This is the PHP class for the Image table
 *
 * @author Ellen Liu
 **/
class Image implements \JsonSerializable {
	use ValidateDate;

	/**
	 * primary key for image
	 * @var int $imageId
	 **/
	private $imageId;
	/**
	 * camera which took the image
	 * @var string $imageCamera
	 **/
	private $imageCamera;
	/**
	 * description of image; only included if it is a featured image
	 * @var string|null $imageDescription
	 **/
	private $imageDescription;
	/**
	 * Earth date on which the image was captured
	 * @var \DateTime $imageEarthDate
	 **/
	private $imageEarthDate;
	/**
	 * Local file path of the image file
	 * @var string $imagePath
	 **/
	private $imagePath;
	/**
	 * Martian date on which the image was captured; represents number of days since the rover landed on Mars
	 * not provided for featured images as of this version
	 * @var int|null $imageSol
	 **/
	private $imageSol;
	/**
	 * Title of image; may be the same as the file name
	 * @var string $imageTitle
	 **/
	private $imageTitle;
	/**
	 * MIME type of image file
	 * @var string $imageType
	 **/
	private $imageType;
	/**
	 * URL from which the image was taken
	 * @var string $imageUrl
	 **/
	private $imageUrl;

	/**
	 * constructor for this image
	 *
	 * @param int|null $newImageId id of this image or null if a new image
	 * @param string $newImageCamera specific camera that took this image
	 * @param string|null $newImageDescription string containing description of image
	 * @param \DateTime $newImageEarthDate date and time image was taken
	 * @param string $newImagePath local uniform resource identifier(uri) of image
	 * @param int|null $newImageSol martian solar day that image was taken (number of days since rover landed)
	 * @param string $newImageTitle title of image
	 * @param string $newImageType MIME file format of image
	 * @param string $newImageUrl uniform resource locator(url) of image source
	 * @throws \InvalidArgumentException if data types are not valid
	 * @throws \RangeException if data values are out of bounds (e.g. strings too long, negative intergers)
	 * @throws \TypeError if data types violate type hints
	 * @throws  \Exception if some other exception occurs
	 **/
	public function __construct(int $newImageId = null, string $newImageCamera, string $newImageDescription = null, \DateTime $newImageEarthDate, string $newImagePath, int $newImageSol, string $newImageTitle, string $newImageType, string $newImageUrl) {
		try {
			$this->setImageId($newImageId);
			$this->setImageCamera($newImageCamera);
			$this->setImageDescription($newImageDescription);
			$this->setImageEarthDate($newImageEarthDate);
			$this->setImagePath($newImagePath);
			$this->setImageSol($newImageSol);
			$this->setImageTitle($newImageTitle);
			$this->setImageType($newImageType);
			$this->setImageUrl($newImageUrl);
		} catch(\InvalidArgumentException $invalidArgument) {
			// rethrow the exception to the caller
			throw(new \InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(\RangeException $range) {
			//rethrow the exception to the caller
			throw(new \RangeException($range->getMessage(), 0, $range));
		} catch(\TypeError $typeError) {
			// rethrow the exception to the caller
			throw(new \TypeError($typeError->getMessage(), 0, $typeError));
		} catch(\Exception $exception) {
			// rethrow the exception to the caller
			throw(new \Exception($exception->getMessage(), 0, $exception));
		}
	}

	/**
	 * accessor method for image id
	 *
	 * @return int|null value of image id
	 **/
	public function getImageId() {
		return ($this->imageId);
	}

	/**
	 * mutator method for image id
	 *
	 * @param int|null $newImageId new value of image id
	 * @throws \RangeException if $newImageId is not positive
	 * @throws \TypeError if $newImageId is not an integer
	 **/
	public function setImageId(int $newImageId = null) {
		// base case: if the image id is null, this is a new image without a mySQL assigned id(yet)
		if($newImageId === null) {
			$this->imageId = null;
			return;
		}

		//verify the image id is positive
		if($newImageId <= 0) {
			throw(new \RangeException("image id is not positive"));
		}

		//convert and  store the image id
		$this->imageId = $newImageId;
	}

	/** accessor method for image camera
	 *
	 * @return string $imageCamera value of image camera
	 **/
	public function getImageCamera() {
		return ($this->imageCamera);
	}

	/** matator method for image camera
	 * @param string $newImageCamera new value of image camera
	 * @throws \InvalidArgumentException if $newImageCamera is not a string of insecure
	 * @throws \RangeException is $newImageCamera is > 64 characters
	 * @throws \TypeError is $newImageCamera is not a string
	 **/
	public function setImageCamera(string $newImageCamera) {
		// verify the image camera is secure
		$newImageCamera = trim($newImageCamera);
		$newImageCamera = filter_var($newImageCamera, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($newImageCamera) === true) {
			throw(new \InvalidArgumentException("image camera is empty or insecure"));
		}

		// verify the image camera will fit in the database
		if(strlen($newImageCamera) > 64) {
			throw(new \RangeException("image camera too large"));
		}

		//store the image camera
		$this->imageCamera = $newImageCamera;
	}

	/** accessor method for imageDescription
	 *
	 * @return string value of imageDescription
	 **/
	public function getImageDescription() {
		return ($this->imageDescription);
	}

	/**
	 * mutator method for image description
	 *
	 * @param string|null $newImageDescription new value of image description
	 * @throws \InvalidArgumentException if $newImageDescription is not a string or insecure
	 * @throws \RangeException if $newImageDescription is > 5000 characters
	 * @throws \TypeError if $newImageDescription is not a string
	 **/
	public function setImageDescription(string $newImageDescription = null) {
		// verify the image description is secure
		$newImageDescription = trim($newImageDescription);
		$newImageDescription = filter_var($newImageDescription, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);

		// verify the image description will fit in the database
		if(strlen($newImageDescription) > 5000) {
			throw(new \RangeException("image description too large"));
		}

		//store the image description
		$this->imageDescription = $newImageDescription;
	}

	/**
	 * accessor method for image earth date
	 *
	 * @return \DateTime value of image earth date
	 **/
	public function getImageEarthDate() {
		return ($this->imageEarthDate);
	}

	/**
	 * mutator method for image earth date
	 *
	 * @param \DateTime|string|null $newImageEarthDate image earth date as a Datetime object or string (or null to load the current time)
	 * @throws \InvalidArgumentException if $newImageEarthDate is not a valid object or string
	 * @throws \RangeException if input is a date that does not exist
	 **/

	public function setImageEarthDate($newImageEarthDate = null) {
		//base case: if the date is null, use the current date and time
		if($newImageEarthDate === null) {
			$this->imageEarthDate = new \DateTime();
			return;
		}

		//store the image earth date
		try {
			$newImageEarthDate = $this->validateDate($newImageEarthDate);
		} catch(\InvalidArgumentException $invalidArgument) {
			throw(new \InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(\RangeException $range) {
			throw(new \RangeException($range->getMessage(), 0, $range));
		}
		$this->imageEarthDate = $newImageEarthDate;
	}

	/**
	 * accessor method for local image file path
	 *
	 * @return string value of image file path
	 **/
	public function getImagePath() {
		return ($this->imagePath);
	}

	/**mutator method for image path
	 *
	 * @param string $newImagePath new value of image file path
	 * @throws \InvalidArgumentException if $newImagePath is not a string or insecure
	 * @throws \RangeException if $newImagePath is > 256 characters
	 * @throw \TypeError if $newImagePath is not a string
	 **/
	public function setImagePath(string $newImagePath) {
		// verify the image path is secure
		$newImagePath = trim($newImagePath);
		$newImagePath = filter_var($newImagePath, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($newImagePath) === true) {
			throw(new \InvalidArgumentException("image path is empty or insecure"));
		}

		// verify the image path will fit in the database
		if(strlen($newImagePath) > 256) {
			throw(new \RangeException("image path too large"));
		}

		// store the image path
		$this->imagePath = $newImagePath;
	}

	/**
	 * accessor method for image sol
	 *
	 * @return int|null $imageSol number of Martian solar days since rover landed at time of image capture
	 */
	public function getImageSol() {
		return ($this->imageSol);
	}

	/**
	 * mutator method for image sol
	 *
	 * @param int|null $newImageSol new value of image sol
	 * @throws \RangeException if $newImageId is not positive
	 * @throws \TypeError if $newImageId is not an integer
	 **/
	public function setImageSol(int $newImageSol) {
		// verify the image sol is positive
		if($newImageSol <= 0) {
			throw(new \RangeException("image sol is not positive"));
		}

		// convert and store the image sol
		$this->imageSol = $newImageSol;
	}

	/**
	 * accessor method for image title
	 *
	 * @return string value of image title
	 **/
	public function getImageTitle() {
		return ($this->imageTitle);
	}

	/**
	 * mutator method for image title
	 *
	 * @param string $newImageTitle new value of image title
	 * @throws \InvalidArgumentException if $newImageTitle is not a string or insecure
	 * @throws \RangeException if $newImageTitle is > 128 characters
	 * @throws \TypeError if $newImageTitle is not a string
	 **/
	public function setImageTitle(string $newImageTitle) {
		// verify the image title is secure
		$newImageTitle = trim($newImageTitle);
		$newImageTitle = filter_var($newImageTitle, FILTER_SANITIZE_STRING);
		if(empty($newImageTitle) === true) {
			throw(new \InvalidArgumentException("image title is empty or insecure"));
		}

		// verify the image title will fit in the database
		if(strlen($newImageTitle) > 128) {
			throw(new \RangeException("image title too large"));
		}

		// store the image title
		$this->imageTitle = $newImageTitle;
	}

	/**
	 * accessor method for image type
	 *
	 * @return string value of image MIME type
	 **/
	public function getImageType() {
		return ($this->imageType);
	}

	/**
	 * mutator method for image type
	 *
	 * @param string $newImageType new value of image MIME type
	 * @throws \InvalidArgumentException if $newImageType is not a string
	 * @throws \RangeException if $newImageType is > 10 characters
	 * @throws \TypeError if $newImageType is not a string
	 **/
	public function setImageType(string $newImageType) {
		// verify the image type is secure
		$newImageType = trim($newImageType);
		$newImageType = filter_var($newImageType, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($newImageType) === true) {
			throw(new \InvalidArgumentException("image type is empty or insecure"));
		}

		// store the image type
		$this->imageType = $newImageType;
	}

	/**
	 * accessor method for image url
	 *
	 * @return string value of full-size image source url
	 **/
	public function getImageUrl() {
		return ($this->imageUrl);
	}

	/**
	 *  mutator method for image url
	 *
	 * @param string $newImageUrl new value of full-size image source url
	 * @throws \InvalidArgumentException if $newImageUrl is not a string or insecure
	 * @throws \RangeException if $newImageUrl is > 256 characters
	 * @throws \TypeError if $newImageUrl is not a string
	 **/
	public function setImageUrl(string $newImageUrl) {
		// verify the image url is secure
		$newImageUrl = trim($newImageUrl);
		$newImageUrl = filter_var($newImageUrl, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($newImageUrl) === true) {
			throw(new \InvalidArgumentException("image url is empty or insecure"));
		}

		// verify the image url will fit in the database
		if(strlen($newImageUrl) > 256) {
			throw(new \RangeException("image url too large"));
		}

		// store the image url
		$this->imageUrl = $newImageUrl;
	}

	/**
	 *
	 * inserts this Image into mySQL
	 * @param \PDO $pdo PDO connection object
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function insert(\PDO $pdo) {
		// enforce the imageId is null (i.e. , don't insert an image that already exists)
		if($this->imageId !== null) {
			throw(new \PDOException("not a new image"));
		}

		// create query template
		$query = "INSERT INTO Image(imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl) VALUES (:imageCamera, :imageDescription, :imageEarthDate, :imagePath, :imageSol,:imageTitle, :imageType, :imageUrl)";
		$statement = $pdo->prepare($query);

		// bind the member variables to the place holders in the template
		$formattedDate = $this->imageEarthDate->format("Y-m-d H:i:s");
		$parameters = ["imageCamera" => $this->imageCamera, "imageDescription" => $this->imageDescription, "imageEarthDate" => $formattedDate, "imagePath" => $this->imagePath, "imageSol" => $this->imageSol, "imageTitle" => $this->imageTitle, "imageType" => $this->imageType, "imageUrl" => $this->imageUrl];
		$statement->execute($parameters);

		// update the null Image Id with what mySQL just gave us
		$this->imageId = intval($pdo->lastInsertId());
	}

	/**
	 * deletes this Image from mySQL
	 *
	 * @param \PDO $pdo PDO connection object
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function delete(\PDO $pdo) {
		// enforce the imageId is not null (i.e., don't delete an image that hasn't been inserted)
		if($this->imageId === null) {
			throw(new \PDOException("unable to delete an image that does not exist"));
		}

		// create query template
		$query = "DELETE FROM Image WHERE imageId = :imageId";
		$statement = $pdo->prepare($query);

		// bind the member variables to the place holder in the template
		$parameters = ["imageId" => $this->imageId];
		$statement->execute($parameters);
	}

	/**
	 * updates this Image in mySQL
	 *
	 * @param \PDO $pdo PDO connection object
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError if $pdo is not a PDO connection object
	 **/
	public function update(\PDO $pdo) {
		// enforce the imageId is not null (i.e., don't update an image that hasn't been inserted)
		if($this->imageId === null) {
			throw(new \PDOException("unable to update an image that does not exist"));
		}

		// create query template
		$query = "UPDATE Image SET imageCamera = :imageCamera, imageDescription = :imageDescription, imageEarthDate = :imageEarthDate, imagePath = :imagePath, imageSol = :imageSol, imageTitle = :imageTitle, imageType = :imageType, imageUrl = :imageUrl";
		$statement = $pdo->prepare($query);

		// bind the member variables to the place holders in the template
		$formattedDate = $this->imageEarthDate->format("Y-m-d H:i:s");
		$parameters = ["imageCamera" => $this->imageCamera, "imageDescription" => $this->imageDescription, "imageEarthDate" => $formattedDate, "imagePath" => $this->imagePath, "imageSol" => $this->imageSol, "imageTitle" => $this->imageTitle, "imageType" => $this->imageType, "imageUrl" => $this->imageUrl];
		$statement->execute($parameters);
	}

	/**
	 * gets the Image by imageId
	 *
	 * @param \PDO $pdo PDO connection object
	 * @param int $imageId image id to search for
	 * @return Image|null Image found or null if not found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getImageByImageId(\PDO $pdo, int $imageId) {
		// sanitize the imageId before searching
		if($imageId <= 0) {
			throw(new \PDOException("image id is not positive"));
		}

		// create query template
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image WHERE imageId = :imageId";
		$statement = $pdo->prepare($query);

		// bind the image id to the place holder in the template
		$parameters = array("imageId" => $imageId);
		$statement->execute($parameters);

		// grab the Image from mySQL
		try {
			$image = null;
			$statement->setFetchMode(\PDO::FETCH_ASSOC);
			$row = $statement->fetch();
			if($row !== false) {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
			}
		} catch(\Exception $exception) {
			// if the row couldn't be converted, rethrow it
			throw(new \PDOException($exception->getMessage(), 0, $exception));
		}
		return ($image);
	}

	/**
	 * gets the Image by imageCamera
	 *
	 * @param \PDO $pdo PDO connection object
	 * @param string $imageCamera image camera to search for
	 * @return \SplFixedArray SplFixedArray of Images found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getImageByImageCamera(\PDO $pdo, string $imageCamera) {
		// sanitize the description before searching
		$imageCamera = trim($imageCamera);
		$imageCamera = filter_var($imageCamera, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($imageCamera) === true) {
			throw(new \PDOException("image camera is invalid"));
		}

		// create query template
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image WHERE imageCamera LIKE :imageCamera";
		$statement = $pdo->prepare($query);

		// bind the image camera to the place holder in the template
		$imageCamera = "%$imageCamera%";
		$parameters = array("imageCamera" => $imageCamera);
		$statement->execute($parameters);

		// build an array of Images
		$images = new \SplFixedArray(($statement->rowCount()));
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
				$images[$images->key()] = $image;
				$images->next();
			} catch(\Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($images);
	}

	/**
	 * gets the Image by imageDescription
	 *
	 * @param \PDO $pdo PDO connection object
	 * @param string $imageDescription image description to search for
	 * @return \SplFixedArray SplFixedArray of Images found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getImageByImageDescription(\PDO $pdo, string $imageDescription) {
		// sanitize the description before searching
		$imageDescription = trim($imageDescription);
		$imageDescription = filter_var($imageDescription, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($imageDescription) === true) {
			throw(new \PDOException("image description is invalid"));
		}

		// create query template
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image WHERE imageDescription LIKE :imageDescription";
		$statement = $pdo->prepare($query);

		// bind an array of images
		$imageDescription = "%$imageDescription%";
		$parameters = array("imageDescription" => $imageDescription);
		$statement->execute($parameters);

		//build an array of images
		$images = new \SplFixedArray(($statement->rowCount()));
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
				$images[$images->key()] = $image;
				$images->next();
			} catch(\Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($images);
	}

	/**
	 * get the Image by image title
	 *
	 * @param \PDO $pdo PDO connection object
	 * @param string $imageTitle image title to search for
	 * @return \SplFixedArray SplFixedArray of Images found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getImageByImageTitle(\PDO $pdo, string $imageTitle) {
		// sanitize the description before searching
		$imageTitle = trim($imageTitle);
		$imageTitle = filter_var($imageTitle, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
		if(empty($imageTitle) === true) {
			throw(new \PDOException("image title is invalid"));
		}

		// create query template
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image WHERE imageTitle LIKE :imageTitle";
		$statement = $pdo->prepare($query);

		// bind the image title to the place holder in the template
		$imageTitle = "%$imageTitle%";
		$parameters = array("imageTitle" => $imageTitle);
		$statement->execute($parameters);

		// build an array of Images
		$images = new \SplFixedArray(($statement->rowCount()));
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
				$images[$images->key()] = $image;
				$images->next();
			} catch(\Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($images);
	}

	/**
	 * get the Image by image sol
	 *
	 * @param \PDO $pdo PDO connection object
	 * @param int $imageSol Image sol to search for
	 * @return \SplFixedArray SplFixedArray of Images found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getImagesByImageSol(\PDO $pdo, int $imageSol) {
		// sanitize the imageId before searching
		if($imageSol <= 0) {
			throw(new \PDOException("image sol is not positive"));
		}
		// create query template
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image WHERE imageSol = :imageSol";
		$statement = $pdo->prepare($query);

		// bind the image id to the place holder in the template
		$parameters = array("imageSol" => $imageSol);
		$statement->execute($parameters);

		//build an array of images
		$images = new \SplFixedArray(($statement->rowCount()));
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
				$images[$images->key()] = $image;
				$images->next();
			} catch(\Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($images);
	}

	/**
	 * gets the Image by imageEarthDate
	 *
	 * @param \PDO $pdo PDO connection object
	 * @param \DateTime $imageEarthDate image earth date to search for
	 * @return \SplFixedArray SplFixedArray of Images found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 */
	public static function getImagesByImageEarthDate(\PDO $pdo, \DateTime $imageEarthDate) {
		// sanitize the imageEarthDate before searching
		try {
			$imageEarthDate = self::validateDate($imageEarthDate);
		} catch(\InvalidArgumentException $invalidArgument) {
			throw(new \InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
		} catch(\RangeException $range) {
			throw(new \RangeException($range->getMessage(), 0, $range));
		}

		// create query template
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image WHERE imageEarthDate = :imageEarthDate";
		$statement = $pdo->prepare($query);

		// bind the image id to the place holder in the template
		$parameters = array("imageEarthDate" => $imageEarthDate->format("Y-m-d H:i:s"));
		$statement->execute($parameters);

		//build an array of images
		$images = new \SplFixedArray(($statement->rowCount()));
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
				$images[$images->key()] = $image;
				$images->next();
			} catch(\Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($images);
	}

	/**
	 * gets the Image by imageUrl
	 * sanitization is unnecessary, as this method is strictly internal
	 *
	 * @param \PDO $pdo PDO connection object
	 * @param string $imageUrl image URL to search for
	 * @return Image found or null if none
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static function getImageByImageUrl(\PDO $pdo, string $imageUrl) {

		// create query template
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image WHERE imageUrl LIKE :imageUrl";
		$statement = $pdo->prepare($query);

		// bind the image url to the place holder in the template
		$imageUrl = "%$imageUrl%";
		$parameters = array("imageUrl" => $imageUrl);
		$statement->execute($parameters);

		// grab the Image from mySQL
		try {
			$image = null;
			$statement->setFetchMode(\PDO::FETCH_ASSOC);
			$row = $statement->fetch();
			if($row !== false) {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
			}
		} catch(\Exception $exception) {
			// if the row couldn't be converted, rethrow it
			throw(new \PDOException($exception->getMessage(), 0, $exception));
		}
		return ($image);
	}

	/**
	 * gets all Images
	 *
	 * @param \PDO $pdo
	 * @return \SplFixedArray SplFixedArray of Images found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/

	public static function getAllImages(\PDO $pdo) {
		// create query template
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image";
		$statement = $pdo->prepare($query);
		$statement->execute();

		// build an array of images
		$images = new \SplFixedArray($statement->rowCount());
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
				$images[$images->key()] = $image;
				$images->next();
			} catch(\Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($images);
	}

	/**
	 * gets 25 most recent images
	 * @param \PDO $pdo
	 * @return \SplFixedArray of Images found
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 **/
	public static
	function getImages(\PDO $pdo) {
		$query = "SELECT imageId, imageCamera, imageDescription, imageEarthDate, imagePath, imageSol, imageTitle, imageType, imageUrl FROM Image ORDER BY imageEarthDate DESC LIMIT 25";
		$statement = $pdo->prepare($query);
		$statement->execute();

		$images = new \SplFixedArray($statement->rowCount());
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		while(($row = $statement->fetch()) !== false) {
			try {
				$image = new Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
				$images[$images->key()] = $image;
				$images->next();
			} catch(\Exception $exception) {
				// if the row couldn't be converted, rethrow it
				throw(new \PDOException($exception->getMessage(), 0, $exception));
			}
		}
		return ($images);
	}

	/**
	 * gets a random image from the database
	 *
	 * @param \PDO $pdo
	 * @return Image
	 * @throws \PDOException when mySQL related errors occur
	 * @throws \TypeError when variables are not the correct data type
	 */
	public static function getRandomImage(\PDO $pdo) {
		
	}

	/**
	 * format the state variables for JSON serialization
	 *
	 * @return array resulting state variables to serialize
	 **/
	public function jsonSerialize() {
		$fields = get_object_vars($this);
		$fields["imageEarthDate"] = intval($this->imageEarthDate->format("U")) * 1000;
		return ($fields);
	}
}





