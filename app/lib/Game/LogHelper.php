<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2012
 */
/**
 * Created by PhpStorm.
 * User: ilfate
 * Date: 12/11/13
 * Time: 10:48 PM
 */

namespace Game;

class LogHelper {

    /** @var \BattleWizard */
    protected $battleWizardModel;
    /** @var \Wizard */
    protected $wizardModel;
    /** @var \BattleMap */
    protected $battleMapModel;
    /** @var \Chunk */
    protected $chunkModel;
    /** @var \Monster */
    protected $monsterModel;
    /** @var \BattleLog */
    protected $battleLogModel;

    protected $userId;
    protected $battleMapId;



    public function __construct($userId, $battleMapId)
    {
        $this->userId = $userId;
        $this->battleMapId = $battleMapId;

        $this->battleWizardModel = new \BattleWizard();
        $this->wizardModel       = new \Wizard();
        $this->battleMapModel    = new \BattleMap();
        $this->chunkModel        = new \Chunk();
        $this->monsterModel      = new \Monster();
        $this->battleLogModel    = new \BattleLog();
    }

    public function run()
    {
        $lastLog = $this->battleLogModel->getLastLog($this->userId, $this->battleMapId);
        if (empty($lastLog[0]['data'])) {
            echo "\n" . ' WE DO NOT HAVE LOG FOR THIS USER AND MAP' . "\n";
            return;
        }
        $this->analyzeLog($lastLog[0]['data']);
    }

    public function analyzeLog($log)
    {
        // hardcoded data from controller
        $gameData = [
            'screen_size' => 3,
            'cell_size' => 64,
            'visionRadius' => 3
        ];
        $screenSize = $gameData['visionRadius'] * 2 + 1;
        $visionRadius = $gameData['visionRadius'];
        $log = json_decode($log, true);

        $map = [];
        $units = [];
        $middlePoint = [0, 0];

        foreach ($log as $time => $events) {
            foreach ($events as $event) {
                list($who, $code, $data) = $event;
                switch ($code) {
                    case 'map':
                        $i = 0;
                        $mapRaw = explode('|', $data[0]);
                        for ($x = -$visionRadius; $x <= $visionRadius; $x++) {
                            for ($y = -$visionRadius; $y <= $visionRadius; $y++) {
                                $map[$x][$y] = $mapRaw[$i];
                                $i++;
                            }
                        }
                    break;
                    case 'vm':
                        switch ($data[1]) {
                            case 0:
                                $middlePoint[1] -= $data[0];
                            break;
                            case 1:
                                $middlePoint[0] += $data[0];
                            break;
                            case 2:
                                $middlePoint[1] += $data[0];
                            break;
                            case 3:
                                $middlePoint[0] -= $data[0];
                            break;
                        }
                    break;
                    case 'c':
                        if (!isset($map[$middlePoint[0] + $data[0]])) {
                            $map[$middlePoint[0] + $data[0]] = [];
                        }
                        $map[$middlePoint[0] + $data[0]][$middlePoint[1] + $data[1]] = $data[2];
                    break;
                    default:
                        echo "\n" . ' CODE "' . $code . '" is unknown for log Helper' . "\n";
                    break;
                }
            }
        }
        var_dump($map);
    }

    public function saveLog($log, $battleMapId, $time)
    {
        foreach ($log as $userId => $log) {
            $data = [
                'user_id' => $userId,
                'battle_map_id' => $battleMapId,
                'time' => $time,
                'data' => json_encode($log)
            ];
            $this->battleLogModel->create($data);
        }
    }
}