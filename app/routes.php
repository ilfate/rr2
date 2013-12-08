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

Route::get('/', array('as' => 'main', 'uses' => 'MainController@index'));

Route::get('logout', array('as' => 'login.logout', 'uses' => 'LoginController@logout'));

Route::group(array('before' => 'un_auth'), function()
{
    Route::get('login', array('as' => 'login.index', 'uses' => 'LoginController@index'));
    Route::get('register', array('as' => 'login.register', 'uses' => 'LoginController@register'));
    Route::post('login', array('uses' => 'LoginController@login'));
    Route::post('register', array('uses' => 'LoginController@store'));
});

Route::group(array('before' => 'user.auth'), function()
{
    Route::get('dashboard', function()
    {
        return View::make('login.dashboard');
    });

    Route::resource('roles', 'RolesController');

    Route::controller('home', 'HomeController');
    Route::controller('wizard', 'WizardController');
    Route::controller('map', 'MapController');
    Route::controller('battle-map', 'BattleMapController');

});

Route::filter('user.auth', function()
{
    if (Auth::guest()) {
        return Redirect::to('login');
    }
});

Route::filter('un_auth', function()
{
    if (!Auth::guest()) {
        Auth::logout();
    }
});