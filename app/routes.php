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
Route::get('example', function(){
  return View::make('example');
});

Route::get('/', function() {
	return View::make('hello');
});

/* LOGIN */
Route::get('login', function() {
	return View::make('loginForm');
});

Route::post('login', function() {
	/* Get the login form data using the 'Input' class */
	$userdata = array('username' => Input::get('username'), 'password' => Input::get('password'));

	if (Input::get('persist') == 'on')
		$isAuth = Auth::attempt($userdata, true);
	else
		$isAuth = Auth::attempt($userdata);

	if ($isAuth) {
		// we are now logged in, go to home
		return Redirect::to('home');
	} else {
		return Redirect::to('login');
	}
});

/* LOGOUT */
Route::get('logout', function() {
	Auth::logout();
	return Redirect::to('login');
});

/* Internal Page */

Route::group(array('before' => 'auth'), function() {
	if (Auth::check()) {
		$privilege = Auth::user() -> level_id;
		if ($privilege == 1) {
			Route::get('home', 'HomePageController@adminHome');
			Route::get('addSeries', 'AdminController@addSeries');
			Route::get('addComic', 'AdminController@addComic');
			Route::get('addBox', 'AdminController@addBox');
			Route::get('series', 'AdminController@manageSeries');
			Route::get('boxes', 'AdminController@manageBoxes');
		} else {
			Route::get('home', 'HomePageController@userHome');
			Route::get('box', 'UserController@box');
		}
	}
	//return URL::action('HomePageController@index');
	// Route::get('home', 'HomePageController@index');
	//return View::make('homePage');
	//return Comic::where('name', 'LIKE', '%città%')
	//  ->get();
});
