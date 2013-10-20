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


function get_boundingbox_native($srs_bbox_arrays){ 

    
    $srs_native = (array_keys($srs_bbox_arrays['BoundingBox']));
                        
    if (isset($srs_bbox_arrays['BoundingBox'][$srs_native]['minx'])) 
    {
        $current_minx_geo = $srs_bbox_arrays['BoundingBox'][$srs_native]['minx'];  
        $minx_geo = floatval($current_minx_geo);
        
    }

    if (isset($srs_bbox_arrays['BoundingBox']['miny'])) 
    {
        $current_miny_geo = $srs_bbox_arrays['BoundingBox'][$srs_native]['miny'];  
        $miny_geo = floatval($current_miny_geo);
        
    }

    if (isset($srs_bbox_arrays['BoundingBox'][$srs_native]['maxx'])) 
    {
        $current_maxx_geo = $srs_bbox_arrays['BoundingBox'][$srs_native]['maxx'];  
        $maxx_geo = floatval($current_maxx_geo);
        
    }

    if (isset($srs_bbox_arrays['BoundingBox'][$srs_native]['maxy'])) 
    {
        $current_maxy_geo = $srs_bbox_arrays['BoundingBox'][$srs_native]['maxy']; 
        $maxy_geo = floatval($current_maxy_geo);
    }    

    return array ($minx_geo, $miny_geo, $maxx_geo, $maxy_geo);
    
} 



//Get workspace request 

$select_workspace=(isset($_GET['ws']) ? $_GET['ws'] : null);


//Do the default workspace if the request returns empty
if (empty($select_workspace))
{
    $select_workspace="inon";
}

$select_workspace = $select_workspace.":";

//Set variables
$local_server           ="http://wms.dirnat.no/geoserver/ows?service=wms&version=1.1.1&request=GetCapabilities";


//get that fairly ok wms parser
include ('include/wms-parser.php');


//Calculate the workspace length for use later
$select_workspace_length=strlen($select_workspace);

$nombre_archivo         ="http://wms.dirnat.no/geoserver/ows?service=wms&version=1.1.1&request=GetCapabilities";
$gestor = fopen($nombre_archivo, "r");
$contenido = stream_get_contents($gestor);
fclose($gestor);

$caps = new CapabilitiesParser( );
$caps->parse($contenido);
$caps->free_parser( );


//Calclate the max extent for all layers listed under this workspace                         
$minx_geo=180.00;
$miny_geo=90.00;
$maxx_geo=-180.00;
$maxy_geo=-90.00;


//Go through all relevant layers and find max extent
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

//Calclate the max extent for all layers listed under this workspace                         
$minx_native=10000000;
$miny_native=-10000000;
$maxx_native=-10000000;
$maxy_native=10000000;


//Go through all relevant layers and find max extent
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

