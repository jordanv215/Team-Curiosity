<?php
namespace Redrovr\TeamCuriosity\Test;

use Redrovr\TeamCuriosity\{
	LoginSource
};

// grab the test parameters
require_once("./TeamCuriosityTest.php");

//grab the class under scrutiny
require_once("../php/classes/Autoload.php");

/**
 * PHPUnit test for the LoginSource class
 *
 * This is a test of the LoginSource class belonging to the team-curiosity project. All PDO enabled methods are tested for valid and invalid inputs.
 *
 * @see LoginSource
 * @author Kai Garrott <garrottkai@gmail.com>
 **/
class LoginSourceTest extends TeamCuriosityTest {
	/**
	 * api key of the login source
	 * @var string $VALID_LOGIN_SOURCE_API_KEY
	 **/
	protected $VALID_LOGIN_SOURCE_API_KEY = "abc123def456";
	/**
	 * another api key for login source
	 * @var string $VALID_LOGIN_SOURCE_API_KEY2
	 **/
	protected $VALID_LOGIN_SOURCE_API_KEY2 = "xyz789uvw000";
	/**
	 * social media login provider
	 * @var string $VALID_LOGIN_SOURCE_PROVIDER
	 **/
	protected $VALID_LOGIN_SOURCE_PROVIDER = "congratulations";
	/**
	 * test inserting a valid login source
	 **/
	public function testInsertValidLoginSource() {
		// count number of table rows
		$numRows = $this->getConnection()->getRowCount("LoginSource");

		//create a new login source and insert it into table
		$loginSource = new LoginSource(null, $this->VALID_LOGIN_SOURCE_API_KEY, $this->VALID_LOGIN_SOURCE_PROVIDER);
		$loginSource->insert($this->getPDO());

		// grab data from table and ensure it matches expectations
		$pdoLoginSource = LoginSource::getLoginSourceByLoginSourceId($this->getPDO(), $loginSource->getLoginSourceId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("LoginSource"));
		$this->assertEquals($pdoLoginSource->getLoginSourceId(), $loginSource->getLoginSourceId());
		$this->assertEquals($pdoLoginSource->getLoginSourceApiKey(), $loginSource->getLoginSourceApiKey());
		$this->assertEquals($pdoLoginSource->getLoginSourceProvider(), $loginSource->getLoginSourceProvider());
	}

	/**
	 *  test inserting a login source with a primary key that already exists
	 *
	 * @expectedException \PDOException
	 **/
	public function testInsertInvalidLoginSource() {
		// create a login source with a non null id - it should fail
		$loginSource = new LoginSource(TeamCuriosityTest::INVALID_KEY, $this->VALID_LOGIN_SOURCE_API_KEY, $this->VALID_LOGIN_SOURCE_PROVIDER);
		$loginSource->insert($this->getPDO());
	}
	
	/**
	 * test inserting a login source, editing it, then updating it
	 **/
	public function testUpdateValidLoginSource() {
		// count the number of rows
		$numRows = $this->getConnection()->getRowCount("LoginSource");
		
		//create a new login source and insert it into table
		$loginSource = new LoginSource(null, $this->VALID_LOGIN_SOURCE_API_KEY, $this->VALID_LOGIN_SOURCE_PROVIDER);
		$loginSource->insert($this->getPDO());
		
		// edit the login source and update it in table
		$loginSource->setLoginSourceApiKey($this->VALID_LOGIN_SOURCE_API_KEY2);
		$loginSource->update($this->getPDO());
		
		// grab the table data and enforce that fields match expectations
		$pdoLoginSource = LoginSource::getLoginSourceByLoginSourceId($this->getPDO(), $loginSource->getLoginSourceId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("LoginSource"));
		$this->assertEquals($pdoLoginSource->getLoginSourceApiKey(), $loginSource->getLoginSourceApiKey());
		$this->assertEquals($pdoLoginSource->getLoginSourceProvider(), $loginSource->getLoginSourceProvider());
	}
	
	/**
	 * test creating a login source and then deleting it
	 *
	 **/
	public function testDeleteValidLoginSource() {
		// count number of rows
		$numRows = $this->getConnection()->getRowCount("LoginSource");
		
		// create a new login source and insert into table
		$loginSource = new LoginSource(null, $this->VALID_LOGIN_SOURCE_API_KEY, $this->VALID_LOGIN_SOURCE_PROVIDER);
		$loginSource->insert($this->getPDO());
		
		// delete it from the table
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("LoginSource"));
		$loginSource->delete($this->getPDO());
		
