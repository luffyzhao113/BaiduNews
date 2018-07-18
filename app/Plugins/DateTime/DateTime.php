<?php
/**
 * Created by PhpStorm.
 * User: luffyzhao
 * Date: 2018/7/18
 * Time: 0:05
 */

namespace App\Plugins\DateTime;


use Carbon\Carbon;

class DateTime extends Carbon
{
    public static function forString($string){
        if(preg_match('/((\d+)((分钟前)|(小时前)))|(([0-9]{4})年([0-9]{2})月([0-9]{2})日 ([0-9]{2}):([0-9]{2}))/', $string, $matches)){
            if($matches[3] === '小时前'){
                return Carbon::now()->subHour($matches[2]);
            }else if($matches[3] === '分钟前'){
                return Carbon::now()->subMinute($matches[2]);
            }else{
                return Carbon::create($matches[7], $matches[8], $matches[9], $matches[10], $matches[11]);
            }
        }else{
            return Carbon::createFromTimestamp($string);
        }
    }
}