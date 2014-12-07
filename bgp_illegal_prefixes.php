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

//Define current page data
$mysql_table = 'cclass';
  
?>

					<!-- ILLEGAL PREFIXES SECTION START -->
                    <div id="main_content">
                      
						<!-- LIST ILLEGAL PREFIXES START -->
						<fieldset>

							<legend>&raquo; Illegal Prefixes List</legend>

                        	<div class="monospace">
								<strong>Colors legend:</strong>
								<br/>
								<br/>
								<strong class="green">Green</strong>:&nbsp; The officially assigned prefix(es) to a Node by the <a href="http://<?=$CONF['WIND_DOMAIN'];?>" target="_blank">WiND Database</a>.
								<br />
								<strong class="red">Red</strong>:&nbsp;&nbsp;&nbsp; Announced Prefixes not assigned by WiND for that particular Node.
								<br />
								<strong class="orange">Orange</strong>: The Prefix is assigned to a Node which belongs to the same owner (not technically a problem).
								<br />
								<strong class="brown">Brown</strong>:&nbsp; The Prefix is assigned to another Node than the one announcing it (potentially malicious).
								<br />
								<strong class="blue">Blue</strong>:&nbsp;&nbsp; The Prefix is not assigned to any node, by WiND.
								<br />
								<br />
							</div>


							<table width="100%" border="0" cellspacing="2" cellpadding="5">
								<tr>
									<th>Node ID</th>
									<th>Assigned Prefixes</th>
									<th>Illegal Prefixes</th>
									</tr>
									<!-- RESULTS START -->
									<?
									$SELECT_RESULTS  = mysql_query("SELECT `".$mysql_table."`.* FROM `".$mysql_table."` WHERE `state` = 'up' GROUP BY Node_id ",$db);
									while($LISTING = mysql_fetch_array($SELECT_RESULTS)){

									$NODEID = $LISTING['Node_id'];

									$SELECT_NODE = mysql_query("SELECT `C-Class`, Node_name FROM nodes WHERE Node_id = '".$NODEID."' ", $db);
									$NODE = mysql_fetch_array($SELECT_NODE);

									$NODENAME = $NODE['Node_name'];

									$cclasses = array();
									$SELECT_CCLASSES = mysql_query("SELECT CClass FROM cclass WHERE Node_id = '".$NODEID."' AND state = 'up' ", $db);
									while ($CCLASSES = mysql_fetch_array($SELECT_CCLASSES)){
										$cclasses[] = $CCLASSES['CClass'];
									}

									$cclasses2 = array();
									$node_classes_expl = explode("\n", $NODE['C-Class']);
									foreach( $node_classes_expl as $node_classes_part ){
										if ($node_classes_part){
											$cclasses2[] = trim($node_classes_part) . "/24";
										}
									}

									$class = array_diff ($cclasses, $cclasses2);

									//clear non /24 prefixes
									foreach($class as $key => $one) {
										if(strpos($one, '/24') != true){
											unset($class[$key]);
										}
									}


									if ($class && (int)$NODEID < 22000  ){
									//if ($class && (int)$NODEID != 8580 && (int)$NODEID < 39999  ){
									//echo "Node: #". $NODEID . " " . $NODENAME . "<br>\n";
									//print_r($class);

									$SELECT_NODE_OWNER = mysql_query("SELECT Owner FROM nodes WHERE Node_id = '".$NODEID."' ", $db);
									$NODE_OWNER = mysql_fetch_array($SELECT_NODE_OWNER);

									?>      
									<tr onmouseover="this.className='on' " onmouseout="this.className='off' ">
										<td nowrap><b>#<?=$NODEID;?><?if ($NODENAME){echo " - ". $NODENAME;}?></b> <font color='orange'>(<?=trim($NODE_OWNER['Owner']);?>)</font></td>
										<td nowrap>
											<b><?
											foreach( $cclasses2 as $nodevalidcclass ){
												echo  "<font color='green'>" . $nodevalidcclass  . "</font><br>";						
											}
											?></b>                        
										</td>
										<td nowrap >
											<b><?
											foreach( $class as $nodecclass ){
												$SELECT_OWNER = mysql_query("SELECT Owner, Node_id, Node_name FROM nodes WHERE `C-Class` LIKE '%".str_replace ("/24", "", $nodecclass)."%' ", $db);
												$OWNER = mysql_fetch_array($SELECT_OWNER);
												if ($OWNER['Owner']){

													if (trim($NODE_OWNER['Owner']) == trim($OWNER['Owner'])){
														$color = 'orange';
													}else{
														$color = 'brown';
													}
													$real_class_owner = "<font color='".$color."'>(#".$OWNER['Node_id']." - ".$OWNER['Node_name']." - ".trim($OWNER['Owner']).")</font>"; 
												}else{
													$real_class_owner = "<font color='blue'>(unassigned)</font>";
												}
												echo  "<font color='red'>" . $nodecclass . "</font> ".$real_class_owner."<br>";						
											}
											?></b>                        
										</td>

									</tr>
									<?}}?>
                                    <!-- RESULTS END -->
								</table>

							</fieldset>
                        	<!-- LIST ILLEGAL PREFIXES END -->

                        </div>    
                        <!-- ILLEGAL PREFIXES SECTION END --> 