//Here the HTML code starts
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Spatial data for the Geoserver workspace: <?php echo $select_workspace; ?></title>

        <script src = "http://www.openlayers.org/api/OpenLayers.js"></script>

        <script type = "text/javascript">
            var lon = <?php echo $map_center_x_geo; ?>;
            var lat = <?php echo $map_center_y_geo; ?>;

            var zoom = 8;

            format = 'image/png';

            var map, layer1,layer2 ;

            function init(){

                map_controls = [ new OpenLayers.Control.OverviewMap(),       
                    new OpenLayers.Control.LayerSwitcher({'ascending':true}),
                    new OpenLayers.Control.PanZoomBar(),
                    new OpenLayers.Control.MouseToolbar(), 
                    new OpenLayers.Control.KeyboardDefaults()];

                map = new OpenLayers.Map( 'map', {controls: map_controls} );

                var ls = map.getControlsByClass('OpenLayers.Control.LayerSwitcher')[0];

                ls.maximizeControl();

                var gphy = new OpenLayers.Layer.Google(
                    "Google Physical",
                    {type: G_PHYSICAL_MAP}
                );

                map.addLayer(gphy);


                var gsat = new OpenLayers.Layer.Google(
                    "Google Satellite",
                    {type: G_SATELLITE_MAP, numZoomLevels: 22}
                );

                map.addLayer(gsat);


                var wms_osm = new OpenLayers.Layer.WMS("Welt",
                    "http://129.206.229.158/cached/osm?",
                    {layers: 'europe_wms:hs_srtm_europa', 
                        format: 'image/png'},
                    {buffer: 0});


                map.addLayer(wms_osm);<?php 

                //Add layers according to available layers in Geoserver
                $i=0;
                
                foreach ($caps->layers as $l) {
                    if ($l['queryable']) {          
                        //Filter out layers which are similar to the one 
                        if  (substr(($l['Name']),0,$select_workspace_length)==$select_workspace) {
                            
                            
                            $srs_native = (array_keys($d['BoundingBox']));
                            
                            echo $d['Name'].":".$srs_native[0]."<br>";
                            ?>    

                            wms_layer_<?php echo $i; ?> = new OpenLayers.Layer.WMS("<?php echo isset($l['Title']) ?>",
                                "http://wms.dirnat.no/geoserver/ows?service=wms",
                                {
                                    layers: '<?php echo $l['Name'] ?>',
                                    styles: '',
                                    //srs: 'EPSG:4326',
                                    format: format,
                                    transparent: "true"
                                },
                                {
                                    opacity:0.5
                                },
                                {
                                    isBaseLayer: false, visibility: false
                                }
                            );

                            map.addLayer(wms_layer_<?php echo $i; ?>);

                            wms_layer_<?php echo $i; ?>.setVisibility(false);  

                            <?php $i++;
                        }  
                    } 

                }?>                                                                                                                 
                map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);     
            }
        </script>
    </head>

    <body onload = "init()">
        <h1 id = "title">Data set presentation</h1>

        <div id = "tags">wms, layer, singletile
        </div>

        <p id = "shortdesc">
            The data presented are from the geoserver install supervised by Ragnvald Larsen. This setup is currently for testing purposes. Contact me if there are any questions at ragnvald@mindland.com
        </p>

        <div id = "map" class = "bigmap">
        </div>

        <h2><strong>Layers count:</strong><?php echo ($i - 1) ?></h2>

        <ol>
            <?php
            $i=1;

            //Make a list of the layers
            foreach ($caps->layers as $l)
            {
                if ($l['queryable'])
                {
                    if (substr(($l['Name']), 0, $select_workspace_length) == $select_workspace)
                    
                    {
                        
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
                        
                        ?>

                        <li><strong><?php echo $l['Title'] ?></strong><br>
                        (<a href=http://wms.dirnat.no/geoserver/ows?service=wms&version=1.1.1&request=GetMap&layers=<?php echo $l['Name'] ?>&styles=&bbox=<?php echo $boundingbox_native;?>&width=512&height=469&srs=<?php echo $srs_native[0] ?>&format=application/openlayers><?php
                            echo $l['Name'] ?></a>)<br/>

                            <a href = "#" id = "<?php echo ($i-1) ?>-show" class = "showLink"
                                onclick = "showHide('<?php echo ($i-1) ?>');return false;"> <img src="/graphics/btn_open.gif" border="0" width="19" height="25" alt="Open">See more.</a>

                            <div id = "<?php echo ($i-1) ?>"
                                class = "more"><a href = "#"
                                    id = "<?php echo ($i-1) ?>-hide"
                                    class = "hideLink"
                                    onclick = "showHide('<?php echo ($i-1) ?>');return false;"><img src="/graphics/btn_close.gif" border="0" width="19" height="25" alt="Close"> Hide</a>

                                <br>
                                <table>
                                    <tr><td colspan=2><a href="http://wms.dirnat.no/geoserver/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=<?php echo $l['Name'] ?>&outputFormat=SHAPE-ZIP">Download shapefile</a></td></tr>
                                    <tr><td><b>Legend&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td><td><b>Abstract</b></td></tr>
                                    <tr><td><img src = "http://wms.dirnat.no/geoserver/ows?service=wms&REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=<?php echo $l['Name'] ?>"></td>

                                        <td>
                                            <?php
                                            echo $l['Abstract']?>

                                            <br>
                                            Bounding box: (<i><?php echo $boundingbox_native; ?>)</i>    

                                        </td>

                                    </tr>

                                </table>
                            </div>

                            <br/></li>

                        <?php
                        $i++;
                    }
                }
            }
            ?>
        </ol>
    </body>
</html>