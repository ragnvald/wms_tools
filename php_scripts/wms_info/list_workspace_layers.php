<?php
//////////////////////////////////////////////////////////////////
// list_layers.php
//     This file produces an openlayer map based on layers within a 
//     given workspace on a geoserver
//
//     It uses the wms-parser.php library
//
//     rla@miljodir.no
//
///////////////////////////////////////////////////////////////////


//Script settings

$default_workspace          = "files";
$thumbnail_maxx             = 200;
$wms_server                 = "http://mapa.meioambiente.tl/geoserver/";
$wms_server_ows             = $wms_server."ows?";
$wms_server_getcapabilities = $wms_server_ows."service=wms&version=1.1.1&request=GetCapabilities";


$overviewmap_visibility     = true;
$overviewmap_layers_on      = "false";

$link_viewongeoserver       = true;
$link_downloadshapefile     = true;

$link_viewongeonode         = false;
$geonode_server             = "http://testserver.com"."/data/";




//Get workspace request 
$select_workspace=(isset($_GET['ws']) ? $_GET['ws'] : null);

//USe the default workspace if the request returns empty
if (empty($select_workspace))
{
    $select_workspace = $default_workspace;
}
// The workspace has been set. Ready to roll...
else {
    
}

$domain = $select_workspace;

$select_workspace = $select_workspace.":";



//get that fairly ok wms parser
include ('include/wms-parser.php');


//Calculate the workspace length for use later
$select_workspace_length=strlen($select_workspace);

$gestor     = fopen($wms_server_getcapabilities, "r");
$contenido  = stream_get_contents($gestor);
fclose($gestor);

$caps = new CapabilitiesParser( );
$caps->parse($contenido);
$caps->free_parser( );


//Go through all relevant layers and find max lonlat extent

             
// Set default values to be adjusted      
$minx_geo=180.00;
$miny_geo=90.00;
$maxx_geo=-180.00;
$maxy_geo=-90.00;


foreach ($caps->layers as $d)
{
    if ($d['queryable'])
    {
        if (substr(($d['Name']), 0, $select_workspace_length) == $select_workspace)
        {
            if (isset($d['LatLonBoundingBox']['minx'])) 
            {
                $current_minx_geo = $d['LatLonBoundingBox']['minx'];  
                if (floatval($current_minx_geo) < $minx_geo)
                {
                    $minx_geo = floatval($current_minx_geo);
                }
            }

            if (isset($d['LatLonBoundingBox']['miny'])) 
            {
                $current_miny_geo = $d['LatLonBoundingBox']['miny'];  
                if (floatval($current_miny_geo) < $miny_geo)
                {
                    $miny_geo = floatval($current_miny_geo);
                }
            }

            if (isset($d['LatLonBoundingBox']['maxx'])) 
            {
                $current_maxx_geo = $d['LatLonBoundingBox']['maxx'];  
                if (floatval($current_maxx_geo) > $maxx_geo)
                {
                    $maxx_geo = floatval($current_maxx_geo);
                }
            }

            if (isset($d['LatLonBoundingBox']['maxy'])) 
            {
                $current_maxy_geo = $d['LatLonBoundingBox']['maxy'];  
                if (floatval($current_maxyx) > $maxy_geo)
                {
                    $maxy_geo = floatval($current_maxy_geo);
                }
            }    

        }
    }
}

//Make the average calculation to find the center of the presented map
$map_center_x_geo =(($minx_geo + $maxx_geo) / 2);
$map_center_y_geo =(($miny_geo + $maxy_geo) / 2);

$boundingbox_geo = $minx_geo.",".$miny_geo.",".$maxx_geo.",".$maxy_geo;

//Go through all relevant layers and find max native extent

// Set default values to be adjusted                      
$minx_native=10000000;
$miny_native=-10000000;
$maxx_native=-10000000;
$maxy_native=10000000;



foreach ($caps->layers as $d)
{
    if ($d['queryable'])
    {

        if (is_array($d['BoundingBox'])) {
        
        
            if (substr(($d['Name']), 0, $select_workspace_length) == $select_workspace)
            {
               
                    $current_minx_native = $d['BoundingBox'][$srs_native[0]]['minx'];  
                    
                    if (floatval($current_minx_native) < $minx_native)
                    {
                        $minx_native = floatval($current_minx_native);
                    }
                

              
                    $current_miny_native = $d['BoundingBox'][$srs_native[0]]['miny'];  
                    if (floatval($current_miny_native) < $miny_native)
                    {
                        $miny_native = floatval($current_miny_native);
                    }
                

                    $current_maxx_native = $d['BoundingBox'][$srs_native[0]]['maxx'];  
                    if (floatval($current_maxx_native) > $maxx_native)
                    {
                        $maxx_native = floatval($current_maxx_native);
                    }
                

             
                    $current_maxy_native = $d['BoundingBox'][$srs_native[0]]['maxy'];  
                    if (floatval($current_maxy_native) > $maxy_native)
                    {
                        $maxy_native = floatval($current_maxy_native);
                    }
            
               
            }
         }
    }
}

