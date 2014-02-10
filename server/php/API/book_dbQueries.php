<?php

include_once '_database_includes.php';

class LatLongPair
{
	public $latitude;
	public $longitude;
}

//returns the minimum latitude longitude pair based on a given distance , and a lat long pair
function GetMinLatLong( $distance , $platlong )
{
	$minlatlong = new LatLongPair();

	$minlatlong->latitude = $platlong->latitude - ($distance/69);
	$minlatlong->longitude = $platlong->longitude - $distance/abs(cos($platlong->latitude)*69);
	
	return $minlatlong;
}

//returns the maximum latitude longitude pair based on a given distance , and a lat long pair
function GetMaxLatLong( $distance , $platlong )
{
	$maxlatlong = new LatLongPair();
	
	$maxlatlong->latitude = $platlong->latitude + ($distance/69);
	$maxlatlong->longitude = $platlong->longitude + $distance/abs(cos($platlong->latitude)*69);
	
	return $maxlatlong;
}


function InsertNewBook($owner_id , $book_name , $book_review , $latitude , $longitude)
{
	global $book_table_name;

	DB::insert($book_table_name , array(
			'owner_id' => $owner_id,
			'book_name' => $book_name,
			'book_review' => $book_review,
			'latitude' => $latitude,
			'longitude' => $longitude,
		));

	return DB::insertId();
}

function UpdateBook($id , $owner_id='', $book_name='' , $book_review='' , $latitude='' , $longitude='')
{
	global $book_table_name;

	$array = array();

	if($owner_id != '')	{
		$array['owner_id'] = $owner_id;
	}

	if($book_name != '')	{
		$array['book_name'] = $book_name;
	}

	if($book_review != '')	{
		$array['book_review'] = $book_review;
	}

	if($latitude != '')	{
		$array['latitude'] = $latitude;
	}

	if ($longitude != '') {
		$array['longitude'] = $longitude;
	}

	DB::update($book_table_name,$array,"id=%i",$id);

	return DB::affectedRows();
}

function DeleteBook($id)
{
	global $book_table_name;

	DB::delete($book_table_name , "id=%i" , $id);
}

function GetBooksByOwnerid($owner_id)
{
	global $book_table_name;
	return DB::query("SELECT * from $book_table_name where owner_id = %i", $owner_id);
}

function GetBooksByName($name , $limit = 1000 , $offset = 0)
{
	global $book_table_name;
	global $user_table_name;
	
	return DB::query("SELECT owner.name as owner_name , book_table.* FROM $book_table_name as book_table ,$user_table_name as owner WHERE book_table.owner_id = owner.id AND MATCH (book_name) AGAINST (%s) LIMIT %i,%i",$name,$offset,$limit);
}

function GetBooksByNameWithinRadiusByLatLong($name , $dis = 10 , $lat = '' , $lon = '' , $limit = 1000 , $offset = 0)
{
	global $book_table_name;
	global $user_table_name;

	if($lat == '' || $lon =='')
	{
		return GetBooksByName($name);
	}

	$pLatlong = new LatLongPair();

	$pLatlong->latitude = $lat;
	$pLatlong->longitude = $lon;

	$min_latlong = GetMinLatLong($dis , $pLatlong);
	$max_latlong = GetMaxLatLong($dis , $pLatlong);

	return DB::query("SELECT owner.name as owner_name , book_table.*".
		"FROM $book_table_name as book_table ,$user_table_name as owner".
		"WHERE book_table.owner_id = owner.id".
		"AND MATCH (book_name) AGAINST (%s)".
		"AND latitude BETWEEN %d and %d".
		"AND longitude BETWEEN %d and %d".
		"LIMIT %i,%i" , $name , 
		$min_latlong->latitude,
		$max_latlong->latitude ,
		$min_latlong->longitude ,
		$max_latlong->longitude , 
		$offset,$limit
		);
}

?>