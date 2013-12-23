<?php

use Illuminate\Support\Facades\Redirect;

class GameController extends BaseController {

    /** @var  Map */
    protected $mapModel;

    /** @var  BattleWizard */
    protected $battleWizardModel;

    /** @var  BattleMap */
    protected $battleMapModel;

    /** @var  BattleLog */
    protected $battleLogModel;

    /**
     * Controller constructor
     */
    public function __construct()
    {
        $this->mapModel = new Map();
        $this->battleMapModel = new BattleMap();
        $this->battleLogModel = new BattleLog();
        $this->battleWizardModel = new BattleWizard();
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
        $userId = Auth::user()->id;
        $activeWizard = $this->battleWizardModel->getActiveWizard($userId);
        if (count($activeWizard) > 1) {
            // we have problem here
            return Redirect::to('home');
        }
        if (!$activeWizard) {
            return Redirect::to('home')->with('message', 'There is no active Wizard for you');
        }
        $activeWizard = $activeWizard[0];
        $lastLog = $this->battleLogModel->getLastLog($userId, $activeWizard['battle_map_id']);
        $gameData = [
            'screen_size' => 3,
            'cell_size' => 64,
            'visionRadius' => 3
        ];
        if ($lastLog) {
            $gameLog = $lastLog[0]['data'];
        }
        return View::make('game.index')->with(array(
            'gameData' => json_encode($gameData),
//            'gameLog' => '{[' . substr(substr($gameLog, 1), 0, strlen($gameLog)-2) . ']}',
            'gameLog' => $gameLog,
        ));
	}

}
