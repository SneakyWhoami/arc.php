<?php

namespace Arc;

require_once 'Arc.php';
require_once 'Coord.php';
// require_once 'GreatCircle.php';
require_once 'LineString.php';

class GreatCircle {
    
    private $start;
    private $g;
    private $properties;
    
    /*
     * http://en.wikipedia.org/wiki/Great-circle_distance
     *
     */
    public function __construct($start,$end,$properties=array()) {
        if (empty($start)) {
            throw new \Error("GreatCircle constructor expects two args: start and end objects with x and y properties");
        }
        if (empty($end)) {
            throw new \Error("GreatCircle constructor expects two args: start and end objects with x and y properties");
        }
        $this->start = new Coord($start->x,$start->y);
        $this->end = new Coord($end->x,$end->y);
        $this->properties = $properties;
        
        $w = $this->start->x - $this->end->x;
        $h = $this->start->y - $this->end->y;
        $z = sin($h / 2.0) ** 2 +
        cos($this->start->y) *
        cos($this->end->y) *
        sin($w / 2.0) ** 2;
        $this->g = 2.0 * asin(sqrt($z));
        
        if ($this->g == pi()) {
            throw new \Error('it appears ' . $start->view() . ' and ' . $end->view() . " are 'antipodal', e.g diametrically opposite, thus there is no single route but rather infinite");
        } elseif (is_nan($this->g)) {
            throw new \Error('could not calculate great circle between ' . $start . ' and ' . $end);
        }
    }
    
    /*
     * http://williams.best.vwh.net/avform.htm#Intermediate
     */
    public function interpolate($f) {
        $A = sin((1 - $f) * $this->g) / sin($this->g);
        $B = sin($f * $this->g) / sin($this->g);
        $x = $A * cos($this->start->y) * cos($this->start->x) + $B * cos($this->end->y) * cos($this->end->x);
        $y = $A * cos($this->start->y) * sin($this->start->x) + $B * cos($this->end->y) * sin($this->end->x);
        $z = $A * sin($this->start->y) + $B * sin($this->end->y);
        $lat = rad2deg( atan2($z, sqrt($x ** 2 + $y ** 2)) );
        $lon = rad2deg(atan2($y, $x));
        return array($lon, $lat);
    }
    
    
    
