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
        $startX = 1;
        $startY = 1;
        $rangeX = 11;
        $rangeY = 11;
        $countChunks = $this->chunkModel->where('battle_map_id', '=', $id)->count();
        if ($countChunks > 0 && $countChunks < 1000) {
            $chunks = $this->chunkModel->where('battle_map_id', '=', $id)->get()->toArray();
            $mapData = $this->battleMapModel->getBattleMapData($id);
            $mapObject = new MapObject($mapData);
            $mapObject->setChunks($chunks);
            $mapObject->allowToLoadFromDb = false;

            $list = [];

            for ($i = $startY; $i < $rangeY; $i++) {
                for ($i2 = $startX; $i2 < $rangeX; $i2++) {
                    $list[] = [$i2, $i];
                }
            }
            $grid = $mapObject->getChunks($list);
        } else {
            $mapData = $this->battleMapModel->findOrFail($id);
            $grid    = array();
        }
        return View::make('maps.battleMap')->with(array(
            'battleMap'   => $mapData,
            'countChunks' => $countChunks,
            'grid'        => $grid,
            'startX'      => $startX,
            'startY'      => $startY,
            'rangeX'      => $rangeX,
            'rangeY'      => $rangeY
        ));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param $mapId
     *
     * @return Response
     */
    public function postCreate($mapId)
    {
        // by this load we are checking is this map exists
        $map = $this->mapModel->findOrFail($mapId);
        $this->battleMapModel->create(array('map_id' => $map->id));
        return Redirect::to('map');
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
        $mapData = $this->battleMapModel->getBattleMapData($battleMapId);

        $mapObject = new MapObject($mapData);
        $mapObject->allowToLoadFromDb = false;

        $generator = new Game\Map\Generator($mapObject);
        $generator->generateFullMap($this->chunkModel);

       // $countChunks = $this->chunkModel->where('battle_map_id', '=', $battleMapId)->count();

        return Redirect::to('battle-map/show/' . $battleMapId);
	}
}
