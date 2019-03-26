<?php

namespace NCSC;

class DateUtil Extends NCSCBase {
    
    /**
     * initialize class
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    public static function getDate($format,$dateString=null):int {
        $f3 = \Base::instance();
        if (empty($date)) {
            $date = new \DateTime("now");
        }
        else {
            $date = new \DateTime($dateString);
        }
        if (empty($format)) {
            return $date->format('Y-m-d H:i:s');
        }
        else {
            return $date->format($format);
        };
    }
}