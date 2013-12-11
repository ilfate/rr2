<?php

class BattleMap extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

    public function getBattleMapData($battleMapId)
    {
        $maps = $this->where('battle_maps.id', '=', $battleMapId)->join('maps', 'battle_maps.map_id', '=', 'maps.id')->get();
        return $maps->toArray()[0];
    }

    public function getBattleMapWithWizards()
    {
        return $this
            ->where('active', '=', 1)
            ->join('battle_wizards', 'battle_maps.id', '=', 'battle_wizards.battle_map_id')
            ->get()
            ->toArray();
    }
}
