<?php

class BattleWizard extends Eloquent {
	protected $guarded = array();

	public static $rules = array();

    public function getActiveWizard($userId)
    {
        return $this
            ->where('user_id', '=', $userId)
            ->get()
            ->toArray();
    }
}
