<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../src/Controllers/HomeController.php';
require_once __DIR__ . '/../src/Controllers/PageController.php';
require_once __DIR__ . '/../src/Controllers/ReviewController.php';
require_once __DIR__ . '/../src/Controllers/AgentController.php';
require_once __DIR__ . '/../src/Controllers/NeighborhoodController.php';
require_once __DIR__ . '/../src/helpers/View.php';

// Create Router instance
$router = new \Bramus\Router\Router();

// Define routes
$router->get('/', 'HomeController@index');
$router->get('/(\w+)', 'PageController@show');
$router->get('/api/reviews', 'ReviewController@getReviews');
$router->get('/api/agents', 'AgentController@getAgents');
$router->get('/api/neighborhoods', 'NeighborhoodController@getNeighborhoods');

// Run it!
$router->run();