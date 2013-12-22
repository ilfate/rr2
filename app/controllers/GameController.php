<?php

class GameController extends BaseController {

    /** @var  Map */
    protected $mapModel;

    /** @var  BattleMap */
    protected $battleMapModel;

    /**
     * Controller constructor
     */
    public function __construct()
    {
        $this->mapModel = new Map();
        $this->battleMapModel = new BattleMap();
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{

        return View::make('game.index')->with(array(
        ));
	}

}
