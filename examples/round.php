#!/usr/bin/env php
<?php
// Round GeoJSON lines file.

require_once '../Arc.php';

$geojson = json_decode(file_get_contents('tracks.geojson'));

$tolerance = 1;

for ($i = 0; $i < count($geojson->features); $i++) {
    if ($geojson->features[$i]->geometry->type == 'LineString') {
        for ($j = 0; $j < count($geojson->features[$i]->geometry->coordinates) - 1; $j++) {
            $a = $geojson->features[$i]->geometry->coordinates[$j];
            $b = $geojson->features[$i]->geometry->coordinates[$j + 1];
            $dist = sqrt(
                abs($a[0] - $b[0]) *
                abs($a[1] - $b[1]));
            if ($dist > $tolerance) {
                $gc = new Arc\GreatCircle(
                    (object) [ 'x' => $a[0], 'y' => $a[1] ],
                    (object) [ 'x' => $b[0], 'y' => $b[1] ]);
                $line = $gc->Arc(10);
                $geojson->features[$i]->geometry->coordinates[$j]['arc'] = $line->geometries[0]->coords;
            }
        }
        $co = [];
        for ($k = count($geojson->features[$i]->geometry->coordinates) - 1; $k >= 0; $k--) {
            if (isset($geojson->features[$i]->geometry->coordinates[$k]['arc'])) {
                $co = array_merge($geojson->features[$i]->geometry->coordinates[$k]['arc'], $co);
            } else {
                array_unshift($co, $geojson->features[$i]->geometry->coordinates[$k]);
            }
        }
        $geojson->features[$i]->geometry->coordinates = $co;
    }
}

echo json_encode($geojson).PHP_EOL;
