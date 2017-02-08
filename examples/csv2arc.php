#!/usr/bin/env php
<?php

/*
Sample code to parse a pre-formatted csv file into
a GeoJSON feature collection.
This script is intended as a starting point only.
You will need to modify depending on your csv format.
*/

require_once '../Arc.php';


$geojson = (object) array('type'=> 'FeatureCollection', 'features'=> array());

$csv = array_map('str_getcsv', file('routes.csv'));

function trim_value(&$value) { 
    $value = trim($value, "'\" \t\n\r\0\x0B"); 
}


$headers = array_slice($csv, 0, 1)[0];
array_walk($headers, 'trim_value');
$properties_names = array_slice($headers, -2);
$rows = array_slice($csv, 1);

foreach ($rows as $row) {
    array_walk($row, 'trim_value');
    $coords = array_slice($row, 0, 4);
    $start_x = floatval(trim($coords[0]));
    $start_y = floatval(trim($coords[1]));
    $end_x = floatval(trim($coords[2]));
    $end_y = floatval(trim($coords[3]));
    $start = (object) array('x'=> $start_x, 'y'=> $start_y);
    $end = (object) array('x'=> $end_x, 'y'=> $end_y);
    
    $attributes = array_slice($row, -2);
    $properties = array();
    
    foreach ($properties_names as $k => $v) {
        //$att_value = trim($attributes[$k], '\'" \t\n\r\0\x0B');
        $att_value = $attributes[$k];
        if (is_numeric($att_value)) {
            // if int
               $att_value = floatval($att_value);
        }
        $properties[$v] = $att_value;
    }
        // now actually form up the GreatCircle object
    $gc = new Arc\GreatCircle($start, $end, $properties);

    // build out a linestring with 10 intermediate points
    $line = $gc->Arc(10);
    
    // add this line to the json features
    $geojson->features[] = $line->json();
}

echo json_encode($geojson), PHP_EOL;
