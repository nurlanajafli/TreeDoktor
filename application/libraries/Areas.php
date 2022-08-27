<?php  
	//$test['rings'][$k][$key][] = $val->lat;
	//$test['rings'][$k][$key][] = $val->lng;

class Areas
{
/*
 * calculate the area of a polygon, in whatever units it's in
 */
function getAreaOfPolygon($polygon) {
    $area = 0;

	for ($vi=0, $vl=sizeof($polygon); $vi<$vl; $vi++) {
		$thisx = $polygon[ $vi ]->lng;
		$thisy = $polygon[ $vi ]->lat;
		$nextx = $polygon[ ($vi+1) % $vl ]->lng;
		$nexty = $polygon[ ($vi+1) % $vl ]->lat;
		$area += ($thisx * $nexty) - ($thisy * $nextx);
	}

    // done with the rings: "sign" the area and return it
    $area = abs(($area / 2));
    return $area;
}

/*
 * calculate the centroid of a polygon
 * return a 2-element list: array($x,$y)
 */
function getCentroidOfPolygon($polygon) {
    $cx = 0;
    $cy = 0;

	$ring = $polygon;
	
	for ($vi=0, $vl=count($ring); $vi<$vl; $vi++) {//countOk
		$thisx = $ring[$vi]->lng;
		$thisy = $ring[$vi]->lat;
		$nextx = $ring[($vi+1) % $vl ]->lng;
		$nexty = $ring[($vi+1) % $vl ]->lat;

		$p = ($thisx * $nexty) - ($thisy * $nextx);
		$cx += ($thisx + $nextx) * $p;
		$cy += ($thisy + $nexty) * $p;
	}

    // last step of centroid: divide by 6*A 
    $area = $this->getAreaOfPolygon($polygon);
    $cx = -$cx / ( 6 * $area);
    $cy = -$cy / ( 6 * $area);

    // done!
    return array($cy, $cx);
}
}
?>
