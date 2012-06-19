<?php

namespace Models;

require_once 'test/TestHelper.php';
require_once 'models/LastPlayedRadioFM.php';

class LastPlayedRadioFMTest extends \TestCase
{
	public function setUp()
	{
		$this->db = $this->getMock('mysqli');
		
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
		return $this->getMock('mysqli_stmt', [], [], '', false);
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
			->expects($this->once())
			->method('bind_param')
			->with(
				'issssss', 
				$this->response['id'], '2012-06-17 18:21:58', 
				$this->response['artist'], $this->response['album'], $this->response['song'],
				$this->response['cover'], $this->response['program']
			);
			
		$statement
			->expects($this->once())
			->method('execute');
			
		LastPlayedRadioFM::createFromResponse($this->response);
	}
	
	private function createMockedResult()
	{
		return $this->getMock('mysqli_result', [], [], '', false);
	}
	
	//public function testLastShouldQueryLastRow()
	//{
	//	$result = $this->createMockedResult();
	//
	//	$this->db
	//		->expects($this->once())
	//		->method('query')
	//		->with($this->matchesQuery('select', 'last_played_radiofm', 'order by id desc', 'limit 1'))
	//		->will($this->returnValue($result));
	//
	//	$last = LastPlayedRadioFM::last();
	//}
	
	///**
	// * @expectedExceptionMessage 1 row was expected, 2 fetched
	// */
	//public function testLastShouldThrowExceptionIfZeroOrMultipleRowsAreFetched()
	//{
	//	$result = $this->createMockedResult();
	//		
	//	$this->db
	//		->expects($this->once())
	//		->method('query')
	//		->will($this->returnValue($result));
	//	
	//	//$result
	//	//	->expects($this->at(0))
	//	//	->method('__get')
	//	//	->with('num_rows')
	//	//	->will($this->returnValue(2));
	//
	//	$last = LastPlayedRadioFM::last();
	//}
}