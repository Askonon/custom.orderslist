<?php

namespace OrdersList;

use DateTime;
class Date {

    private array $arHolidays;
    
    public function __construct() {
        $calendar = simplexml_load_file('http://xmlcalendar.ru/data/ru/'.date('Y').'/calendar.xml');
        $calendar = $calendar->days->day;
        foreach($calendar as $day){
            $d = (array)$day->attributes()->d;
            $d = $d[0];
            $d = date('Y').'-'.substr($d, 0, 2).'-'.substr($d, 3, 2);
            if($day->attributes()->t == 1) $this->arHolidays[$d] = true;
        }
    }

    function deliveryDate(DateTime $date):DateTime {
        
        $correctDate = 0;
        while ($correctDate != 5) {
            $date->modify('+1 day');
            $numberDay = date("w", strtotime($date->format("Y-m-d")));
            if ($numberDay != 0 && $numberDay != 6 && is_null($this->arHolidays[$date->format('Y-m-d')])) {
                $correctDate += 1;
            }
        }
        return $date;
    }
}