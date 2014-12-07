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

require("includes/config.php");
require("includes/functions.php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$CONF['APP_NAME'];?> | <?=$maintitle_title;?> | <?=$_SERVER['HTTP_HOST'];?></title>
<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
<!-- INCLUDE STYLES & JAVASCRIPTS -->
<link href="./includes/css.php" rel="stylesheet" type="text/css"  media="screen" />
<script type="text/javascript" src="./includes/js.php"></script>
<?if( $SECTION == 'bgp_map' && $CONF['GMAP_ENABLED'] == true){?>                               
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
<script type="text/javascript" src="includes/jquery/gmap3.js"></script>
<?}?> 
<!-- INCLUDE STYLES & JAVASCRIPTS END -->

</head>

<body>

	<!-- NO JAVASCRIPT NOTIFICATION START -->
	<noscript>
	<div class="maintitle_nojs">This site needs Javascript enabled to function properly!</div>
	</noscript>
	<!-- NO JAVASCRIPT NOTIFICATION END -->

	<div id="wrapper">

		<!-- HEADER START -->
		<div id="header">
			<div id="logo<?if (is_file("./images/logo.custom.png")){?>_custom<?}?>">
				<a href="index.php"><span><?=$CONF['APP_NAME'];?></span></a>
			</div>
		</div>

		<!-- MENU START -->
		<div id="navigation">
			<!-- MAIN MENU START -->
			<ul>
				<li class="menu_home" ><a href="index.php" <? if ($SECTION=='' || !$SECTION) echo " class=\"selected\""; ?>><span>Dashboard</span></a></li>
				<li class="menu_bgp_nodes_peers" ><a href="index.php?section=bgp_nodes_peers" title="Live Node's BGP Peers (Links)" <? if ($SECTION=='bgp_nodes_peers' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='bgp_nodes_peers' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='bgp_nodes_peers' && staff_help()){?>class="tip_south"<?}?> ><span>Nodes BGP Peers</span></a></li>
				<li class="menu_bgp_peers" ><a href="index.php?section=bgp_peers" title="Total BGP Peers List" <? if ($SECTION=='bgp_peers' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='bgp_peers' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='bgp_peers' && staff_help()){?>class="tip_south"<?}?> ><span>BGP Peers</span></a></li>
				<li class="menu_bgp_prefixes" ><a href="index.php?section=bgp_prefixes" title="Announced BGP Prefixes List" <? if ($SECTION=='bgp_prefixes' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='bgp_prefixes' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='bgp_prefixes' && staff_help()){?>class="tip_south"<?}?> ><span>BGP Prefixes</span></a></li>
				<li class="menu_bgp_prepends" ><a href="index.php?section=bgp_prepends" title="BGP Prepends" <? if ($SECTION=='bgp_prepends' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='bgp_prepends' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='bgp_prepends' && staff_help()){?>class="tip_south"<?}?> ><span>BGP Prepends</span></a></li>
				<li class="menu_bgp_illegal_prefixes" ><a href="index.php?section=bgp_illegal_prefixes" title="Wrong Prefix Announcements" <? if ($SECTION=='bgp_illegal_prefixes' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='bgp_illegal_prefixes' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='bgp_illegal_prefixes' && staff_help()){?>class="tip_south"<?}?> ><span>Bad Announcements</span></a></li>
				<?if ($CONF['GMAP_ENABLED'] == true){?>
				<li class="menu_bgp_map" ><a href="index.php?section=bgp_map" title="Live BGP Network Map" <? if ($SECTION=='bgp_map' && staff_help() ){?>class="tip_south selected"<?}elseif($SECTION=='bgp_map' && !staff_help() ){?>class="selected"<?}elseif($SECTION!='bgp_map' && staff_help()){?>class="tip_south"<?}?> ><span>BGP Live Map</span></a></li>
				<?}?>
				<?if ($CONF['BGP_LOOKING_GLASS_NG_DOMAIN']){?>
				<li class="menu_bgp_lg" ><a href="http://<?=$CONF['BGP_LOOKING_GLASS_NG_DOMAIN'];?>" title="BGP Looking Glass NG" target="_blank" class="tip_south"><span>BGP Looking Glass</span></a></li>
				<?}?>
				<?if ($CONF['WIND_DOMAIN']){?>
				<li class="menu_wind" ><a href="http://<?=$CONF['WIND_DOMAIN'];?>" title="WiND Database for <?=$CONF['WIRELESS_COMMUNITY_NAME'];?>" target="_blank" class="tip_south"><span>WiND</span></a></li>
				<?}?>
			</ul>
			<!-- MAIN MENU END -->
		</div>
		<!-- MENU END -->

        <div class="clr">&nbsp;</div>
		<!-- HEADER END -->
        
		<!-- MAIN START --><br />
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<!-- SIDEBAR START -->
				<td valign="top" id="sidebar">
					<h2 class="sidebar_title">Quick Search</h2>   	        

					<form method="GET" action="index.php">
						<input type="text" name="nodeid" id="nodeidsearch"  title="Enter Node ID" value="Enter Node ID" size="15" />
						<input type="hidden" name="section" value="bgp_nodes_peers" />
						<button type="submit"  >Show BGP Peers</button>
					</form>
					<br />
					<br />

					<h2 class="sidebar_title">BGP Statistics</h2>
					<?
					$SELECT_LINKS = mysql_query("SELECT 1 FROM links WHERE state = 'up' ", $db);
					$LINKS = mysql_num_rows($SELECT_LINKS);

					$LINKS_DOWN = array();
					$SELECT_LINKS_DOWN1 = mysql_query("SELECT node1 FROM links WHERE state = 'down' ", $db);
					while ($LINKS_DOWN1 = mysql_fetch_array($SELECT_LINKS_DOWN1)){
						$LINKS_DOWN[] = $LINKS_DOWN1['node1'];
					}
					$SELECT_LINKS_DOWN2 = mysql_query("SELECT node2 FROM links WHERE state = 'down' ", $db);
					while ($LINKS_DOWN2 = mysql_fetch_array($SELECT_LINKS_DOWN2)){
						$LINKS_DOWN[] = $LINKS_DOWN2['node2'];
					}
					$LINKS_DOWN = array_unique($LINKS_DOWN);

					$NODES = array();
					$SELECT_NODES1 = mysql_query("SELECT node1 FROM links WHERE state = 'up' ", $db);
					while ($NODES1 = mysql_fetch_array($SELECT_NODES1)){
						$NODES[] = $NODES1['node1'];
					}
					$SELECT_NODES2 = mysql_query("SELECT node2 FROM links WHERE state = 'up' ", $db);
					while ($NODES2 = mysql_fetch_array($SELECT_NODES2)){
						$NODES[] = $NODES2['node2'];
					}

					$NODES = array_unique($NODES);

					$SELECT_LAST_UPDATE = mysql_query("SELECT `date` FROM links WHERE 1 ORDER BY `date` DESC LIMIT 0,1", $db);
					$LAST_UPDATE = mysql_fetch_array($SELECT_LAST_UPDATE);

					$SELECT_ROUTERS = mysql_query("SELECT 1 FROM routers_db.routers WHERE Status = 'up' AND Active='1' AND Stats ='1' ", $db2);
					$ROUTERS = mysql_num_rows($SELECT_ROUTERS);

					$SELECT_ROUTERS_ALL = mysql_query("SELECT 1 FROM routers_db.routers ", $db2);
					$ROUTERS_ALL = mysql_num_rows($SELECT_ROUTERS_ALL);

					$SELECT_CLASS = mysql_query("SELECT DISTINCT(CClass)  FROM cclass WHERE state='up' ", $db);
					$CLASS = mysql_num_rows($SELECT_CLASS);

					$SELECT_CLASS_ALL = mysql_query("SELECT CClass FROM cclass WHERE 1", $db);
					$CLASS_ALL = mysql_num_rows($SELECT_CLASS_ALL);

					$SELECT_PREPENDS = mysql_query("SELECT 1 FROM prepends WHERE state='up' ", $db);
					$PREPENDS = mysql_num_rows($SELECT_PREPENDS);

					$SELECT_PREPENDS_ALL = mysql_query("SELECT 1 FROM prepends WHERE 1", $db);
					$PREPENDS_ALL = mysql_num_rows($SELECT_PREPENDS_ALL);
					?>

					<table width="80%" border="0" cellspacing="2" cellpadding="2" align="right">
						<tr>
							<td align="right" nowrap="nowrap" height="25" class="smalltahoma">Total AS (Nodes) <img src="images/ico_bgp.png" align="top"></td>
							<td class="smalltahoma"><strong><?=count($NODES);?></strong></td>
						</tr>
						<tr>
							<td align="right" nowrap="nowrap" height="25" class="smalltahoma">Peers (Active/Inactive)  <img src="images/nav_bgp_peers.png" align="top"></td>
							<td class="smalltahoma"><strong><?=$LINKS;?>/<?=count($LINKS_DOWN);?></strong></td>
						</tr>
						<tr>
							<td align="right" nowrap="nowrap" height="25" class="smalltahoma">Prefixes (Live/Total) <img src="images/nav_bgp_prefixes.png" align="top"></td>
							<td class="smalltahoma"><strong><?=$CLASS;?>/<?=$CLASS_ALL;?></strong></td>
						</tr>
						<tr>
							<td align="right" nowrap="nowrap" height="25" class="smalltahoma">Prepends  (Active/Inactive) <img src="images/nav_bgp_prepends.png" align="top"></td>
							<td class="smalltahoma"><strong><?=$PREPENDS;?> / <?=$PREPENDS_ALL;?></strong></td>
						</tr>
						<tr>
							<td align="right" nowrap="nowrap" height="25" class="smalltahoma">Routers Collecting Data <img src="images/ico_routers.png" align="top"></td>
							<td class="smalltahoma"><strong><?=$ROUTERS;?>/<?=$ROUTERS_ALL;?></strong></td>
						</tr>
						<tr>
							<td align="center" nowrap="nowrap" height="25" colspan="2" class="smalltahoma"><h2 class="sidebar_title"></h2></td>
						</tr>                  
						<tr>
							<td align="center" nowrap="nowrap" height="25" colspan="2" class="smalltahoma">Last link state change:<br /><?=date("F j, Y, g:i a", $LAST_UPDATE['date']);?><br /><?=sec2hms($LAST_UPDATE['date'], time());?></td>
						</tr>                  
						<?if ($CONF['SIDEBAR_BGP_LG_NG_URL']){?>
						<tr>
							<td align="center" nowrap="nowrap" height="25" colspan="2" class="smalltahoma"><?=$CONF['SIDEBAR_BGP_LG_NG_URL'];?></td>
						</tr>
						<?}?>                  
						<?if ($CONF['SIDEBAR_SUPPORT_URL']){?>
						<tr>
							<td align="center" nowrap="nowrap" height="25" colspan="2" class="smalltahoma"><?=$CONF['SIDEBAR_SUPPORT_URL'];?></td>
						</tr>
						<?}?>                  
					</table>

				</td>
				<!-- SIDEBAR END -->
				
				<td class="main_content_spacer"></td>
				<td valign="top" id="main">

					<div class="maintitle_bg">
						<div class="<?=$maintitle_class;?>"><a href="index.php?section=<?=$SECTION;?>"><?=$maintitle_title;?></a></div>
					</div>    

					<? 
					if (!$SECTION){
						if (file_exists('dashboard.php')) {
							include "dashboard.php";
						}else{
							include "dashboard.php.dist";
						}
					}

					if ($SECTION && preg_match('!^[\w @.-]*$!', $SECTION)) {
						if (file_exists($SECTION.'.php')) {
							include $SECTION.'.php';    
						}else{ 
							header("Location: index.php"); 
							exit; 
						}
					}
					?>        
				</td>
			</tr>
		</table>
		<!-- MAIN END -->

	</div>

	<!-- FOOTER START -->
	<div id="footer">
		<span style="float:right"><?=$CONF['FOOTER_TEXT'];?></span> <a href="https://github.com/Cha0sgr/Live-BGP-Statistics" target="_blank">Live BGP Statistics</a>
	</div>
	<!-- FOOTER END -->

</body>
</html>
<? 
$buffer = ob_get_clean(); 
ob_start("ob_gzhandler"); 
echo $buffer;
?>