<?php

class BattleLog extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

    public function getLastLog($userId, $battleMapId)
    {
        return $this
            ->where('user_id', '=', $userId)
            ->where('battle_map_id', '=', $battleMapId)
            ->where('is_watched', '=', false)
            ->orderBy('time')
            ->limit(1)
            ->get()
            ->toArray();
    }
}
