<?php
/**
 * ILFATE PHP ENGINE
 * @autor Ilya Rubinchik ilfate@gmail.com
 * 2013
 */
namespace Game;

class Math {

    public static function getChance($chance)
    {
        if($chance >= 100) return true;
        if($chance <= 0) return false;
        if( $chance >= mt_rand(0, 10000)/100 )
        {
            return true;
        }
    }


    public static function chanses($chanses = array())
    {
        if(!$chanses) return false;
        $rand = mt_rand(0, 10000)/100;
        $current = 0;
        foreach ($chanses as $key => $chance) {
            $current += $chance;
            if($current >= $rand) {
                return $key;
            }
        }
        return false;
    }

    public static function customChance($chances = array())
    {
        if(!$chances) return false;
        $sum = array_sum($chances);
        $rand = mt_rand(0, $sum * 100) / 100;
        $current = 0;
        foreach ($chances as $key => $chanceValue) {
            $current += $chanceValue;
            if ($rand <= $current) {
                return $key;
            }
        }
        //Logger::dump('some thing is working wrong in Math::customChance', 'file');
        Logger::dump('some thing is working wrong in Math::customChance');
        return false;
    }
}