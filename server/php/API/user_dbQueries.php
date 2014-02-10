<?php

include_once '_database_includes.php';
	
function InsertNewUser($email,$password_hash,$name,$is_active,$access_token)
{
	global $user_table_name;
	
	DB::insert( $user_table_name , array(
		 'email'=>$email,
		 'password_hash'=>$password_hash,
		 'name'=>$name,
		 'is_active'=>$is_active,
		 'access_token'=>$access_token,
		 'last_login_time'=>date("Y-m-d H:i:s"),
		));
		
	return DB::insertId();
}

function UpdateUser($id,$email = '',$password_hash ='',$name ='',$is_active = '',$access_token = '',$last_login_time='')
{
	global $user_table_name;
	
	$array = array();
	
	if($email != '')
	{
		$array['email']=$email;
	}
	
	if($password_hash != '')
	{
		$array['password_hash']=$password_hash;
	}
	
	if($name != '')
	{
		$array['name']=$name;
	}
	
	if($is_active != '')
	{
		$array['is_active']=$is_active;
	}
	
	if($last_login_time != '')
	{
		$array['last_login_time']=date("Y-m-d H:i:s");
	}
	
	DB::update( $user_table_name , $array , "id = %i" , $id);
		
	return DB::affectedRows();
}

function GetUserByEmail($email)
{
	global $user_table_name;
	return DB::queryFirstRow("SELECT * FROM $user_table_name WHERE email=%s",$email);
}

function GetUserById($id)
{
	global $user_table_name;
	
	return DB::queryFirstRow("SELECT * FROM $user_table_name WHERE id=%i",$id);
}

function DeleteUser($id)
{
	global $user_table_name;
	
	DB::delete($user_table_name, "id=%i", $id);
}
?>