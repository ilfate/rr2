<?php

class MapController extends BaseController {

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
        $maps = $this->mapModel->all();
        $bMaps = $this->battleMapModel->all();
        $battleMaps = array();
        foreach ($bMaps as $battleMap) {
            if (!isset($battleMaps[$battleMap->map_id])) {
                $battleMaps[$battleMap->map_id] = array();
            }
            $battleMaps[$battleMap->map_id][] = $battleMap;
        }
        return View::make('maps.index')->with(array(
            'maps' => $maps,
            'battleMaps' => $battleMaps,
        ));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
        $this->addMapsConfigs();
        return View::make('maps.create');
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postCreate()
	{
        $input = Input::all();

        $validation = Validator::make($input, $this->getRules());

        if (!empty($input['bioms'])) {
            $input['bioms'] = implode('|', $input['bioms']);
        }

        if ($validation->passes())
        {
            $this->mapModel->create($input);
            return Redirect::to('map');
        }

        return Redirect::to('map/create')
            ->withInput()
            ->withErrors($validation)
            ->with('message', 'There were validation errors.');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function getEdit($id)
	{
        $map = $this->mapModel->findOrFail($id);
        $this->addMapsConfigs();
        return View::make('maps.edit')->with(array(
                'map' => $map
            ));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param $id
     *
     * @return Response
     */
    public function postSave($id)
    {
        $map = $this->mapModel->findOrFail($id);
        $input      = array_except(Input::all(), '_token');
        $validation = Validator::make($input, $this->getRules(false));

        if (!empty($input['bioms'])) {
            $input['bioms'] = implode('|', $input['bioms']);
        }

        if ($validation->passes())
        {
            $map->update($input);
            return Redirect::to('map');
        }

        return Redirect::to('map/edit/' . $map->id)
            ->withInput()
            ->withErrors($validation)
            ->with('message', 'There were validation errors.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function postDelete($id)
    {
        $map = $this->mapModel->findOrFail($id);
        $map->delete();
        return Redirect::to('map');
    }

	protected function addMapsConfigs()
    {
        $possibleTypes  = Config::get('maps.types');
        $possibleSpawns = Config::get('maps.spawns');
        $possibleBioms = Config::get('maps.biomTypes');
        View::share('possibleTypes', array_combine($possibleTypes, $possibleTypes));
        View::share('possibleSpawns', array_combine($possibleSpawns, $possibleSpawns));
        View::share('possibleBioms', array_combine($possibleBioms, $possibleBioms));
    }

    protected function getRules($uniqueName = true)
    {
        Validator::extend('arrayIn', function($attribute, $value, $parameters)
        {
            foreach ($value as $val) {
                if (!in_array($val, $parameters)) {
                    return false;
                }
            }
            return true;
        });
        return array(
            'name' => 'required|alpha_num' . ($uniqueName ? '|unique:maps,name' : ''),
            'width' => 'required|numeric',
            'height' => 'required|numeric',
            'chunk_size' => 'numeric',
            'max_players' => 'numeric',
            'bioms' => 'arrayIn:' . implode(',', Config::get('maps.biomTypes')),
            'type' => 'required|in:' . implode(',', Config::get('maps.types')),
            'spawn' => 'required|in:' . implode(',', Config::get('maps.spawns')),
        );
    }

}
