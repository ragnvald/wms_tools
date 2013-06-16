<?php
//////////////////////////////////////////////////////////////////
// list_layers.php
//  This file produces an openlayer map based on layers within a 
//  given workspace on a geoserver
//
//  It uses the wms-parser.php library
//
//  Ragnvald.larsen@dirnat.no
//
///////////////////////////////////////////////////////////////////


//Get workspace request 
$select_workspace=(isset($_GET['ws']) ? $_GET['ws'] : null);


//Do the default workspace if the request returns empty
if (empty($select_workspace))
    {
    $select_workspace="files";
    }

$select_workspace = $select_workspace.":";

//Set variables
$local_server           ="http://mapa.meioambiente.tl:8080/geoserver/";


//get that fairly ok wms parser
include ('include/wms-parser.php');


//Calculate the workspace length for use later
$select_workspace_length=strlen($select_workspace);

$nombre_archivo         ="http://mapa.meioambiente.tl:8080/geoserver/ows?service=wms&version=1.1.1&request=GetCapabilities";
$gestor = fopen($nombre_archivo, "r");
$contenido = stream_get_contents($gestor);
fclose($gestor);

$caps = new CapabilitiesParser( );
$caps->parse($contenido);
$caps->free_parser( );


//Calclate the max extent for all layers listed under this workspace                         
$minx=180;
$miny=90;
$maxx=-180;
$maxy=-90;


//Make a list of the layers
foreach ($caps->layers as $d)
    {
    if ($d['queryable'])
        {
        if (substr(($d['Name']), 0, $select_workspace_length) == $select_workspace)
            {
            if (isset($d['LatLonBoundingBox']['minx']) < $minx)
                {
                $minx=isset($d['LatLonBoundingBox']['minx']);
                }

            if (isset($d['LatLonBoundingBox']['miny']) < $miny)
                {
                $miny=isset($d['LatLonBoundingBox']['miny']);
                }

            if (isset($d['LatLonBoundingBox']['maxx']) > $maxx)
                {
                $maxx=isset($d['LatLonBoundingBox']['maxx']);
                }

            if (isset($d['LatLonBoundingBox']['maxy']) > $maxy)
                {
                $maxy=isset($d['LatLonBoundingBox']['maxy']);
                }
            }
        }
    }

//Make the average calculation
$map_center_x =(($minx + $maxx) / 2);
$map_center_y =(($miny + $maxy) / 2);


//Here the HTML code starts
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Spatial data for the Geoserver workspace: <?php echo $select_workspace; ?></title>

        <link rel = "stylesheet" href = "../theme/default/style.css" type = "text/css">
        <link rel = "stylesheet" href = "../theme/default/google.css" type = "text/css">
        <link rel = "stylesheet" href = "style.css" type = "text/css">
        <script src="http://ecn.dev.virtualearth.net/mapcontrol/mapcontrol.ashx?v=6.2&mkt=en-us"></script>
        <script src="http://api.maps.yahoo.com/ajaxymap?v=3.0&appid=euzuro-openlayers"></script>
        <script src = "http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAZXoppjlhcZwAzXZ7jOC8WRT9iijjZKBe9uPuf_n3vkiE-yFixhQpE_8NU7MHWhy-9wv02VKsi9QkTw"></script>

        <script src = "http://www.openlayers.org/api/OpenLayers.js"></script>

        <SCRIPT type = "text/javascript">
            //From http://www.cssnewbie.com/showhide-content-css-javascript/
            function showHide(shID)
                {
                if (document.getElementById(shID))
                    {
                    if (document.getElementById(shID + '-show').style.display != 'none')
                        {
                        document.getElementById(shID + '-show').style.display = 'none';
                        document.getElementById(shID).style.display = 'block';
                        }

                    else
                        {
                        document.getElementById(shID + '-show').style.display = 'inline';
                        document.getElementById(shID).style.display = 'none';
                        }
                    }
                }
        </SCRIPT>

        <script type = "text/javascript">
        var lon = <?php echo $map_center_x; ?>;
        var lat = <?php echo $map_center_y; ?>;
        
        var zoom = 8;
        
        format = 'image/png';
        
        var map, wms_overall_biodiversity, wms_biodiv_conservation_status, wms_biodiv_mammals_and_crocodiles, wms_biodiv_species_special_importance, wms_biodiv_species_richness, wms_biodiv_threatened_birds ;

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

            
        map.addLayer(wms_osm);
        
        var wms_osm_all = new OpenLayers.Layer.WMS("All",
                    "http://129.206.229.158/cached/osm?",
                    {layers: 'osm_auto:all', 
                    format: 'image/png'},
                    {buffer: 0});

            
        map.addLayer(wms_osm_all);
        
        var yahooLayer = new OpenLayers.Layer.Yahoo( "Yahoo");


            
        map.addLayer(yahooLayer);<?php 

//Add layers according to available layers in Geoserver
$i=0;
foreach ($caps->layers as $l) {
    if (isset($l['queryable'])) {          
      //Filter out layers which are similar to the one 
      if  (substr((isset($l['Name'])),0,$select_workspace_length)==$select_workspace) {
          
    echo "//".$l['BoundingBox']['SRS']."|"; 
?>    

           wms_layer_<?php echo $i; ?> = new OpenLayers.Layer.WMS("<?php echo isset($l['Title']) ?>",
                    "http://mapa.meioambiente.tl:8080/geoserver/wms?service=wms",
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
                    if (substr((isset($l['Name'])), 0, $select_workspace_length) == $select_workspace)
                        {
            ?>

                        <li><strong><?php echo isset($l['Title']) ?></strong> (<a href=http://mapa.meioambiente.tl:8080/geoserver/wms?service=WMS&version=1.1.0&request=GetMap&layers=<?php echo isset($l['Name']) ?>&styles=&bbox=500000.0,9565902.0,852305.875,9889125.0&width=512&height=469&srs=EPSG:21036&format=application/openlayers><?php
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
                                <tr><td colspan=2><a href="http://mapa.meioambiente.tl:8080/geoserver/ows?service=WFS&version=1.0.0&request=GetFeature&typeName=<?php echo isset($l['Name']) ?>&outputFormat=SHAPE-ZIP">Download shapefile</a></td></tr>
                                <tr><td><b>Legend&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td><td><b>Abstract</b></td></tr>
                                <tr><td><img src = "http://mapa.meioambiente.tl:8080/geoserver/wms?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=<?php echo isset($l['Name']) ?>"></td>

                                    <td>
                            <?php
                                        echo isset($l['Abstract'])                      ?>

                                        <br>
                                        Bounding box: (<i><?php echo
     isset($l['LatLonBoundingBox']['minx']) . ',' . isset($l['LatLonBoundingBox']['miny']) . ',' . isset($l['LatLonBoundingBox']['maxx'])
        . ',' . isset($l['LatLonBoundingBox']['maxy']); ?>)</i>    

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