<?php

use Illuminate\Support\Facades\Redirect;

class HomeController extends BaseController {

    protected $battleMapModel;

    public function __construct()
    {
        $this->battleMapModel = new BattleMap();
    }

    /**
     * Home page
     *
     * @return \Illuminate\View\View
     */
    public function getIndex()
	{
        $wizards = DB::table('wizards')->where('user_id', Auth::user()->id)->get();
        $availableBattleMaps = $this->battleMapModel->where('active', '=', 1)->get();

        // if we do not have any wizard, just create one
        if (!$wizards) {
            return Redirect::to('wizard/create-wizard');
        }
		return View::make('home.index')->with(array(
            'wizards'             => $wizards,
            'availableBattleMaps' => $availableBattleMaps
        ));
	}

}