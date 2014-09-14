<?php

class TdController extends BaseController
{
    /**
     * @return \Illuminate\View\View
     */
    public function index()
	{
		return View::make('td');
	}

}