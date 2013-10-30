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
$select_workspace=isset($_GET['ws']);



//Do the default workspace if the request returns empty
if (empty($select_workspace))
    {
    $select_workspace="inon";
    }

$select_workspace = $select_workspace.":";

include("include/wms-parser.php");

//Calculate the workspace length for use later
$select_workspace_length=strlen($select_workspace);

$nombre_archivo         ="http://wms.dirnat.no/geoserver/ows?service=wms&version=1.1.1&request=GetCapabilities";
$gestor                 =fopen($nombre_archivo, "rb");
$contenido              =stream_get_contents($gestor);
fclose ($gestor);

$caps                   =new CapabilitiesParser();
$caps->parse($contenido);
$caps->free_parser();

 

//Here the HTML code starts
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Spatial data from the Geoserver workspace: <?php echo $select_workspace; ?></title>
                                                                                           
        <link rel = "stylesheet" href = "style.css" type = "text/css">      
        <style type="text/css">
       .more {
          display: none;
          border-top: 1px solid #666;
          border-bottom: 1px solid #666; }
       a.showLink, a.hideLink {
          text-decoration: none;
          color: #36f;
          padding-left: 8px;
          background: transparent url(down.gif) no-repeat left; }
       a.hideLink {
          background: transparent url(up.gif) no-repeat left; }
       a.showLink:hover, a.hideLink:hover {
          border-bottom: 1px dotted #36f; }
        </style>

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

        
    </head>

    <body>


        <ol>
            <?php
            $i=1;

            //Make a list of the layers
            foreach ($caps->layers as $l)
                {
                if (isset($l['queryable']))
                    {
                    if (substr(($l['Name']), 0, $select_workspace_length) == $select_workspace)
                        {
                            ?>

                        <li><table><tr><td width=400><b><?php echo $l['Title'] ?></b></td><td>(<a href = http://wms.dirnat.no/geoserver/ows?service=wms&version=1.1.1&request=GetMap&layers=<?php echo $l['Name'] ?>&styles=&bbox=<?php echo $l['LatLonBoundingBox']['minx'] . ',' . $l['LatLonBoundingBox']['miny'] . ',' . $l['LatLonBoundingBox']['maxx'] . ',' . $l['LatLonBoundingBox']['maxy']; ?>&width=512&height=469&srs=32630&format=application/openlayers><?php
    echo $l['Name'] ?></a>)</td></tr></table>
    <a href = "#" id = "<?php echo ($i-1) ?>-show" class = "showLink"
                           onclick = "showHide('<?php echo ($i-1) ?>');return false;"> <img src="graphics/btn_open.gif" border="0" width="19" height="25" alt="Open"></a>

                        <div id = "<?php echo ($i-1) ?>"
                             class = "more"><a href = "#"
                                               id = "<?php echo ($i-1) ?>-hide"
                                               class = "hideLink"
                                               onclick = "showHide('<?php echo ($i-1) ?>');return false;"><img src="graphics/btn_close.gif" border="0" width="19" height="25" alt="Close"></a>

                            <br>
                            <table>
                                <tr><td colspan=2><a href="http://wms.dirnat.no/geoserver/ows?service=wms&version=1.1.1&request=GetFeature&typeName=<?php echo isset($l['Name']) ?>&outputFormat=SHAPE-ZIP">Download shapefile</a></td></tr>
                                <tr><td><b>Legend&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b></td><td><b>Abstract</b></td></tr>
                                <tr><td><img src = "http://wms.dirnat.no/geoserver/ows?service=wms&version=1.1.1&request=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&LAYER=<?php echo $l['Name'] ?>"></td>

                                    <td>
                            <?php
                                        echo isset($l['Abstract'])                      ?>

                                        <br>
                                        Bounding box: (<i><?php echo
    $l['LatLonBoundingBox']['minx'] . ',' . $l['LatLonBoundingBox']['miny'] . ',' . $l['LatLonBoundingBox']['maxx']
        . ',' . $l['LatLonBoundingBox']['maxy']; ?>)</i>
                        </div>

                        </td>

                        </tr>

                        </table>

                        <br/>

                        </div></li>

            <?php
                        $i++;
                        }
                    }
                }
            ?>
        </ol>
    </body>
</html>