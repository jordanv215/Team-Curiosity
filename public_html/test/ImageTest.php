<?php
namespace Edu\Cnm\TeamCuriosity\Test;

use Edu\Cnm\TeamCuriosity\{Image};

// grab the project test parameters
require_once(dirname(__DIR__) . "/PHP/classes/autoload.php");

/**
 * Full PHPUnit test for the Image class
 * 
 * This is a complete PHPUnit test of the Image class. It is complete because *ALL* mySOL/PDO enabled methods are tested for both invalid and valid inputs
 * 
 * @see Image
 **/
class ImageTest extends TeamCuriosityTest {
	/**
	 * Camera of where image was taken
	 * @var string $VALID_IMAGECAMERA
	 **/
	protected $VALID_IMAGECAMERA = "PHPUnit test passing";
	/**
	 * description of the Image
	 * @var string $VALID_IMAGEDESCRIPTION
	 **/
	protected $VALID_IMAGEDESCRIPTION = "PHPUnit test passing";
	/**
	 * timestamp of the Image; this starts as null and is assigned later
	 * @var DateTime $VALID_IMAGEEARTHDATE
	 **/
	protected $VALID_IMAGEEARTHDATE = null;
	/**
	 * martian date for image
	 * @var  int $VALID_IMAGESOL
	 */
	protected $VALID_IMAGESOL = "PHPUnit test passing";
	/**
	 * title of image
	 * @var $VALID_IMAGETITLE
	 */
	protected $VALID_IMAGETITLE = "PHPUnit test is passing";

	/**
	 * test inserting a valid Image and verify that the actual mySQL data matches
	 **/
	public function testInsertValidImage() {
		// count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("Image");

		//create a new image and insert into mySQL
		$image = new image(null, $this->image->getImageId(), $this->VALID_IMAGECAMERA, $this->VALID_IMAGEDESCRIPTION, $this->VALID_IMAGEEARTHDATE, $this->VALID_IMAGESOL, $this->VALID_IMAGETITLE);
		$image->insert($this->getPDO());

		//grab the data from mySQL and enforce the fields match our expectations
		$pdoImage = Image::getImageByImageId($this->getPDO(), $image->getImageId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("Image"));
		$this->assertEquals($pdoImage->getImageByImageCamera(), $this->VALID_IMAGECAMERA);
		$this->assertEquals($pdoImage->getImageByImageDescription(), $this->VALID_IMAGEDESCRIPTION);
		$this->assertEquals($pdoImage->getImageByEarthDate(), $this->VALID_IMAGEEARTHDATE);
		$this->assertEquals($pdoImage->getImageByImageSol(), $this->VALID_IMAGESOL);
		$this->assertEquals($pdoImage->getImageByImageTitle(), $this->VALID_IMAGETITLE);
	}

	/**
	 * test inserting a image that already exists
	 *
	 * @expectedException PDOException
	 **/
	public function testInsertInvalidImage() {
		// create a image with a non null image id and watch it fail
		$image = new Image(TeamCuriosityTest::INVALID_KEY, $this->image->getImageId(), $this->VALID_IMAGECAMERA, $this->VALID_IMAGEDESCRIPTION, $this->VALID_IMAGEEARTHDATE, $this->VALID_IMAGESOL, $this->VALID_IMAGETITLE);
		$image->insert($this->getPDO());
	}

	/**
	 * test creating a Image and then deleting it
	 **/
	public function testDeleteValidImage() {
		// count the number of row sna dsave it for later
		$numrows = $this->getConnection()->getRowCount("Image");

		//create a new Image and insert into mySQL
		$image = new Image(null, $this->image->getImageId(), $this->VALID_IMAGECAMERA, $this->VALID_IMAGEDESCRIPTION, $this->VALID_IMAGEEARTHDATE, $this->VALID_IMAGESOL, $this->VALID_IMAGETITLE);
		$image->insert($this->getPDO());

		// delete the Image from mySQL
		$this->assertEquals($numrows + 1, $this->getConnection()->getRowCount("Image"));
		$image->delete($this->getPDO());

		// grab the data from mySQL and enforce the Image does not exist
		$pdoImage = Image::getImageByImageId($this->getPDO(), $image->getImageId());
		$this->assertNull($pdoImage);
		$this->assertEquals($numrows, $this->getConnection()->getRowCount("Image"));
	}

	/**
	 * test deleting a Image that does not exist
	 *
	 * @expectedException PDOException
	**/
	public function testDeleteInvalidImage() {
		// create a Image and try to delete it without actually inserting it
		$image = new Image(null, $this->image->getImageId(), $this->VALID_IMAGECAMERA, $this->VALID_IMAGEDESCRIPTION, $this->VALID_IMAGEEARTHDATE, $this->VALID_IMAGESOL, $this->VALID_IMAGETITLE);
		$image->delete($this->getPDO());
	}

	/**
	 * test grabbing a Image that does not exist
	 **/
	public function testGetInvalidImageByImageId() {
		// grab a image id that exceeds the miximum allowable image id
		$image = Image::getImageByImageId($this->getPDO(), TeamCuriosityTest::INVALID_KEY);
		$this->assertNull($image);
	}

