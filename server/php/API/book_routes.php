<?php
include_once 'book_dbQueries.php';

//Create new book
$app->post('/api/books' , function() use($app){

	$book_name=isset($request['book_name'])? $request['book_name'] : $app->request->post('book_name');
	$book_review=isset($request['book_review'])? $request['book_review'] : $app->request->post('book_review');
	$owner_id=isset($request['owner_id'])? $request['owner_id'] : $app->request->post('owner_id');
	$latitude=isset($request['latitude'])? $request['latitude'] : $app->request->post('latitude');
	$longitude=isset($request['longitude'])? $request['longitude'] : $app->request->post('longitude');
	$access_token=isset($request['access_token'])? $request['access_token'] : $app->request->post('access_token');

	$auth = AuthenticateUser($owner_id, '', $access_token);
	
	if($auth)
	{
		return InsertNewBook($owner_id , $book_name , $book_review , $latitude , $longitude);
	} else
	{
		//permission denied
		$app->response->setStatus(401);
	}
});

//Update a book
$app->put('/api/books/:id' , function($id) use($app){
	$book_name=isset($request['book_name'])? $request['book_name'] : $app->request->put('book_name');
	$book_review=isset($request['book_review'])? $request['book_review'] : $app->request->put('book_review');
	$owner_id=isset($request['owner_id'])? $request['owner_id'] : $app->request->put('owner_id');
	$latitude=isset($request['latitude'])? $request['latitude'] : $app->request->put('latitude');
	$longitude=isset($request['longitude'])? $request['longitude'] : $app->request->put('longitude');
	$access_token=isset($request['access_token'])? $request['access_token'] : $app->request->put('access_token');

	if(AuthenticateUser($owner_id, '', $access_token))
	{
		return UpdateBook($id , $owner_id='', $book_name='' , $book_review='' , $latitude='' , $longitude='');
	} else
	{
		//permission denied
		$app->response->setStatus(401);
	}

	
});

//Delete a book
$app->delete('/api/books/:id', function($id) use($app){
	$access_token=isset($request['access_token'])? $request['access_token'] : $app->request->delete('access_token');

	if(AuthenticateUser($owner_id, '', $access_token))
	{
		return DeleteBook($id);
	} else
	{
		//permission denied
		$app->response->setStatus(401);
	}
});

//Search a book
$app->get('/api/search/book', function() use($app){
	$name=isset($request['book_name'])? $request['book_name'] : $app->request->get('book_name');
	$lat=isset($request['latitude'])? $request['latitude'] : $app->request->get('latitude');
	$lon=isset($request['longitude'])? $request['longitude'] : $app->request->get('longitude');
	$dis=isset($request['distance'])? $request['distance'] : $app->request->get('distance');

	echo json_Encode (GetBooksByNameWithinRadiusByLatLong($name , $dis , $lat , $lon));
});

?>