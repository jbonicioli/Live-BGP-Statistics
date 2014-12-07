<?php
/*-----------------------------------------------------------------------------
* Live PHP Statistics                                                         *
*                                                                             *
* Main Author: Vaggelis Koutroumpas vaggelis@koutroumpas.gr                   *
* (c)2008-2014 for AWMN                                                       *
* Credits: see CREDITS file                                                   *
*                                                                             *
* This program is free software: you can redistribute it and/or modify        *
* it under the terms of the GNU General Public License as published by        * 
* the Free Software Foundation, either version 3 of the License, or           *
* (at your option) any later version.                                         *
*                                                                             *
* This program is distributed in the hope that it will be useful,             *
* but WITHOUT ANY WARRANTY; without even the implied warranty of              *
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the                *
* GNU General Public License for more details.                                *
*                                                                             *
* You should have received a copy of the GNU General Public License           *
* along with this program. If not, see <http://www.gnu.org/licenses/>.        *
*                                                                             *
*-----------------------------------------------------------------------------*/

//Check if Google Maps page is enabled
if ($CONF['GMAP_ENABLED'] != true){
	header ("Location: index.php");
	exit();
}

?>

					<script>
 						$(function() {				    	
	
							$("#livemap").gmap3({
  								map:{
  			 						action:'init',
    								options:{
      									center:[<?=$CONF['GMAP_DEFAULT_LAT'];?>,<?=$CONF['GMAP_DEFAULT_LON'];?>],
      									zoom: <?=$CONF['GMAP_DEFAULT_ZOOM'];?>,
      									mapTypeId: google.maps.MapTypeId.HYBRID
    								},
    								navigationControl: true,
     								scrollwheel: true,
     								streetViewControl: true
  								},

  	    						polyline:{
  	    	        				values: [
                    					<?  	   	   
										$SELECT_LINKS = mysql_query ("SELECT node1, node2, id, state FROM links WHERE node1 != '6076' AND node1 != '9134' AND node2 != '6076' AND node2 != '9134'   ", $db);
										while ($LINKS = mysql_fetch_array($SELECT_LINKS)){
											$SELECT_NODE1 = mysql_query ("SELECT lat, lon FROM nodes WHERE Node_id  = '$LINKS[node1]' ", $db);
											$NODE1 = mysql_fetch_array($SELECT_NODE1);
											$SELECT_NODE2 = mysql_query ("SELECT lat, lon FROM nodes WHERE Node_id  = '$LINKS[node2]' ", $db);
											$NODE2 = mysql_fetch_array($SELECT_NODE2);
											if ($NODE1['lat'] && $NODE1['lon'] && $NODE2['lat'] && $NODE2['lon']){
												if ($LINKS['state'] == 'up'){
													$color = '#00ff00';
												}else{
													$color = '#ff0000'; 	
												}
										?>
										{
											options:{
												strokeColor: "<?=$color;?>",
												strokeOpacity: 1.0,
												strokeWeight: 1,

												path:[
													[<?=$NODE1[lat];?>, <?=$NODE1[lon];?>],
													[<?=$NODE2[lat];?>, <?=$NODE2[lon];?>],
												]
											}
										},  	  
										<?}}?>
									],
								}, 

								marker:{
	                                values: [
                    					<?
	                                    $final_sql = "SELECT nodes.Node_id, nodes.Node_name, nodes.Node_area, nodes.lat, nodes.lon, links.node1, links.node2 FROM nodes, links WHERE ( nodes.Node_id = links.node1 OR nodes.Node_id = links.node2 ) AND ( nodes.Node_id != '6076' AND nodes.Node_id != '9134' AND links.node1 != '6076' AND links.node2 != '6076' AND links.node1 != '9431' AND links.node2 != '9431' ) GROUP BY nodes.Node_id";
										
										$SELECT_ROUTERS = mysql_query($final_sql, $db);
										if (mysql_num_rows($SELECT_ROUTERS)){
											// PRINT UP NODES
											$routers_qnt = mysql_num_rows($SELECT_ROUTERS);
											$i=0;
											while ($ROUTERS = mysql_fetch_array($SELECT_ROUTERS)){

												$LAT = $ROUTERS['lat']; 
												$LON = $ROUTERS['lon'];
												$COUNTRY = $ROUTERS['Node_area']; 
												$NODENAME = $ROUTERS['Node_name']; 
												$NODEID = $ROUTERS['Node_id']; 
												$i++;

												//revert to default lat/lon if none found
												if ($LAT == 0){
													$LAT = $CONF['GMAP_DEFAULT_LAT'];
												}
												if ($LON == 0){
													$LON = $CONF['GMAP_DEFAULT_LON'];
												}       	
											?>
											{
												latLng:[<?=$LAT;?>, <?=$LON;?>],
												options:{
													icon: "images/gmap/mm_20_green.png",
												},
												data:'<div align="left" style="width: 200px; height:80px;" >Node: <strong><span class="blue">#<?=$NODEID;?></span> <span class="green"><?=$NODENAME;?></span></strong><br />Area: <strong><?=$COUNTRY;?></strong><br /><br /><a href="http://<?=$CONF['WIND_DOMAIN'];?>/?page=nodes&node=<?=$NODEID;?>" title="Visit Node\'s WiND Page" target="_blank"><img src="./images/ico_wind.png" width="16" height="16" /></a> &nbsp; <a href="index.php?section=bgp_nodes_peers&nodeid=<?=$NODEID;?>" title="View this Node\'s Links" target="_blank"><img src="./images/nav_bgp_nodes_peers.png"/> &nbsp; <a href="index.php?section=bgp_prefixes&q=<?=$NODEID;?>" title="View this Node\'s announced prefixes" target="_blank"><img src="./images/nav_bgp_prefixes.png"/> </div>'
		                                    }					
		                                    <?
												if ($i < $routers_qnt){
													echo ",\n";
												}else{ 
													echo "\n";
												}
											}		
	                                    }
									?>     
									],

									events:{
										click: function(marker, event, context){
											var map = $(this).gmap3("get"),
											infowindow = $(this).gmap3({get:{name:"infowindow"}});
											if (infowindow){
												infowindow.open(map, marker);
												infowindow.setContent(context.data);
											} else {
												$(this).gmap3({
													infowindow:{
														anchor:marker,
														options:{content: context.data}
													}	
												});
											}
										}
									},			
	                            },

                    			autofit:{}
								
							});

						});  

					</script>

					<style>
					<!--
					.livemap{
						margin: -2px auto;
						border: 1px #C0C0C0;
						width: 100%;
						height: 450px;
						margin-bottom:4px;    
					}
                    /* cluster */
					.cluster{
						color: #FFFFFF;
						text-align:center;
						font-family: Verdana;
						font-size:14px;
						font-weight:bold;
						text-shadow: 0 0 2px #000;
						-moz-text-shadow: 0 0 2px #000;
						-webkit-text-shadow: 0 0 2px #000;
					}
					.cluster-1{
						background: url(images/gmap/m1.png) no-repeat;
						line-height:53px;
						width: 53px;
						height: 52px;
					}
					.cluster-2{
						background: url(images/gmap/m2.png) no-repeat;
						line-height:56px;
						width: 56px;
						height: 55px;
					}
					.cluster-3{
						background: url(images/gmap/m3.png) no-repeat;
						line-height:66px;
						width: 66px;
						height: 65px;
					}      
					-->
					</style>

                    <!-- ROUTERS MAP SECTION START -->
                    <div id="main_content">

						<!-- GMAP START -->
                        <fieldset>

							<legend>&raquo; Live Network Map View</legend>

							<table cellpadding="0" cellspacing="0" width="100%" >
								<tr>
									<td style="font-size:12px; text-align:center; width: 100%; height: 100%">
										<div id="livemap" class="livemap"></div>
									</td>
								</tr>
							</table>

						</fieldset>
                    	<!-- GMAP END -->

                    </div>    
                    <!-- ROUTERS MAP SECTION END --> 