	/**
	 * test grabbing a Image by image camera
	 **/
	public function testGetValidImageByImageCamera() {
		// count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("Image");

		// create a new image and insert into mySQL
		$image = new Image(null, $this->image->getImageId(), $this->VALID_IMAGECAMERA, $this->VALID_IMAGEDESCRIPTION, $this->VALID_IMAGEEARTHDATE, $this->VALID_IMAGESOL, $this->VALID_IMAGETITLE);
		$image->insert($this->getPDO());

		// grab the data from mySQL and enforce the fields match our expectations
		$results = Image::getImageByImageCamera($this->getPDO(), $image->getImageCamera());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("Image"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\TeamCuriosity\\Test", $results);

		// grab the result from the array and validate it
		$pdoImage = $results[0];
		$this->assertEquals($pdoImage->getImageId(), $this->image->getImageId());
		$this->assertEquals($pdoImage->getImageByImageCamera(), $this->VALID_IMAGECAMERA);
		$this->assertEquals($pdoImage->getImageByImageDescription(), $this->VALID_IMAGEDESCRIPTION);
		$this->assertEquals($pdoImage->getImageByEarthDate(), $this->VALID_IMAGEEARTHDATE);
		$this->assertEquals($pdoImage->getImageByImageSol(), $this->VALID_IMAGESOL);
		$this->assertEquals($pdoImage->getImageByImageTitle(), $this->VALID_IMAGETITLE);
	}

	/**
	 * test grabbing a Image by imageCamera that does not exist
	 **/
	public function testGetInvalidImageByImageCamera() {
		// grab a Image by image camera did not take the image of
		$image = Image::getImageByImageCamera($this->getPDO(), "This image was not taken by camera");
		$this->assertCount(0, $image);
	}

	/**
	 * test grabbing a Image by image description
	 **/
	public function testGetValidImageByImageDescription() {
		// count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("Image");

		// create a new image and insert into mySQL
		$image = new Image(null, $this->image->getImageId(), $this->VALID_IMAGECAMERA, $this->VALID_IMAGEDESCRIPTION, $this->VALID_IMAGEEARTHDATE, $this->VALID_IMAGESOL, $this->VALID_IMAGETITLE);
		$image->insert($this->getPDO());

		// grab the data from mySQL and enforce the fields match our expectations
		$results = Image::getImageByImageDescription($this->getPDO(), $image->getImageDescription());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("Image"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\TeamCuriosity\\Test", $results);

		// grab the result from the array and validate it
		$pdoImage = $results[0];
		$this->assertEquals($pdoImage->getImageId(), $this->image->getImageId());
		$this->assertEquals($pdoImage->getImageByImageCamera(), $this->VALID_IMAGECAMERA);
		$this->assertEquals($pdoImage->getImageByImageDescription(), $this->VALID_IMAGEDESCRIPTION);
		$this->assertEquals($pdoImage->getImageByEarthDate(), $this->VALID_IMAGEEARTHDATE);
		$this->assertEquals($pdoImage->getImageByImageSol(), $this->VALID_IMAGESOL);
		$this->assertEquals($pdoImage->getImageByImageTitle(), $this->VALID_IMAGETITLE);
	}

	/**
	 * test grabbing a Image by imageDescription that does not exist
	 **/
	public function testGetInvalidImageByImageDescription() {
		// grab a Image by image camera did not take the image of
		$image = Image::getImageByImageDescription($this->getPDO(), "This image was not taken by camera");
		$this->assertCount(0, $image);
	}
	/**
	 * test grabbing all Images
	 **/
	public function testGetAllValidImages() {
		// count the number of tow s and save it for later
		$numRows = $this->getConnection()->getRowCount("Image");

		// create a new Image and insert into mySQL
		$image = new Image(null, $this->image->getImageId(), $this->VALID_IMAGECAMERA, $this->VALID_IMAGEDESCRIPTION, $this->VALID_IMAGEEARTHDATE, $this->VALID_IMAGESOL, $this->VALID_IMAGETITLE);
		$image->insert($this->getPDO());

		// grab the data from mySQL and enforce the fields match our expectations
		$results = Image::getAllImages($this->getPDO(), $image->getImageCamera());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("Image"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\TeamCuriosity\\Test", $results);

		// grab the result from the array and validate it
		$pdoImage = $results[0];
		$this->assertEquals($pdoImage->getImageId(), $this->image->getImageId());
		$this->assertEquals($pdoImage->getImageByImageCamera(), $this->VALID_IMAGECAMERA);
		$this->assertEquals($pdoImage->getImageByImageDescription(), $this->VALID_IMAGEDESCRIPTION);
		$this->assertEquals($pdoImage->getImageByEarthDate(), $this->VALID_IMAGEEARTHDATE);
		$this->assertEquals($pdoImage->getImageByImageSol(), $this->VALID_IMAGESOL);
		$this->assertEquals($pdoImage->getImageByImageTitle(), $this->VALID_IMAGETITLE);
	}
}