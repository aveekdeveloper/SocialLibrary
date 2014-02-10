<?php
/**
 * Step 1: Require the Slim Framework
 *
 * If you are not using Composer, you need to require the
 * Slim Framework and register its PSR-0 autoloader.
 *
 * If you are using Composer, you can skip this step.
 */
require_once 'API/ThirdPartyLibraries/Slim/Slim.php';
require_once 'API/ThirdPartyLibraries/meekrodb.2.2.class.php';

\Slim\Slim::registerAutoloader();

/**
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 */
$app = new \Slim\Slim();
$app->add(new \Slim\Middleware\ContentTypes());

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 */
include_once 'API/user_routes.php';
include_once 'API/book_routes.php';

// GET route
$app->get(
    '/',    function() use($app){
		$app->response->headers->set('Content-Type', 'text/html');
		//$app->render('landingpage.html');
		$app->render('bootstrap_dashboard.htm');
		//$app->render('new_homepage.html');
    }
);

$app->run();
