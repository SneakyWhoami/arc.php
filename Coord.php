<?php

namespace Arc;

require_once 'Arc.php';
// require_once 'Coord.php';
require_once 'GreatCircle.php';
require_once 'LineString.php';

class Coord {
    
    public $lat;
    public $lon;
    public $x;
    public $y;
    
    public function __construct($lon,$lat) {
        $this->lon = $lon;
        $this->lat = $lat;
        $this->x = deg2rad($lon);
        $this->y = deg2rad($lat);
    }
    
    public function view() {
        return substr($this->lon, 0, 4) . ',' . substr($this->lat, 0, 4);
    }
    
    public function antipode() {
        $anti_lat = -1 * $this->lat;
        $anti_lon = ($this->lon < 0) ? 180 + $this->lon : (180 - $this->lon) * -1;
        return new Coord($anti_lon, $anti_lat);
        
    }
}
