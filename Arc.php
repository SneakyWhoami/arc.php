<?php

namespace Arc;

//require_once 'Arc.php';
require_once 'Coord.php';
require_once 'GreatCircle.php';
require_once 'LineString.php';

class Arc {
    
    public $properties;
    public $geometries;
    
    public function __construct($properties=array()) {
        $this->properties = $properties;
        $this->geometries = array();
    }
    
    public function json() {
        if (count($this->geometries) <= 0) {
            return array('geometry'=> array( 'type' => 'LineString', 'coordinates'=> null ),
            'type'=> 'Feature', 'properties'=> (object) $this->properties
            );
        } elseif (count($this->geometries) == 1) {
            return array('geometry'=> array( 'type' => 'LineString', 'coordinates'=> $this->geometries[0]->coords ),
            'type'=> 'Feature', 'properties'=> (object) $this->properties
            );
        } else {
            $multiline = array();
            for ($i = 0; $i < count($this->geometries); $i++) {
                $multiline[] = $this->geometries[$i]->coords;
            }
            return array('geometry'=> array( 'type'=> 'MultiLineString', 'coordinates'=> $multiline ),
            'type'=> 'Feature', 'properties'=> (object) $this->properties
            );
        }
    }
    
    // TODO - output proper multilinestring
    // NOTE the above comment is inherited from the original code. i know nothing about multilinestring
    function wkt() {
        $wkt_string = '';
        $wkt = 'LINESTRING(';
        for ($i = 0; $i < count($this->geometries); $i++) {
            if (count($this->geometries[i]['coords']) === 0) {
                return 'LINESTRING(empty)';
            } else {
                $coords = $this->geometries[i]['coords'];
                foreach ($coords as $c) {
                    $wkt .= $c[0] . ' ' . $c[1] . ',';
                }
                $wkt_string .= substr($wkt, 0, -1) . ')';
            }
        }
        return $wkt_string;
    }
    
}
