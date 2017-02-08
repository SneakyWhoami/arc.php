<?php

require_once '../Arc.php';

final class ArcTest extends PHPUnit_Framework_TestCase {
    
    
    public function testCoordWorksOK(): void
    {
        $coord = new Arc\Coord(0, 0);
        $this->assertEquals(0,$coord->lon);
        $this->assertEquals(0,$coord->lat);
        $this->assertEquals(0,$coord->x);
        $this->assertEquals(0,$coord->y);
        $this->assertEquals('0,0',$coord->view());
        $this->assertEquals('-180,0',$coord->antipode()->view());
    }
    
    public function testArcWorksOK(): void
    {
        // the json representation should eventually work out regardless
        $a = new Arc\Arc();
        $this->assertEquals('[]',json_encode($a->properties));
        $this->assertEquals('', $a->wkt());
        $this->assertEquals(json_decode('{"geometry":{"type":"LineString","coordinates":null},"type":"Feature","properties":[]}',true),$a->json());
    }
    
    
    public function testGreatCircleWorksOK(): void
    {
        $a = new Arc\GreatCircle((object)['x'=>0,'y'=>0], (object)['x'=>10,'y'=>0]);
        //     $this->assertTrue($a); // whatever
        $this->assertEquals([0,0], $a->interpolate(0));
        $this->assertEquals([10,0], $a->interpolate(1));
    }
    
    public function testRoutingWorksOK(): void
    {
        
        $routes = json_decode('[[{"x":-122,"y":48},{"x":-77,"y":39},{"name":"Seattle to DC"}],[{"x":-122,"y":48},{"x":0,"y":51},{"name":"Seattle to London"}],[{"x":-75.9375,"y":35.460669951495305},{"x":146.25,"y":-43.06888777416961},{"name":"crosses dateline 1"}],[{"x":145.54687500000003,"y":48.45835188280866},{"x":-112.5,"y":-37.71859032558814},{"name":"crosses dateline 2"}],[{"x":-74.564208984375,"y":-0.17578097424708533},{"x":137.779541015625,"y":-22.75592068148639},{"name":"south 1"}],[{"x":-66.829833984375,"y":-18.81271785640776},{"x":118.795166015625,"y":-20.797201434306984},{"name":"south 2"}]]');
        // trouble: needs work
        // NOTE: this is an original comment. i haven't investigated
        //{ x: 114.576416015625, y:-44.21370990970204},{x: -65.423583984375, y:-43.19716728250127}
        
        $arcs = json_decode(' [
        {"properties":{"name":"Seattle to DC"},"geometries":[{"coords":[[-122,48.00000000000001],[-97.72808611752785,45.75368189927002],[-77,38.99999999999999]],"length":3}]},
        {"properties":{"name":"Seattle to London"},"geometries":[{"coords":[[-122,48.00000000000001],[-64.16590091973099,67.47624207149578],[0,51]],"length":3}]},
        {"properties":{"name":"crosses dateline 1"},"geometries":[{"coords":[[-75.9375,35.46066995149531],[-136.82303405489677,-10.367409282634164],[146.25,-43.06888777416961]],"length":3}]},
        {"properties":{"name":"crosses dateline 2"},"geometries":[{"coords":[[145.54687500000003,48.45835188280866],[-157.28484118689477,8.44205355944752],[-112.5,-37.71859032558814]],"length":3}]},
        {"properties":{"name":"south 1"},"geometries":[{"coords":[[-74.564208984375,-0.17578097424708533],[-140.44327137076033,-35.80108605508993],[137.779541015625,-22.755920681486387]],"length":3}]},
        {"properties":{"name":"south 2"},"geometries":[{"coords":[[-66.829833984375,-18.812717856407765],[-146.78177837397814,-82.1795027896656],[118.795166015625,-20.79720143430698]],"length":3}]}
        ]');
        
        // var_dump($routes);die;
        
        foreach ($routes as $i => $route) {
            $gc = new Arc\GreatCircle($route[0], $route[1], $route[2]);
            $line = $gc->Arc(3);
            //console.log(JSON.stringify(line))
            // too much of a mess without serialization. but maybe later...
            $this->assertEquals($arcs[$i]->geometries[0]->coords, $line->geometries[0]->coords); 
        }
        
        
    }
    
    
    
}
