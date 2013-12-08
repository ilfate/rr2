<?php

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
        //$battleMap = $this->battleMapModel->findOrFail($battleMapId);

        $mapData = $this->battleMapModel->where('battle_maps.id', '=', $battleMapId)->join('maps', 'battle_maps.map_id', '=', 'maps.id')->select(
            'maps.width',
            'maps.height',
            'maps.chunk_size'
        )->get();

        $generator = new Game\Map\Generator($mapData->toArray()[0]);

       // $countChunks = $this->chunkModel->where('battle_map_id', '=', $battleMapId)->count();

        return View::make('maps.create');
	}
}
