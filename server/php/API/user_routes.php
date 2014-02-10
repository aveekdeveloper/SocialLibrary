<?php
include_once 'user_dbQueries.php';
 
function unique_md5() {
    mt_srand(microtime(true)*100000 + memory_get_usage(true));
    return md5(uniqid(mt_rand(), true));
}

function SetErrorMessage($error)
{
	$array = array();
	$array['error']=$error;
	return $array; 
}

function GenerateAccessToken($key)
{
	return crypt($key , unique_md5());
}

function IsEmailRegistered($email)
{
	$user = GetUserByEmail($email);
	//If user with email is in database
	if(!empty($user))
	{
		return True;
	} else
	{
		return False;
	}
}

function RegisterUser($email , $password , $name) {
	
	//check if Email is valid
	if(!filter_var($email, FILTER_VALIDATE_EMAIL))
	{
		return SetErrorMessage("Invalid email provided for registration ".$email.$name.$password);
	}
	//check if name provided
	if($name == '')
	{
		return SetErrorMessage("Name not provided for registration");
	}
	//check if password provided
	if($password == '')
	{
		return SetErrorMessage("Password not provided for registration");
	}
	
	//Password hash
	$password_hash =  hash('sha1' , $password);
	//Access token
	$access_token = GenerateAccessToken($password);
	//When the user first registers he is inactive
	$is_active = False; 
	
	if(GetUserByEmail($email))
	{
		return(SetErrorMessage("User with email id already registered"));
	}
	
	$result = InsertNewUser($email,$password_hash,$name,$is_active,$access_token);
	
	if($result == 0 )
	{
		return(SetErrorMessage("User with email id already registered"));
	} else
	{
		//TODO : Send the user's access_token to his email to verify his password
		return(GetUserById($result));
	}
	
}

function LoginUser($email , $password) {
	$password_hash =  hash('sha1' , $password);
	$user = GetUserByEmail($email);
	
	if(empty($user))
	{
		return SetErrorMessage("User with email not registered");
	}
	
	if($user['is_active'] ==0)
	{
		//Access token will only be given after one time activation
		$user['access_token'] = '';
	}
	if(strcmp($user['password_hash'] ,$password_hash)==0)
	{
		//Do not give away the password hash
		unset($user['password_hash']);
		return $user;
	} else
	{
		return SetErrorMessage("Invalid Password");
	}
}

function AuthenticateUser($id ='', $email ='', $access_token)
{
	$user = NULL;
	if($id != '')
	{
		$user = GetUserById($id);
	}
	if($email != '')
	{
		$user = GetUserByEmail($email);
	}
	
	if($user == NULL)
	{
		//return SetErrorMessage("The user could not be authenticated");
		return False;
	}
	
	if(strcmp($user['access_token'] , $access_token)==0)
	{
		if($user['is_active'] == 0)
		{
			//Since the user activated itself set him/her to be active
			UpdateUser($user['id'] ,'','','', 1);
		}
		return True;
	}
	
	return False;
}

function RegenerateAccessToken($email , $password)
{
	$user = LoginUser($email , $password);
	
	if($user['error'])
	{
		return $user;
	}
	
	//Generate new access token
	$access_token = GenerateAccessToken($password);
	UpdateUser($user['id'] ,'','','', '' , $access_token);
	//TODO :Send email to user with the new access token
	
	return SetErrorMessage("Access token successfully regenerated");
}

$app->response->headers->set('Content-Type', 'application/json');

//Get all users
$app->get('/api/users',function(){
		
	});

//View a particular user
$app->get('/api/users/:idOrEmail' , function($idOrEmail) use($app){
		//Try to get user by ID
		$user = GetUserById($idOrEmail);
		//If no user by that ID is obtained try getting a user by Email
		$user = !empty($user)? $user : GetUserByEmail($idOrEmail);
		
		//Remove access token
		unset($user['access_token']);
		//Remove password hash
		unset($user['password_hash']);
		if(empty($user))
		{
			$app->response->setStatus(400);
		}
		echo json_encode((array)$user);
	});
	
//An alternative login approach
$app->post('/api/users/:email' , function($email) use($app){
		
		$request = (array)$app->request()->getBody();

		//Give preference to payload data
		$password = isset($request['password'])? $request['password'] : $app->request->post('password');
		
		$user = LoginUser($email , $password);
		
		if(isset($user['error']))
		{
			$app->response->setStatus(401);
		}
		echo json_encode($user);
	});

//User Registration
//Includes support for JSON as well as normal form POST
$app->post('/api/users' , function() use ($app)	{
		
		$request = (array)$app->request()->getBody();
		
		//Give preference to Payload data
		$email = isset($request['email'])? $request['email'] : $app->request->post('email');
		$password = isset($request['password'])? $request['password'] : $app->request->post('email');
		$name = isset($request['name'])? $request['name'] : $app->request->post('name');
		
		//Insert into table and set the user as inactive
		$user = RegisterUser($email , $password , $name);
		if(isset($user['error']))
		{
			$app->response->setStatus(401);
		}
		echo json_encode($user);
	});
	
//User Login
$app->post('/api/login' , function() use ($app)	{

		$request = (array)$app->request()->getBody();

		//Give preference to payload data
		$email = isset($request['email'])? $request['email'] : $app->request->post('email');
		$password = isset($request['password'])? $request['password'] : $app->request->post('password');

		echo json_encode(LoginUser($email , $password));
	});
	
//User Authenticate
$app->get('/api/authenticate',function() use ($app){
	
	$email = $app->request->get('email');
	$id = $app->request->get('id');
	$access_token = $app->request->get('access_token');

	echo json_encode(AuthenticateUser($id , $email , $access_token));
});

//User Update
$app->put('/api/user/:id' , function(){
	$email = $app->request->put('email');
	$name = $app->request->put('name');
	$password = $app->request->put('password');
	$access_token = $app->request->put('access_token');
	
	if($password != '')
	{
	//Generate new access token
	}
	
	if($email != '')
	{
	//User is changing email , so account needs to be verified again , generate new access token , deactivate the account till reactivation
	}
} )
?>