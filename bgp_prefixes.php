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
$sorting_array = array("id", "Node_id", "CClass", "state", "date", "Seenby");

// ----------------------------------------------------------------------

$action_title = "All BGP Prefixes"; 

$search_vars = "";

$q = mysql_real_escape_string($_GET['q'], $db);
if ($q) { 
	$search_vars .= "&q=$q"; 
	$action_title = "Search: " . $q;
}

$p = mysql_real_escape_string($_GET['p'], $db);
if ($p) { 
	$search_vars .= "&p=$p"; 
	$action_title = "Search: " . $p;
}

if (isset($_GET['search_state'])) {
	$s = mysql_real_escape_string($_GET['search_state'], $db); 
	$search_vars .= "&search_state=$s"; 
}else{
	$s = 'up';
	$_GET['search_state'] = 'up';
}

if ($q && $p){
	$search_query = "WHERE ( $mysql_table.Node_id = '$q' AND $mysql_table.CClass LIKE '%$p%') AND $mysql_table.state LIKE '%$s%'  ";
}elseif ($q){
	$search_query = "WHERE $mysql_table.Node_id = '$q' AND $mysql_table.state LIKE '%$s%'  ";
}elseif ($p){
	$search_query = "WHERE $mysql_table.CClass LIKE '%$p%' AND $mysql_table.state LIKE '%$s%'  ";
}else{
	$search_query = "WHERE $mysql_table.state LIKE '%$s%'  ";		
}


// Sorting
if (isset($_GET['sort'])){
	if (in_array($_GET['sort'], $sorting_array)) {
		if ($_GET['by'] !== "desc" && $_GET['by'] !== "asc") {
			$_GET['by'] = "desc";
		}
		$order = "ORDER BY `". mysql_escape_string($_GET['sort']) ."` ". mysql_escape_string($_GET['by']) . " ";
	}
} else {
	$order = "ORDER BY `date` DESC ";
	$_GET['sort'] = "date";
	$_GET['by'] = "desc";
}
$sort_vars = "&sort=".$_GET['sort']."&by=".$_GET['by'];


// Paging
$count = mysql_query("SELECT id FROM $mysql_table $search_query",$db);
$items_number  = mysql_num_rows($count);
if ($_GET['items_per_page'] && is_numeric($_GET['items_per_page'])){
	$_SESSION['items_per_page'] = $_GET['items_per_page'];
}
if ($_POST['items_per_page'] && is_numeric($_POST['items_per_page'])){
	$_SESSION['items_per_page'] = $_POST['items_per_page'];
}
if (isset($_SESSION['items_per_page']) && is_numeric($_SESSION['items_per_page'])){
	$num = $_SESSION['items_per_page'];
} else { 
	$_SESSION['items_per_page'] = $CONF['ADMIN_ITEMS_PER_PAGE'];
	$num = $CONF['ADMIN_ITEMS_PER_PAGE'];     
}
$e = $num;
$pages = $items_number/$num;
if (!$_GET['pageno']){
	$pageno = 0; 
}else{
	$pageno = $_GET['pageno'];
}
if (isset($_POST['goto'])) {
	if ($_POST['goto'] <= $pages + 1) {
		$pageno = $num * ($_POST['goto'] - 1);
	} else {
		$pageno = 0;
	}
}
$current_page = 0;
for($i=0;$i<$pages;$i++){
	$y=$i+1;
	$page=$i*$num;
	if ($page == $pageno){
		$current_page = $y;
	}
} 
$total_pages=$i; // sinolo selidon

//Final Query for records listing
$SELECT_RESULTS  = mysql_query("SELECT `".$mysql_table."`.* FROM `".$mysql_table."` ".$search_query." ".$order." LIMIT ".$pageno.", ".$e ,$db);
$url_vars = "action=".$_GET['action'] . $sort_vars . $search_vars;

$q = htmlspecialchars($q);
$p = htmlspecialchars($p);
$search_vars = htmlspecialchars($search_vars);
$url_vars = htmlspecialchars($url_vars);

