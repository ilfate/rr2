<?php

use Illuminate\Support\Facades\Redirect;

class HomeController extends BaseController {

	/*
	|--------------------------------------------------------------------------
	| Default Home Controller
	|--------------------------------------------------------------------------
	|
	| You may wish to use controllers instead of, or in addition to, Closure
	| based routes. That's great! Here is an example controller method to
	| get you started. To route to this controller, just add the route:
	|
	|	Route::get('/', 'HomeController@showWelcome');
	|
	*/

	public function getIndex()
	{
        $wizards = DB::table('wizards')->where('user_id', Auth::user()->id)->get();
        if (!$wizards) {
            return Redirect::to('home/create-wizard');
        }
		return View::make('home.index')->with('wizards', $wizards);
	}

    public function getCreateWizard()
    {
        $wizardsConfig = Config::get('wizards.wizards');
        return View::make('home.createWizard')->with('wizardsConfig', $wizardsConfig);
    }

    public function postCreateWizard()
    {
        return Redirect::to('home');
    }

}