//Make the average calculation to find the center of the presented map
$map_center_x_native =(($minx_native + $maxx_native) / 2);
$map_center_y_native =(($miny_native + $maxy_native) / 2);


$boundingbox_native = $minx_native.",".$miny_native.",".$maxx_native.",".$maxy_native;

//HTML ode starts ?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>Spatial data for the Geoserver workspace: <?php echo $select_workspace; ?></title>

        <script src ='http://openlayers.org/api/OpenLayers.js'></script>
        
        <link rel="stylesheet" href="include/style_layout.css" type="text/css">
        <link rel="stylesheet" href="include/style_map.css" type="text/css">

       
        <script type = "text/javascript">
            var layer, map;
            
            var lon = <?php echo $map_center_x_geo ?>;
            var lat = <?php echo $map_center_y_geo ?>;

            //not used yet
            var zoom = 12;

            var format = 'image/png';
            
            //maps established
            function init(){
                
               var geographic  =  new OpenLayers.Projection("EPSG:4326");
               var mercator    =  new OpenLayers.Projection("EPSG:900913");
                                
               map = new OpenLayers.Map( 'map',{projection: mercator});
                
               map.addControl(new OpenLayers.Control.LayerSwitcher());
            
               //OpenStreetMap layer established 
               layer = new OpenLayers.Layer.OSM( "Simple OSM Map");
                    map.addLayer(layer);
                    map.setCenter(
                        new OpenLayers.LonLat(lon,lat).transform(
                            new OpenLayers.Projection("EPSG:4326"),
                            map.getProjectionObject()
                        ), 4
                    );  
                <?php 

                // Add layers according to available layers in Geoserver
                $i=0;
                
                foreach ($caps->layers as $l) {
                    
                    if ($l['queryable']) {          
                    
                        //Filter out layers which are similar to the one 
                        if  (substr(($l['Name']),0,$select_workspace_length)==$select_workspace) {          
                            
                            $srs_geo = (array_keys($d['BoundingBox']));?>
                            
                            //Addning layer for <?php echo $l['Name'] ?> separately
                            var wms_layer_<?php echo $i; ?> = new OpenLayers.Layer.WMS("<?php echo $l['Name'] ?>",
                                "<?php echo $wms_server_ows ?>service=wms&version=1.1.1",
                                {
                                    layers:'<?php echo $l['Name'] ?>',
                                    styles:'',
                                    srs:'EPSG:4326',
                                    format:'image/png', 
                                    transparent: "true"
                                },
                                {opacity: 1.0},
                                {isBaseLayer: false}
                            );
                             
                             
                            wms_layer_<?php echo $i; ?>.setVisibility(<?php echo $overviewmap_layers_on?>); 
                            
                            map.addLayer(wms_layer_<?php echo $i; ?>);


                            <?php $i++;
                        }  
                    } 
                }?>                                                         
            }
        </script>
     </head>
     <body onload = "init()">
        <?php if ($overviewmap_visibility==true) {?>
        <div id="map" class="mediummap"></div>
        <br>
        <?php
        }
        ?>
        <strong>Layers count:</strong> <?php echo ($i - 1) ?>
        <ol>
            <?php
            $i=1;

            //Make an html list. Contains wms-layer metadata and a small map of the different layers
            foreach ($caps->layers as $l)
            {
                if ($l['queryable'])
                {
                    if (substr(($l['Name']), 0, $select_workspace_length) == $select_workspace)
                    {
                        
                        //Handle native srs based information
                        $srs_native = (array_keys($l['BoundingBox']));
                                            
                        if (isset($l['BoundingBox'][$srs_native[0]]['minx'])) 
                        {
                            $current_minx_t = $l['BoundingBox'][$srs_native[0]]['minx'];  
                            $current_minx = floatval($current_minx_t);
                        }

                        if (isset($l['BoundingBox'][$srs_native[0]]['miny'])) 
                        {
                            $current_miny_t = $l['BoundingBox'][$srs_native[0]]['miny']; 
                            $current_miny = floatval($current_miny_t);
                        }

                        if (isset($l['BoundingBox'][$srs_native[0]]['maxx'])) 
                        {
                            $current_maxx_t = $l['BoundingBox'][$srs_native[0]]['maxx'];
                            $current_maxx = floatval($current_maxx_t);  
                        }

                        if (isset($l['BoundingBox'][$srs_native[0]]['maxy'])) 
                        {
                            $current_maxy_t = $l['BoundingBox'][$srs_native[0]]['maxy']; 
                            $current_maxy = floatval($current_maxy_t);
                        }    
                        
                        $boundingbox_native = $current_minx.",".$current_miny.",".$current_maxx.",".$current_maxy;
                        
                        $distance_xmin_xmax = $current_maxx - $current_minx;
                        $distance_ymin_ymax = $current_maxy - $current_miny;

                        $thumbnail_ratio = ($distance_ymin_ymax/$distance_xmin_xmax);

                        $thumbnail_maxy = intval($thumbnail_maxx*$thumbnail_ratio);
                        
                        //Handle geographinc information
                                            
                        if (isset($l['LatLonBoundingBox']['minx'])) 
                        {
                            $current_minx_t = $l['LatLonBoundingBox']['minx'];
                            $current_minx = floatval($current_minx_t);
                        }

                        if (isset($l['LatLonBoundingBox']['miny'])) 
                        {
                            $current_miny_t = $l['LatLonBoundingBox']['miny']; 
                            $current_miny = floatval($current_miny_t);
                        }

                        if (isset($l['LatLonBoundingBox']['maxx'])) 
                        {
                            $current_maxx_t = $l['LatLonBoundingBox']['maxx'];
                            $current_maxx = floatval($current_maxx_t);  
                        }

                        if (isset($l['LatLonBoundingBox']['maxy'])) 
                        {
                            $current_maxy_t = $l['LatLonBoundingBox']['maxy']; 
                            $current_maxy = floatval($current_maxy_t);
                        }    

                        $boundingbox_geo = $current_minx.",".$current_miny.",".$current_maxx.",".$current_maxy;
     
                        ?>
        <li><strong><?php echo $l['Title'] ?></strong><br><br>
            <table>
                <tr>
                    <td><b>Map preview</b><br>
                        <img src="<?php echo $wms_server ?><?php echo $domain ?>/wms?service=WMS&version=1.1.0&request=GetMap&layers=<?php echo $l['Name'] ?>&styles=&bbox=<?php echo $boundingbox_native;?>&width=<?php echo $thumbnail_maxx ?>&height=<?php echo $thumbnail_maxy ?>&srs=<?php echo $srs_native[0]?>&format=image/png">
                    </td>
                    <td>&nbsp;</td>
                    <td><?php 
                                        if (strlen($l['Abstract'])>0) {
                                            echo "<b>Abstract</b><br>";
                                            echo $l['Abstract']."<br><br>";
                                        }
                                        if ($link_viewongeoserver==true) {
                                        ?>
                        <a href="<?php echo $wms_server_ows ?>service=wms&version=1.1.1&request=GetMap&layers=<?php echo $l['Name'] ?>&styles=&bbox=<?php echo $boundingbox_native;?>&width=512&height=469&srs=<?php echo $srs_native[0] ?>&format=application/openlayers" target="_blank"><img src="graphics/icon_link.png" border="0" width="16" height="16" alt="Link to geoserver">View on Geoserver</a>
                        <br>
                        <br> 
                                        <?php
                                        } 
                                        if ($link_downloadshapefile==true) {
                                      ?>
                        <a href="<?php echo $wms_server_ows ?>service=WFS&version=1.0.0&request=GetFeature&typeName=<?php echo $l['Name'] ?>&outputFormat=SHAPE-ZIP" target="_blank"><img src="graphics/icon_download.png" border="0" width="16" height="16" alt="icon_download.png (1?159 bytes)">Download shapefile</a>
                        <br>
                        <br>
                                        <?php
                                        } 
                                        if ($link_viewongeonode==true) {
                                        ?>
                        <a href="<?php echo $geonode_server ?><?php echo $l['Name'] ?>" target="_blank"><img src="graphics/icon_link.png" border="0" width="16" height="16" alt="Link to geonode server">View on Geonode server</a><br><?php
                                        }
                                        ?>   
                        </td>
                    </tr>
                    <tr>
                        <td><b>Legend:</b><br><img src = "<?php echo $wms_server_ows ?>service=wms&REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=<?php echo $l['Name'] ?>">
                        </td>
                        <td>&nbsp;</td>
                        <td valign=bottom>
                            <b>Bounding box:</b><br>
                            <i><?php echo $srs_native[0].": ".$boundingbox_native; ?>)</i><br> 
                            <i><?php echo "Geograpihcal: ".$boundingbox_geo; ?>)</i>
                        </td>
                    </tr>
                </table>
                <hr>
                <br/></li>
                        <?php
                        $i++;
                    }
                }
            }?>
    </ol>
    <br>
    <i><a href="https://github.com/miljodir/wms_tools" target="_blank">WMS php tools Version 0.7</a></i>
    </body>
</html>