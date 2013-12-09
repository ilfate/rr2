<?php

use Game\Map\MapObject;
use Illuminate\Support\Facades\Redirect;

class BattleMapController extends BaseController {

    /** @var  Map */
    protected $mapModel;

    /** @var  BattleMap */
    protected $battleMapModel;

    /** @var  Chunk */
    protected $chunkModel;

    /**
     * Controller constructor
     */
    public function __construct()
    {
        $this->battleMapModel = new BattleMap();
        $this->mapModel       = new Map();
        $this->chunkModel     = new Chunk();
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     *
     * @return Response
     */
	public function getShow($id)
	{
        $battleMap = $this->battleMapModel->findOrFail($id);

        $countChunks = $this->chunkModel->where('battle_map_id', '=', $id)->count();
        return View::make('maps.battleMap')->with(array(
            'battleMap' => $battleMap,
            'countChunks' => $countChunks
        ));
	}

    /**
     * Show the form for creating a new resource.
     *
     * @param $battleMapId
     *
     * @return Response
     */
	public function getFullGenerate($battleMapId)
	{
        $mapData = $this->battleMapModel->where('battle_maps.id', '=', $battleMapId)->join('maps', 'battle_maps.map_id', '=', 'maps.id')->get();

        $mapObject = new MapObject($mapData->toArray()[0]);
        $mapObject->allowToLoadFromDb = false;

        $generator = new Game\Map\Generator($mapObject);
        $generator->generateFullMap($this->chunkModel);

       // $countChunks = $this->chunkModel->where('battle_map_id', '=', $battleMapId)->count();

        return Redirect::to('battle-map/show/' . $battleMapId);
	}
}
