# arc.php
> port of https://github.com/springmeyer/arc.js to php
> Calculate great circles routes as lines in GeoJSON or WKT format.


Algorithms from http://williams.best.vwh.net/avform.htm#Intermediate

Includes basic support for splitting lines that cross the dateline, based on
a partial port of code from OGR.

## License

BSD

## Usage

Just point the machine to Arc.php and it'll load everything:
```php
require_once '../Arc.php';
```
## API

**1)** Create start and end coordinates

First we need to declare where the arc should start and end

```php
$start = (object) [ 'x' => -122, 'y' => 48 ];
$end = (object) [ 'x' => -77, 'y' => 39 ];
```

Note that `x` here is longitude in degrees and `y` is latitude in degrees.

**2)** Create GreatCircle object

Next we pass the start/end to the `GreatCircle` constructor, along with an optional object representing the properties for this future line.

```php
$generator = new Arc\GreatCircle($start, $end, (object) ['name' => 'Seattle to DC']);
```

**3)** Generate a line arc

Then call the `Arc` function on the `GreatCircle` object to generate a line:

```php
$line = $generator->Arc(100,(object)['offset'=>10]);
```

The `line` will be a raw sequence of the start and end coordinates plus an arc of
intermediate coordinate pairs.

## CAVEATS AND FURTHER READING
Check https://github.com/springmeyer/arc.js for more information about how it works.
The interface should be more-or-less the same as javascript. Some small deviations where I felt they were neccessary.
I've also ported the examples and tests.
Your mileage may vary, I offer no warranty.
In fact, while I understand the signatures and whatnot, I didn't really bother untangling everything...
...So I know less about this than what springmeyer.
Nonetheless, if you have issues, report them here as they're quite probably bugs in my port effort :-)
It wasn't done with great care or diligence but it seems to work OK.
I wasn't aiming for any particular platform so there's a good chance it'll choke on PHP versions less than seven.

Anyway, enjoy :)
