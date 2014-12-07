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

if (!is_array($_GET['nodeid'])){
	$NODEID = intval($_GET['nodeid']);
}
if (!is_array($_GET['prevnode'])){
	$PREVNODEID = intval($_GET['prevnode']);
}

if ($NODEID > 0){
	$SELECT_NODE = mysql_query ("SELECT Node_name, Node_id, Node_area, `C-Class` AS CClass, Owner FROM nodes WHERE Node_id = '".$NODEID."' ", $db);
	$NODE = mysql_fetch_array($SELECT_NODE);
}

?>

					<style>
						<!--
						.custom-combobox {
							position: relative;
							display: inline-block;
						}
						.custom-combobox-toggle {
							position: absolute;
							top: 0;
							bottom: 0;
							margin-left: -1px;
							padding: 0;
							/* support: IE7 */
							*height: 1.7em;
							*top: 0.1em;
						}
						.custom-combobox-input {
							margin: 0;
							padding: 0.3em;
						}
						pre {
							font-weight: bold;
							color:#333;
							font-size: 13px;
							line-height: 18px;
							font-family: monospace;
						}
                    	.ui-autocomplete {
    						max-height: 300px;
    						overflow-y: auto;   /* prevent horizontal scrollbar */
    						overflow-x: hidden; /* add padding to account for vertical scrollbar */
    						z-index:1000 !important;
						}
						.title {
							font-family: Verdana;
							font-size: 14px;
							font-weight: bold;
							color: #000033;
						}
						.small {
							font-family: Verdana;
							font-size: 11px;
							color: #003366;
						}
                        .xsmall {
							font-family: Verdana;
							font-size: 9px;
							color: #333;
						}
                        .subtitle {
							font-family: Verdana;
							font-weight: bold;
							font-size: 11px;
							color: #003366;
						}
						-->						
                    </style>
					
					<script>
						(function( $ ) {
							$.widget( "custom.combobox", {
								_create: function() {
									this.wrapper = $( "<span>" )
									.addClass( "custom-combobox" )
									.insertAfter( this.element );
									this.element.hide();
									this._createAutocomplete();
									this._createShowAllButton();
								},
								_createAutocomplete: function() {
									var selected = this.element.children( ":selected" ),
									value = selected.val() ? selected.text() : "";
									this.input = $( "<input>" )
									.appendTo( this.wrapper )
									.val( value )
									.attr( "title", "" )
									.addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
									.autocomplete({
										delay: 0,
										minLength: 0,
										source: $.proxy( this, "_source" )
									})
									.tooltip({
										tooltipClass: "ui-state-highlight"
									});
									this._on( this.input, {
										autocompleteselect: function( event, ui ) {
											ui.item.option.selected = true;
											this._trigger( "select", event, {
												item: ui.item.option
											});
										},
										autocompletechange: "_removeIfInvalid"
									});
								},
								_createShowAllButton: function() {
									var input = this.input,
									wasOpen = false;
									$( "<a>" )
									.attr( "tabIndex", -1 )
									.attr( "title", "Show All Items" )
									.tooltip()
									.appendTo( this.wrapper )
									.button({
										icons: {
											primary: "ui-icon-triangle-1-s"
										},
										text: false
									})
									.removeClass( "ui-corner-all" )
									.addClass( "custom-combobox-toggle ui-corner-right" )
									.mousedown(function() {
										wasOpen = input.autocomplete( "widget" ).is( ":visible" );
									})
									.click(function() {
										input.focus();
										// Close if already visible
										if ( wasOpen ) {
											return;
										}
										// Pass empty string as value to search for, displaying all results
										input.autocomplete( "search", "" );
									});
								},
								_source: function( request, response ) {
									var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
									response( this.element.children( "option" ).map(function() {
										var text = $( this ).text();
										if ( this.value && ( !request.term || matcher.test(text) ) )
											return {
												label: text,
												value: text,
												option: this
											};
									}) );
								},
								_removeIfInvalid: function( event, ui ) {
									// Selected an item, nothing to do
									if ( ui.item ) {
										return;
									}
									// Search for a match (case-insensitive)
									var value = this.input.val(),
									valueLowerCase = value.toLowerCase(),
									valid = false;
									this.element.children( "option" ).each(function() {
										if ( $( this ).text().toLowerCase() === valueLowerCase ) {
											this.selected = valid = true;
											return false;
										}
									});
									// Found a match, nothing to do
									if ( valid ) {
										return;
									}
									// Remove invalid value
									this.input
									.val( "" )
									.attr( "title", value + " didn't match any item" )
									.tooltip( "open" );
									this.element.val( "" );
									this._delay(function() {
										this.input.tooltip( "close" ).attr( "title", "" );
									}, 2500 );
									this.input.data( "ui-autocomplete" ).term = "";
								},
								_destroy: function() {
									this.wrapper.remove();
										this.element.show();
								}
							});
						})( jQuery );


						$(function() {
							$( "#combobox" ).combobox();
							$( "#toggle" ).click(function() {
								$( "#combobox" ).toggle();
							});
							$('.ui-autocomplete-input').css('width','500px');
						});

                    </script>
                	
                	<!-- BGP NODES PEERS SECTION START -->
                    <div id="main_content">
                      
						<!-- LIST BGP NODES PEERS START -->
						<fieldset>

							<legend>&raquo; Node's BGP Peers</legend>

                        	<form name="search_form" action="index.php?section=<?=$SECTION;?>" method="get" class="search_form">
								<input type="hidden" name="section" value="<?=$SECTION;?>" />
								<table border="0" cellspacing="0" cellpadding="4">
									<tr>
										<td width="550">
											<div class="ui-widget">
												<select name="nodeid" id="combobox" class="select_box">
													<option value="">--Select--</option> 
													<? 
													$SELECT_NODES = mysql_query("SELECT Node_id, Node_name, Node_area, `C-Class` AS CClass, Owner FROM nodes INNER JOIN links ON nodes.Node_id = links.node1 OR nodes.Node_id = links.node2 GROUP BY Node_id ORDER BY Node_id ASC", $db);
													while ($SEARCH_NODES = mysql_fetch_array($SELECT_NODES)){
                                                		$search_cclasses = str_replace ("\n", " ", $SEARCH_NODES['CClass']);
	                                                ?>                                                    
													<option value="<?=$SEARCH_NODES['Node_id'];?>"   <? if ($_GET['nodeid'] == $SEARCH_NODES['Node_id']){ echo "selected=\"selected\""; }?> >#<?=$SEARCH_NODES['Node_id'];?> - <?=$SEARCH_NODES['Node_name'];?> - <?=$SEARCH_NODES['Owner'];?> - <?=$SEARCH_NODES['Node_area'];?> (<?=$search_cclasses;?>)</option>
													<?}?> 
												</select>
											</div>                                
										</td>
										<td><button type="submit"  >Search</button></td>
									</tr>
								</table>
								
								<?if ($PREVNODEID){?>
								<input name="prevnode" type="hidden" id="submit" value="<?=$PREVNODEID;?>">
								<?}?> 
							</form>                                                   

							<table width="80%" border="0" align="center" cellpadding="1" cellspacing="1">
								<tr>
									<td align="center">
										<?
										if ($NODEID > 0 && $NODEID != '6076' && $NODEID != '9134'){

                        					if ($NODE['CClass'] ==''){
												$NODE['CClass'] = '<font color=\'red\'>--</font>';
											}else{
												$NODE['CClass'] = nl2br($NODE['CClass']);		
											}

											$cclasses = array();
											$SELECT_CCLASSES = mysql_query("SELECT * FROM cclass WHERE Node_id = '".$NODEID."' AND state = 'up' ", $db);
											while ($CCLASSES = mysql_fetch_array($SELECT_CCLASSES)){
                                        		$cclasses[] = $CCLASSES['CClass'];
	                                        }

											$CCLASSES = implode ("\n", $cclasses);
	                                    ?>
										<br>
										<br>
										<br>
										<table width="80%" border="0" cellpadding="4" cellspacing="1">
											<tr>
												<td colspan="2" align="center" bgcolor="#E4E9E9" class="title">#<?=$NODEID;?><?if ($NODE['Node_name'] != ''){?> - <?=$NODE['Node_name'];?><?}?> <?if ($NODE['Node_area'] != ''){?><span class="small">(<?=$NODE['Node_area'];?>)</span><?}?></td>
											</tr>
											<tr>
												<td colspan="2" bgcolor="#F7F9F9" ><span class="subtitle"><font color="orange">Node Administrator:</font> <a href="http://<?=$CONF['WIND_DOMAIN'];?>/?page=nodes&node=<?=$NODEID;?>&subpage=contact" title="Contact Node administrator via WiND" target="_blank"><?=$NODE['Owner'];?></a></span></td>
					  						</tr>
											<tr>
												<td colspan="2" bgcolor="#F7F9F9" ><span class="subtitle"><font color="green">Assigned Prefixes (based on WiND):<br><?=$NODE['CClass'];?></font></span></td>
											</tr>
											<?
											$SELECT_NODE1 = mysql_query("SELECT * FROM links WHERE node1 = '".$NODEID."' GROUP BY node2 ORDER BY node2 ASC", $db);
											$SELECT_NODE2 = mysql_query("SELECT * FROM links WHERE node2 = '".$NODEID."' GROUP BY node1 ORDER BY node1 ASC", $db);
											if (mysql_num_rows($SELECT_NODE1) == 0 && mysql_num_rows($SELECT_NODE2) == 0 ){  
											?>
											<tr>
												<td colspan="2" bgcolor="#F7F9F9"><center><span class="subtitle"><font color="red">Non-Existant Node</font></span></center></td>
											</tr>
											<?}else{?>
											<tr>
												<td colspan="2" bgcolor="#F7F9F9" ><span class="subtitle"><font color="blue">Announced Prefixes:<br>
												<?=nl2br($CCLASSES);?></font></span></td>
											</tr>
											<tr>
												<td colspan="2" bgcolor="#F7F9F9"><span class="subtitle"><font color="red">Node Peers:</font></span></td>
											</tr>
											<?
											$NODES = FALSE;

                        					$i = 0;
											while ($NODE1 = mysql_fetch_array($SELECT_NODE1)){
												$SELECT_NODE_NAME1 = mysql_query ("SELECT Node_name, Node_area, `C-Class` AS CClass FROM nodes WHERE Node_id = '".intval($NODE1['node2'])."' ", $db);
												$NODE_NAME1 = mysql_fetch_array($SELECT_NODE_NAME1);
												$NODES[$i] = $NODE1['node2'];
												$STATE[$NODES[$i]] = $NODE1['state'];
												$NAMES[$NODES[$i]] = $NODE_NAME1['Node_name'];
												$AREAS[$NODES[$i]] = $NODE_NAME1['Node_area'];
												$CCLASS[$NODES[$i]] = $NODE_NAME1['CClass'];
												$TIME[$NODES[$i]] = $NODE1['date'];
												$i++;
											}
											while ($NODE2 = mysql_fetch_array($SELECT_NODE2)){
												$SELECT_NODE_NAME2 = mysql_query ("SELECT Node_name, Node_area, `C-Class` AS CClass FROM nodes WHERE Node_id = '".intval($NODE2['node1'])."' ", $db);
												$NODE_NAME2 = mysql_fetch_array($SELECT_NODE_NAME2);
												$NODES[$i] = $NODE2['node1'];
												$STATE[$NODES[$i]] = $NODE2['state'];
												$NAMES[$NODES[$i]] = $NODE_NAME2['Node_name'];
												$AREAS[$NODES[$i]] = $NODE_NAME2['Node_area'];
												$CCLASS[$NODES[$i]] = $NODE_NAME2['CClass'];
												$TIME[$NODES[$i]] = $NODE2['date'];
												$i++;
											}
											//  echo "<!--";
											//  print_r ($NODES);
											//  echo "-->";
											if ($i > 0){
												$NODES = array_values(array_unique($NODES));
												//  echo "<!--";
												//  print_r ($NODES);
												//  echo "-->";
												$o=0;				
												for ($i = 0; $i <= count($NODES); $i++) {
													if ($NODES[$i] && $NODES[$i] != '6076' && $NODES[$i] != '9134'){
														$o++;
											?>
											<tr>
												<td width="5" align="left"  bgcolor="<?if ($PREVNODEID == $NODES[$i]){ echo "#E4E9E9"; }else{echo "#FCFCFC"; }?>"><?=$o;?></td>
												<td width="117" align="left" bgcolor="<?if ($PREVNODEID == $NODES[$i]){ echo "#E4E9E9"; }else{echo "#FCFCFC"; }?>" >
												
													<table width="100%" border="0" cellspacing="1" cellpadding="1">
														<tr>
															<td align="left" width="20" nowrap> 
																<?
                                                            	if ($STATE[$NODES[$i]] == 'up'){
																	echo "<img src='./images/ico_up.png' border='0' align='absmiddle'>";
																}else{
																	echo "<img src='./images/ico_down.png' border='0' align='absmiddle'>";
					  											}
																?>
															</td>
															<td align="left" nowrap>
																<b><span class="title">#<?=$NODES[$i];?><?if ($NAMES[$NODES[$i]]){echo " - ". $NAMES[$NODES[$i]];}?></span></b>
																<?
																if ($NODES[$i] >=$CONF['WIRELESS_COMMUNITY_MAX_ASN']){ 
																	echo '<span class="small"> (Wireless Community outside of '.$CONF['WIRELESS_COMMUNITY_NAME'].')</span>';
																}elseif ($AREAS[$NODES[$i]] != ''){
																?> 
																<span class="small"> (<?=$AREAS[$NODES[$i]];?>)</span>
																<?}?>
															
																<?
                                                                if ($NODES[$i] <=$CONF['WIRELESS_COMMUNITY_MAX_ASN']){
                                                                	if ($CCLASS[$NODES[$i]] ==''){
																		$CCLASS[$NODES[$i]] = '<font color=\'red\'>--</font>';
																	}else{
																		$CCLASS[$NODES[$i]] = str_replace ("\n", ", ", $CCLASS[$NODES[$i]] );		
																	}
																	echo "&nbsp;<span class='small'><font color='green'>C-Class: <strong>".$CCLASS[$NODES[$i]]."</strong> </font></span>";
                                                                }		
																
																$PREPENDS = '';
																$SELECT_PREPENDS = mysql_query ("SELECT id FROM prepends WHERE nodeid = '".$NODES[$i]."' AND parent_nodeid = '".$NODEID."' AND state = 'up'  ",$db);
																$PREPENDS_DETECTED = mysql_num_rows($SELECT_PREPENDS);
																if ($PREPENDS_DETECTED){ 
																	$PREPENDS = "&nbsp;&nbsp;&nbsp;<span class='small'><font color='red'>(Prepends Detected)</font></span>";
																}

																echo "<span class='xsmall'><br>Last status change: <strong>". sec2hms($TIME[$NODES[$i]], time()) . "</strong> ". $PREPENDS ."</span>";     
																?>
															</td>
															<td align="right" nowrap>
																&nbsp;&nbsp;&nbsp;&nbsp;
																<?         
																if ($PREVNODEID == $NODES[$i]){
																	echo "<a href='javascript:history.go(-1);'><img src='./images/ico_back.png' border='0' class=\"tip_south\" title='Return to previous Node'></a>  &nbsp; &nbsp; ";
																}
						
																$SELECT_NODE_ROUTERS = mysql_query("SELECT id FROM routers_db.routers WHERE NodeID = '".$NODES[$i]."' AND Active = '1' AND Status  = 'up' ORDER BY id ASC LIMIT 0, 1", $db2);
																if (mysql_num_rows($SELECT_NODE_ROUTERS)){
																	$ROUTER = mysql_fetch_array($SELECT_NODE_ROUTERS);
																	echo "<a href='http://".$CONF['BGP_LOOKING_GLASS_NG_DOMAIN']."/index.php?section=lg&bgp_router=".$ROUTER['id']."&bgp_command=1&arguements=' class=\"tip_south\" title=\"View Node's BGP Routing Table on ".$CONF['BGP_LOOKING_GLASS_NG_DOMAIN']."\" target='_blank'><img src='./images/nav_bgp.png' width='16' height='16' /></a> &nbsp; &nbsp; ";
																}
						
																if ($PREPENDS_DETECTED){
																	echo "<a href='index.php?section=bgp_prepends&q=".$NODES[$i]."' class=\"tip_south\" title=\"View this Node's Prepends\"><img src='./images/nav_bgp_prepends.png' width='16' height='16' /></a> &nbsp; &nbsp; ";
																}
																
																echo "<a href='index.php?section=bgp_prefixes&q=".$NODES[$i]."' class=\"tip_south\" title=\"View this Node's Announced Prefixes\"><img src='./images/nav_bgp_prefixes.png' width='16' height='16' /></a> &nbsp; &nbsp; ";
                                                                echo "<a href='http://".$CONF['WIND_DOMAIN']."/?page=nodes&node=".$NODES[$i]."' target='_blank' class=\"tip_south\" title=\"Visit Node's WiND Page\"><img src='./images/ico_wind.png' /></a> &nbsp; &nbsp; ";
                               									echo "<a href='index.php?section=".$SECTION."&nodeid=".$NODES[$i]."&search=1&prevnode=".$NODEID."' class=\"tip_south\" title=\"View this Node's Links \"><img src='./images/nav_bgp_nodes_peers.png' width='16' height='16' /></a> &nbsp; &nbsp; ";
																?>
															</td>
														</tr>
													</table>
												</td>
											</tr>
											<?}}}?>
											<?}?>
										</table>
										<?}?>
									</td>
								</tr>
							</table>                          

						</fieldset>
                        <!-- LIST BGP NODES PEERS END -->
                      
                    </div>    
                    <!-- BGP NODES PEERS SECTION END --> 