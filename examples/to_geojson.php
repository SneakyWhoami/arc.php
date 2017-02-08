#!/usr/bin/env php
<?php

require_once '../Arc.php';

$geojson = (object) array( 'type' => 'FeatureCollection');
$geojson->features = [];

              // first time using this notation.. you can tell i haven't used php since 5.3
              // is it easier to read? i'm not sure! cool, nonetheless.
$routes = [
    [new Class{public $x = -122; public $y = 48;}, new Class{public $x = -77; public $y = 39;}, ['name' => 'Seattle to DC']],
    [new Class{public $x = -122; public $y = 48;}, new Class{public $x = 0; public $y = 51;}, ['name' => 'Seattle to London']],
    [new Class{public $x = -75.9375; public $y = 35.460669951495305;}, new Class{public $x = 146.25; public $y = -43.06888777416961;}, ['name' => 'crosses dateline']],
    [new Class{public $x = 145.54687500000003; public $y = 48.45835188280866;}, new Class{public $x = -112.5; public $y = -37.71859032558814;}, ['name' => 'crosses dateline']]
  ];
  
  
  

foreach ($routes as $route) {
    $gc = new Arc\GreatCircle($route[0], $route[1], $route[2]);
    $line = $gc->Arc(50);
    $geojson->features[] = $line->json();
}

echo json_encode($geojson), PHP_EOL;
