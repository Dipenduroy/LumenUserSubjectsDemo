<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->group(['middleware' => 'auth','prefix'=>'api'], function () use ($router) {
    $router->get('user-subjects/user/{userid}', [
        'as' => 'user-subjects', 'uses' => 'UsersubjectsController@index'
    ]);
    $router->post('user-subjects/user/{userid}', [
        'as' => 'user-subjects', 'uses' => 'UsersubjectsController@store'
    ]);
    $router->delete('user-subjects/user/{userid}/id/{id}', [
        'as' => 'user-subjects', 'uses' => 'UsersubjectsController@destroy'
    ]);
    $router->patch('user-subjects/user/{userid}/id/{id}', [
        'as' => 'user-subjects', 'uses' => 'UsersubjectsController@update'
    ]);
});
