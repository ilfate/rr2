<?php

use Illuminate\Support\Facades\Redirect;

class HomeController extends BaseController {

    /**
     * Home page
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
	{
        $wizards = DB::table('wizards')->where('user_id', Auth::user()->id)->get();

        // if we do not have any wizard, just create one
        if (!$wizards) {
            return Redirect::to('wizard/create-wizard');
        }
		return View::make('home.index')->with('wizards', $wizards);
	}

}