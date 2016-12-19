<?php

use Respect\Validation\Validator as v;

session_start();

require __DIR__ . '/../vendor/autoload.php';

$app = new \Slim\App([
	'settings' => [
		'displayErrorDetails' => true,
		'db' => [
			'driver' 	=> 'mysql',
			'host' 		=> 'localhost',
			'database' 	=> 'eshop',
			'username' 	=> 'root',
			'password' 	=> '',
			'charset' 	=> 'utf8',
			'collation'	=> 'utf8_unicode_ci',
			'prefix'	=> '',
		]
	],
	
]);

$container = $app->getContainer();

// setup illuminate (Model generator)
$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container['validator'] = function ($container) {
	return new App\Validation\Validator;
};

$container['db'] = function ($container) use ($capsule){
	return $capsule;
};

// add views to the application
$container['view'] = function($container){
	$view = new \Slim\Views\Twig(__DIR__ . '/../resources/views', [
		'cache' => false,
	]);

	$view->addExtension(new Slim\Views\TwigExtension(
		$container->router, 
		$container->request->getUri()
	));

	return $view;
};

$container['HomeController'] = function($container){
	return new \App\Controllers\HomeController($container);
};

$container['AuthController'] = function($container){
	return new \App\Controllers\Auth\AuthController($container);
};

// add Slim CSRF
$container['csrf'] = function($container){
	return new \Slim\Csrf\Guard;
};

// give back errors
$app->add(new \App\Middelware\ValidationErrorsMiddelware($container));

// give back the old input
$app->add(new \App\Middelware\OldInputMiddelware($container));

// run the crsf check
$app->add($container->csrf);

// setup custom rules
v::with('App\\Validation\\Rules\\');

require  __DIR__ . '/../app/routes.php';