<?php

include ('/include/wms-parser.php');
                                              
$nombre_archivo         ="http://mapa.meioambiente.tl:8080/geoserver/wms?request=GetCapabilities&service=WMS";
$gestor                 =fopen($nombre_archivo, "rb");
$contenido              =stream_get_contents($gestor);
fclose ($gestor);

$caps = new CapabilitiesParser( );
$caps->parse($contenido);
$caps->free_parser( );

?>

<h1>WMS Services Description</h1>

<strong>WMS version:</strong> <?php echo $caps->version ?><br />
<strong>Layers count:</strong> <?php echo sizeof($caps->layers) ?> <br />

<h2>Layers list</h2>
<ol>
<?php                        
foreach ($caps->layers as $l) { ?>
  <li><strong><?php echo $l['Title'] ?></strong><br />
  <?php echo $l['Abstract'] ?><br />
  Limits: (<i><?php 
                  echo   $l['LatLonBoundingBox']['minx'] . ',' .
                         $l['LatLonBoundingBox']['miny'] . ',' .
                         $l['LatLonBoundingBox']['maxx'] . ',' .
                         $l['LatLonBoundingBox']['maxy'];  ?>)</i>
  </p></li>
<?php } ?>
</ol>
