<?php

namespace Arc;

require_once 'Arc.php';
require_once 'Coord.php';
require_once 'GreatCircle.php';
// require_once 'LineString.php';

class LineString {
    
    public $coords;
    public $length;
    
    public function __construct() {
        $this->coords = array();
        $this->length = 0;
    }
    
    public function move_to($coord) {
        $this->length++;
        $this->coords[] = $coord;
    }
    
}