    /*
     * Generate points along the great circle
     */
    public function arc($npoints=0,$options=false) {
        $first_pass = array();
        if (!$npoints || $npoints <= 2) {
            $first_pass[] = array($this->start->lon, $this->start->lat);
            $first_pass[] = array($this->end->lon, $this->end->lat);
        } else {
            $delta = 1.0 / ($npoints - 1);
            for ($i = 0; $i < $npoints; ++$i) {
                $step = $delta * $i;
                $pair = $this->interpolate($step);
                $first_pass[] = $pair;
            }
        }
        /* partial port of dateline handling from:
         *     gdal/ogr/ogrgeometryfactory.cpp
         *     TODO - does not handle all wrapping scenarios yet
         *     NOTE - this comment inherited with code
         */
        $bHasBigDiff = false;
        $dfMaxSmallDiffLong = 0;
        // from http://www.gdal.org/ogr2ogr.html
        // -datelineoffset:
        // (starting with GDAL 1.10) offset from dateline in degrees (default long. = +/- 10deg, geometries within 170deg to -170deg will be splited)
        $dfDateLineOffset = $options['offset'] ?? 10;
        $dfLeftBorderX = 180 - $dfDateLineOffset;
        $dfRightBorderX = -180 + $dfDateLineOffset;
        $dfDiffSpace = 360 - $dfDateLineOffset;
        
        // https://github.com/OSGeo/gdal/blob/7bfb9c452a59aac958bff0c8386b891edf8154ca/gdal/ogr/ogrgeometryfactory.cpp#L2342
        for ($j = 1; $j < count($first_pass); ++$j) {
            $dfPrevX = $first_pass[$j-1][0];
            $dfX = $first_pass[$j][0];
            $dfDiffLong = abs($dfX - $dfPrevX);
            if ($dfDiffLong > $dfDiffSpace &&
                (($dfX > $dfLeftBorderX && $dfPrevX < $dfRightBorderX) || ($dfPrevX > $dfLeftBorderX && $dfX < $dfRightBorderX))) {
                    $bHasBigDiff = true;
                } else if ($dfDiffLong > $dfMaxSmallDiffLong) {
                    $dfMaxSmallDiffLong = $dfDiffLong;
                }
        }
        
        $poMulti = array();
        if ($bHasBigDiff && $dfMaxSmallDiffLong < $dfDateLineOffset) {
            $poNewLS = array();
            for ($k = 0; $k < count($first_pass); ++$k) {
                $dfX0 = floatval($first_pass[$k][0]);
                if ($k > 0 &&  abs($dfX0 - $first_pass[$k-1][0]) > $dfDiffSpace) {
                    $dfX1 = floatval($first_pass[$k-1][0]);
                    $dfY1 = floatval($first_pass[$k-1][1]);
                    $dfX2 = floatval($first_pass[$k][0]);
                    $dfY2 = floatval($first_pass[$k][1]);
                    if ($dfX1 > -180 && $dfX1 < $dfRightBorderX && $dfX2 == 180 &&
                        $k+1 < count($first_pass) &&
                        $first_pass[$k-1][0] > -180 && $first_pass[$k-1][0] < $dfRightBorderX) {
                            $poNewLS[] = array(-180, $first_pass[$k][1]);
                            $k++;
                            $poNewLS[] = array($first_pass[$k][0], $first_pass[$k][1]);
                            continue;
                        } else if ($dfX1 > $dfLeftBorderX && $dfX1 < 180 && $dfX2 == -180 &&
                            $k+1 < count($first_pass) &&
                            $first_pass[$k-1][0] > $dfLeftBorderX && $first_pass[$k-1][0] < 180) {
                                $poNewLS[] = array(180, $first_pass[$k][1]);
                                $k++;
                                $poNewLS[] = array($first_pass[$k][0], $first_pass[$k][1]);
                                continue;
                            }
                            
                            if ($dfX1 < $dfRightBorderX && $dfX2 > $dfLeftBorderX) {
                                // swap dfX1, dfX2
                                $tmpX = $dfX1;
                                $dfX1 = $dfX2;
                                $dfX2 = $tmpX;
                                // swap dfY1, dfY2
                                $tmpY = $dfY1;
                                $dfY1 = $dfY2;
                                $dfY2 = $tmpY;
                            }
                            if ($dfX1 > $dfLeftBorderX && $dfX2 < $dfRightBorderX) {
                                $dfX2 += 360;
                            }
                            
                            if ($dfX1 <= 180 && $dfX2 >= 180 && $dfX1 < $dfX2) {
                                $dfRatio = (180 - $dfX1) / ($dfX2 - $dfX1);
                                $dfY = $dfRatio * $dfY2 + (1 - $dfRatio) * $dfY1;
                                $poNewLS[] = array($first_pass[$k-1][0] > $dfLeftBorderX ? 180 : -180, $dfY);
                                                                $poMulti[] = $poNewLS;
                                $poNewLS = [];
                                $poNewLS[] = array($first_pass[$k-1][0] > $dfLeftBorderX ? -180 : 180, $dfY);
                            } else {
                                $poNewLS = array();
                            }
                            $poNewLS[] = array($dfX0, $first_pass[$k][1]);
                } else {
                    $poNewLS[] = array($first_pass[$k][0], $first_pass[$k][1]);
                }
            }
                        $poMulti[] = $poNewLS;
        } else {
            // add normally
            $poNewLS0 = array();
            for ($l = 0; $l < count($first_pass); ++$l) {
                $poNewLS0[] = array($first_pass[$l][0],$first_pass[$l][1]);
            }
            $poMulti[] = $poNewLS0;
        }

        $arc = new Arc($this->properties);
        for ($m = 0; $m < count($poMulti); ++$m) {
            $line = new LineString();
            $points = $poMulti[$m];
            for ($j0 = 0; $j0 < count($points); ++$j0) {
                $line->move_to($points[$j0]);
            }
            $arc->geometries[] = $line;
        }
        return $arc;
    }
    
}
