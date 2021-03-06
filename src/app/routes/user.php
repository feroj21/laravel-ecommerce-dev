<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the Closure to execute when that URI is requested.
  |
 */

Route::get('/', function() {
  return "Home Page";
});

/*
 * Please use all routes as like as this route. So that we can change the route from single point
 */
// Route::get('user/create', ['as' => 'login', 'uses' => 'LoginController@login']);

Route::group(array('prefix' => 'api/v1'),  function(){
	Route::resource('user', 'UserController');

	// custom method for user login
	Route::post('user/login', 'UserController@login');
});

