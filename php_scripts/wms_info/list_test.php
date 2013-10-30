<?php                               

include ('include/wms-parser.php');

$name_source = "http://wms.dirnat.no/geoserver/ows?service=wms&version=1.1.1&request=GetCapabilities";

$source_info = fopen($name_source, "r");
$contents = stream_get_contents($source_info);
fclose($source_info);

$caps = new CapabilitiesParser( );
$caps->parse($contents);
$caps->free_parser( );

?>
<h1>Layers on this server</h1>

<strong>WMS version:</strong> <?php echo $caps->version ?><br />
<strong>Layers count:</strong> <?php echo sizeof($caps->layers) ?> <br />

<h2>Layers list</h2>
<ol>
<?php foreach ($caps->layers as $l) { ?>
  <li><strong><?php echo $l['Title'] ?></strong><br />
  <?php echo $l['Abstract'] ?><br />
  <?php echo $l['SRS'] ?><br />
  Limits: (<i><?php echo $l['LatLonBoundingBox']['minx'] . ',' .    
                         $l['LatLonBoundingBox']['miny'] . ',' .   
                         $l['LatLonBoundingBox']['maxx'] . ',' .
                         $l['LatLonBoundingBox']['maxy'];  ?>)</i>
  </p></li>
<?php } ?>
</ol>