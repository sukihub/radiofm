<?php

namespace Models;

require_once 'test/TestHelper.php';
require_once 'models/LastPlayedRadioFM.php';

class LastPlayedRadioFMTest extends \TestCase
{
	public function setUp()
	{
		$this->db = $this->getMock('MockPDO');		
		LastPlayedRadioFM::connect($this->db);
	}

	private $response = [
		"id" => 16904,
		"time_unix" => 1339957318,
		"artist" => "All The People",
		"album" => "",
		"song" => "Nitelife",
		"cover" => "http://www.sepia.sk/tuner/cover/cddisc.jpg",
		"program" => "Nu Spirit FM"
	]; 
	
	private function createMockedStatement()
	{
		return $this->getMock('MockPDOStatement');
	}

	public function testCreateFromResponseShouldPrepareInsertStatement()
	{
		$statement = $this->createMockedStatement(); 
	
		$this->db
			->expects($this->once())
			->method('prepare')
			->with($this->matchesQuery('insert', 'last_played_radiofm', 'radiofm_id, played_at, artist, album, song, cover, program'))
			->will($this->returnValue($statement));
			
		LastPlayedRadioFM::createFromResponse($this->response);
	}
	
	public function testCreateFromResponseShouldBindParamsAndExecute()
	{
		$statement = $this->createMockedStatement();
	
		$this->db
			->expects($this->once())
			->method('prepare')
			->will($this->returnValue($statement));
			
		$statement
			->expects($this->at(0))
			->method('bindParam')
			->with(':radiofm_id', $this->response['id'], \PDO::PARAM_INT);
		
		$statement
			->expects($this->once())
			->method('execute');
			
		LastPlayedRadioFM::createFromResponse($this->response);
	}
	
	private function createMockedResult($rowCount)
	{
		$statement = $this->getMock('MockPDOStatement');
		
		$statement
			->expects($this->any())
			->method('rowCount')
			->will($this->returnValue($rowCount));
			
		return $statement;
	}
	
	public function testLastShouldQueryLastRow()
	{
		$result = $this->createMockedResult(1);
	
		$this->db
			->expects($this->once())
			->method('query')
			->with($this->matchesQuery('select', 'last_played_radiofm', 'order by id desc', 'limit 1'))
			->will($this->returnValue($result));
	
		$last = LastPlayedRadioFM::last();
	}
	
	/**
	 * @expectedException Models\LastPlayedException
	 * @expectedExceptionMessage 1 row was expected, 0 fetched
	 */
	public function testLastShouldThrowExceptionIfZeroRowsAreFetched()
	{
		$result = $this->createMockedResult(0);
			
		$this->db
			->expects($this->once())
			->method('query')
			->will($this->returnValue($result));
			
		$last = LastPlayedRadioFM::last();
	}
	
	/**
	 * @expectedException Models\LastPlayedException
	 * @expectedExceptionMessage 1 row was expected, 2 fetched
	 */
	public function testLastShouldThrowExceptionIfTwoRowsAreFetched()
	{
		$result = $this->createMockedResult(2);
			
		$this->db
			->expects($this->once())
			->method('query')
			->will($this->returnValue($result));
			
		$last = LastPlayedRadioFM::last();
	}
	
	public function testLastShouldReturnDatabaseRow()
	{
		$result = $this->createMockedResult(1);
		
		$this->db
			->expects($this->once())
			->method('query')
			->will($this->returnValue($result));
		
		$row = [ 'id' => 5, 'radiofm_id' => 15654, 'song' => 'Itchin on a photograph' ];
			
		$result
			->expects($this->once())
			->method('fetch')
			->will($this->returnValue($row));
		
		$last = LastPlayedRadioFM::last();
		
		$this->assertEquals($row, $last);	
	}
}