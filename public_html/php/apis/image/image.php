<?php
// This is a place to write the image retrieval script before it's approved as actually being usable.
// this will be run upon loading of the Image view? Home view? wat? location TBD based on speed of execution

public function getLastRan() {
	$fh = fopen('php/last-ran.txt', 'r+');
	$time = fgets($fh);
	fclose($fh);
	return $time;
}

	if(time() - $this->time > 60) {
	$timeRan = time();
	function setTimeRan() {
		$fh = fopen('php/last-ran.txt', 'w+');
		fwrite($fh, $this->timeRan);
		fclose($fh);
		}
		public function NasaCall() {
			$baseUrl = "https://api.nasa.gov/mars-photos/api/v1/rovers/curiosity/photos";
			$apiKey = ""; // how is this accessed again?
			$camera = "";
			$earthDate = "";
			$sol = "";
		}
		$image = \Edu\Cnm\TeamCuriosity\Image::getImageByImageUrl($pdo,)
	}
	else {
		public function getImages(\PDO $pdo, int $imageId) {
			$query = "SELECT * FROM Image WHERE imageId > MAX(imageId) - 25";
			$statement = $pdo->prepare($query);
			$parameters = array("imageId" => $imageId);
			$statement->execute($parameters);

			$images = new \SplFixedArray($statement->rowCount());
			$statement->setFetchMode(\PDO::FETCH_ASSOC);
			while(($row = $statement->fetch()) !== false) {
				try {
					$image = new Edu\Cnm\TeamCuriosity\Image($row["imageId"], $row["imageCamera"], $row["imageDescription"], \DateTime::createFromFormat("Y-m-d H:i:s", $row["imageEarthDate"]), $row["imagePath"], $row["imageSol"], $row["imageTitle"], $row["imageType"], $row["imageUrl"]);
					$images[$images->key()] = $image;
					$images->next();
				} catch(\Exception $exception) {
					// if the row couldn't be converted, rethrow it
					throw(new \PDOException($exception->getMessage(), 0, $exception));
				}
			}
			return ($images);
		}
	}
