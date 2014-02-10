<?php
require_once 'ThirdPartyLibraries/meekrodb.2.2.class.php';

DB::$user = 'root';
DB::$password = '';
DB::$dbName = 'restslim';
DB::$error_handler = 'database_error_handler';

$user_table_name = 'user_authentication';
$book_table_name = 'book_library';

/*
function database_error_handler()
{
	return -1;
}
*/

?>