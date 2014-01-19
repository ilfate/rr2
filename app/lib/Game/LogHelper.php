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
        $newCells = [];
        $actions = [];

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
                        foreach ($newCells as $cellData) {
                            if (!isset($map[$middlePoint[0] + $cellData[0]])) {
                                $map[$middlePoint[0] + $cellData[0]] = [];
                            }
                            $map[$middlePoint[0] + $cellData[0]][$middlePoint[1] + $cellData[1]] = $cellData[2];
                        }
                        $newCells = [];
                    break;
                    case 'c':
                        $newCells[] = $data;
                    break;
                    case 'init':
                        $units[$who] = $data;
                    break;
                    case 'new':
                        $units[$who] = $data;
                    break;
                    case 'mf':
                        if (!isset($units[$who])) {
                            echo "\n" . ' We are trying to move unexisting unit!' . "\n";
                            die;
                        }
                        if (!isset($actions[$who])) {
                            $actions[$who] = [];
                        }
                        $actions[$who][] = [$code, $data];
                    break;
                    default:
                        echo "\n" . ' CODE "' . $code . '" is unknown for log Helper' . "\n";
                    break;
                }
            }
        }
        $this->printMap($map);
    }

    protected function printMap($map)
    {
        $rowDelimetr = "-----";
        $colDelimetr = "|";
        $width = count($map);

        $reverseMap = [];
        foreach ($map as $x => $col) {
            foreach ($col as $y => $cell) {
                if (!isset($reverseMap[$y])) {
                    $reverseMap[$y] = [];
                }
                $reverseMap[$y][$x] = $cell;
            }
        }

        foreach ($reverseMap as $y => $row) {
            for ($i = 0; $i < $width; $i++) {
                echo $rowDelimetr;
            } echo "\n";
            echo implode(" | ", $row);
            echo "\n";
        }
        for ($i = 0; $i < $width; $i++) {
            echo $rowDelimetr;
        } echo "\n";
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