?>

					<!-- BGP PREFIXES SECTION START -->
					<div id="main_content">

                    	<!-- LIST BGP PREFIXES START -->
                        <fieldset>
                                
							<legend>&raquo; BGP Prepends List</legend>

							<form name="search_form" action="index.php?section=<?=$SECTION;?>" method="get" class="search_form">
								<input type="hidden" name="section" value="<?=$SECTION;?>" />
								<table border="0" cellspacing="0" cellpadding="4">
									<tr>
										<td>AS Number Filter:</td>
										<td><input type="text" name="q" id="search_field_q" class="input_field" value="<?=$q?>" /></td>

										<td>Prefix Filter:</td>
										<td><input type="text" name="p" id="search_field_p" class="input_field" value="<?=$p?>" /></td>

										<td>Prefix State:</td>
										<td>
											<select name="search_state" class="select_box">
												<option value="">Any state</option> 
												<option value="up"   <? if ($_GET['search_state'] == 'up'){   echo "selected=\"selected\""; }?> >Prefix Announced (Up)</option>
												<option value="down" <? if ($_GET['search_state'] == 'down'){ echo "selected=\"selected\""; }?> >Prefix Not Announced (Down)</option>
											</select>
										</td>

                                        <td><button type="submit"  >Search</button></td>
									</tr>
								</table> 
							</form>

							<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:15px; margin-top: 15px;">
								<tr>
									<td width="36%" height="30">
										<h3 style="margin:0"><?=$action_title;?> <? if ($q) { ?><span style="font-size:12px"> (<a href="index.php?section=<?=$SECTION;?>" class="tip_south" title="Clear search">x</a>)</span><? } ?></h3> 
									</td>
									<td width="28%" align="center">
										<? if ($items_number) { ?>
										Total Records: <span id="total_records"><?=$items_number?></span>
										<? } ?>
									</td>
									<td width="36%"><? if ($items_number) { include "includes/paging.php"; } ?></td>
								</tr>
							</table>                            

							<table width="100%" border="0" cellspacing="2" cellpadding="5">
								<tr>
									<th><?=create_sort_link("CClass","Prefix");?></th>
									<th><?=create_sort_link("Node_id","AS Number (NodeID)");?></th>
									<?/*<th><?=create_sort_link("Seenby","First Seen By");?></th>*/?>
									<th><?=create_sort_link("date","Last Status Change");?></th>
									<th><?=create_sort_link("state","Prefix Announced");?></th>
								</tr>
								<!-- RESULTS START -->
								<?
								$i=-1;
								while($LISTING = mysql_fetch_array($SELECT_RESULTS)){
								$i++;

                            	$SELECT_NODE1 = mysql_query("SELECT * from nodes WHERE Node_id = '".$LISTING['Node_id']."' ", $db);
								$NODE1 = mysql_fetch_array($SELECT_NODE1);
								/*
								$SELECT_NODE2 = mysql_query("SELECT * from nodes WHERE Node_id = '".$LISTING['Seenby']."' ", $db);
								$NODE2 = mysql_fetch_array($SELECT_NODE2);
								*/
								?>      
								<tr onmouseover="this.className='on' " onmouseout="this.className='off' " id="tr-<?=$LISTING['id'];?>">
									<td align="center" nowrap><?=$LISTING['CClass'];?></td>
									<td align="center" nowrap><a href="index.php?section=bgp_nodes_peers&nodeid=<?=$LISTING['Node_id'];?>" title="Show #<?=$LISTING['Node_id'];?> <?=$NODE1['Node_name'];?> Node Peers" class="<?if (staff_help()){?>tip_south<?}?>">#<?=$LISTING['Node_id'];?> <?=$NODE1['Node_name'];?></a></td>
									<?/*<td align="center" nowrap><a href="index.php?section=bgp_nodes_peers&nodeid=<?=$LISTING['Seenby'];?>" title="Show #<?=$LISTING['Seenby'];?>  <?=$NODE2['Node_name'];?>Node Peers" class="<?if (staff_help()){?>tip_south<?}?>">#<?=$LISTING['Seenby'];?> <?=$NODE2['Node_name'];?></a></td>*/?>
									<td align="center" nowrap ><?=sec2hms($LISTING['date'], time());?></td>
									<td align="center" nowrap ><a href="javascript:void(0)" class="<?if (staff_help()){?>tip_south<?}?> <? if ($LISTING['state'] == 'up') { ?>enabled<? } else { ?>disabled<? } ?>" title="Prepend is: <?=strtoupper($LISTING['state']);?>"><span>Prepend is: <?=strtoupper($LISTING['state']);?></span></a></td>
                            	</tr>
								<?}?>
                                <!-- RESULTS END -->
							</table>

							<? if (!$items_number) { ?>
							<div class="no_records">No records found</div>
							<? } ?>

							<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin:10px 0">
								<tr>
									<td width="36%" height="30">
										<? include "includes/items_per_page.php"; ?>
									</td>
									<td width="28%">&nbsp;</td>
									<td width="36%"> 
										<? if ($items_number) { include "includes/paging.php"; } ?>
									</td>
								</tr>
							</table>

						</fieldset>
                        <!-- LIST BGP PREFIXES END -->
                        
                	</div>    
                    <!-- BGP PREFIXES SECTION END --> 