		// grab data from table and enforce that login source does not exist
		$pdoLoginSource = LoginSource::getLoginSourceByLoginSourceId($this->getPDO(), $loginSource->getLoginSourceId());
		$this->assertNull($pdoLoginSource);
		$this->assertEquals($numRows, $this->getConnection()->getRowCount("LoginSource"));
	}
	
	/**
	 * test deleting a login source that does not exist
	 * 
	 * @expectedException \PDOException
	 **/
	public function testDeleteInvalidLoginSource() {
		// create a new login source and attempt to delete it without first inserting it into a table
		$loginSource = new LoginSource(null, $this->VALID_LOGIN_SOURCE_API_KEY, $this->VALID_LOGIN_SOURCE_PROVIDER);
		$loginSource->delete($this->getPDO());
	}

	/**
	 * test inserting a login source and regrabbing it from mySQL
	 **/
	public function testGetValidLoginSourceByLoginSourceId() {
		// count the number of rows
		$numRows = $this->getConnection()->getRowCount("LoginSource");

		// create a new login source and insert it into the table
		$loginSource = new LoginSource(null, $this->VALID_LOGIN_SOURCE_API_KEY, $this->VALID_LOGIN_SOURCE_PROVIDER);
		$loginSource->insert($this->getPDO());

		// grab the data from mySQL and enforce that fields match expectations
		$pdoLoginSource = LoginSource::getLoginSourceByLoginSourceId($this->getPDO(), $loginSource->getLoginSourceId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("LoginSource"));
		$this->assertEquals($pdoLoginSource->getLoginSourceId(), $loginSource->getLoginSourceId());
		$this->assertEquals($pdoLoginSource->getLoginSourceApiKey(), $this->VALID_LOGIN_SOURCE_API_KEY);
		$this->assertEquals($pdoLoginSource->getLoginSourceProvider(), $this->VALID_LOGIN_SOURCE_PROVIDER);
	}

	/**
	 * test grabbing a login source that does not exist
	 **/
	public function testGetInvalidLoginSourceByLoginSourceId() {
		// grab a login source id that exceeds the maximum allowable value
		$loginSource = LoginSource::getLoginSourceByLoginSourceId($this->getPDO(), TeamCuriosityTest::INVALID_KEY);
		$this->assertNull($loginSource);
	}

	/**
	 * test grabbing a login source by provider
	 **/
	public function testGetValidLoginSourceByLoginSourceProvider() {
		// count number of rows
		$numRows = $this->getConnection()->getRowCount("LoginSource");

		// create a new login source and insert into table
		$loginSource = new LoginSource(null, $this->VALID_LOGIN_SOURCE_API_KEY, $this->VALID_LOGIN_SOURCE_PROVIDER);
		$loginSource->insert($this->getPDO());

		// grab data from mySQL and enforce that fields match expectations
		$results = LoginSource::getLoginSourceByLoginSourceProvider($this->getPDO(), $loginSource->getLoginSourceProvider());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("LoginSource"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Redrovr\\TeamCuriosity\\LoginSource", $results);

		// grab the login source from the array and validate it
		$pdoLoginSource = $results[0];
		$this->assertEquals($pdoLoginSource->getLoginSourceId(), $loginSource->getLoginSourceId());
		$this->assertEquals($pdoLoginSource->getLoginSourceApiKey(), $this->VALID_LOGIN_SOURCE_API_KEY);
		$this->assertEquals($pdoLoginSource->getLoginSourceProvider(), $this->VALID_LOGIN_SOURCE_PROVIDER);
	}

	/**
	 * test grabbing a login source by a provider that does not exist
	 **/
	public function testGetInvalidLoginSourceByLoginSourceProvider () {
		// grab a login source by searching for string that does not exist
		$loginSource = LoginSource::getLoginSourceByLoginSourceProvider($this->getPDO(), "there's nothing here");
		$this->assertCount(0, $loginSource);
	}

	/**
	 * test grabbing all login source entries
	 **/
	public function testGetAllValidLoginSource() {
		// count number of rows
		$numRows = $this->getConnection()->getRowCount("LoginSource");

		// create a new login source and insert into table
		$loginSource = new LoginSource(null, $this->VALID_LOGIN_SOURCE_API_KEY, $this->VALID_LOGIN_SOURCE_PROVIDER);
		$loginSource->insert($this->getPDO());

		// grab data from table and enforce that fields match expectations
		$results = LoginSource::getAllLoginSources($this->getPDO());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("LoginSource"));
		$this->assertCount(1, $results);
		$this->assertContainsOnlyInstancesOf("Redrovr\\TeamCuriosity\\LoginSource", $results);
		
		// grab result from array and validate it
		$pdoLoginSource = $results [0];
		$this->assertEquals($pdoLoginSource->getLoginSourceId(), $loginSource->getLoginSourceId());
		$this->assertEquals($pdoLoginSource->getLoginSourceApiKey(), $this->VALID_LOGIN_SOURCE_API_KEY);
		$this->assertEquals($pdoLoginSource->getLoginSourceProvider(), $this->VALID_LOGIN_SOURCE_PROVIDER);
	}
}