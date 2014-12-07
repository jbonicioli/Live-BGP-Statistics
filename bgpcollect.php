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
require("includes/functions_bgpcollect.php");

//Include SSH Libs for mikrotik
include('Net/SSH2.php');


//Fill Nodes Table with Node ID + Node Name from WiND
//include("/var/www/html/links/xml2array.php");

// Run on endless loop
while (1){

	// BEGIN GATHERING DATA FROM ROUTERS
    $SELECT_ROUTERS = mysql_query("SELECT NodeName, RouterName, NodeID, Ip, Type, Pass, User, Port FROM `routers_db`.`routers` WHERE Status = 'up' AND Active = '1' AND Stats = '1' ORDER BY id ASC", $db2);
	$ROUTERS_TOTAL = mysql_num_rows($SELECT_ROUTERS);
	$RO = 0;

	// If min_routers are healthy proceed   
	if ($ROUTERS_TOTAL >= $CONF['BGP_COLLECT_MIN_ROUTERS']){

		echo logtime() . " Truncating temporary tables for fresh data...";
		mysql_query ( "TRUNCATE TABLE links_temp ", $db);
		mysql_query ( "TRUNCATE TABLE cclass_temp ", $db);
		mysql_query ( "TRUNCATE TABLE prepends_temp ", $db);

		while ($ROUTERS = mysql_fetch_array($SELECT_ROUTERS)){

			$RO++;
			echo  "\n\n" . logtime() . " [ROUTER ".$RO."/".$ROUTERS_TOTAL."] ->Reading BGP Table from router #" . $ROUTERS['NodeID'] . " ". $ROUTERS['NodeName'] . " - ".$ROUTERS['RouterName'] ." Type: ".$ROUTERS['Type'] . " (".$ROUTERS['Ip'].":".$ROUTERS['Port'] .")\n";

			//$router["title"]    = $ROUTERS['RouterName'];
			$router["address"]  = $ROUTERS['Ip'];
			$router["services"] = $ROUTERS['Type'];
			$router["password"] = $ROUTERS['Pass'];
			$router["port"] 	= $ROUTERS['Port'];
			$router["user"] 	= $ROUTERS['User'];

			echo logtime() . " [BGP] -> Reading ROUTER ASN from " . $ROUTERS['RouterName'] . " (".$ROUTERS['Ip'].")...\n";
			if ($ROUTERS['Type'] == 'mikrotik'){
				$ROUTERAS = routerAS_from_ip($router, true);
			}else{
				$ROUTERAS = routerAS_from_ip($router, false);			
			}
			if ($ROUTERAS){
				echo logtime() ." [BGP] -> Got ". $ROUTERAS ."!\n";
			}else{
				echo "NOT OK :-(\n";
			}

			//GET BGP TABLE FROM ROUTER
			if ($ROUTERAS){                 
				echo logtime() . " [BGP] -> Reading BGP Routing Table from #" . $ROUTERAS . "...\n";
				$BGPLINES = bgppaths2array($router);
				if ($BGPLINES){
					echo logtime() ." [BGP] -> BGPLINES RECEIVED IS OK!\n";
				}else{
					echo logtime() ." [BGP] -> BGPLINES RECEIVED IS NOT OK :-(\n";
				}
				echo logtime() . " [BGP] -> Got Data, going to processing...\n";
				//print_r ($BGPLINES);

				$m = 0;
				$routerlabel = 'local router ID is ';

				//Extract BGP AS_PATH from each prefix
				for ($n=0;$n<count($BGPLINES);$n++) {
					$buffer = $BGPLINES[$n];
					$rpos = strpos($buffer,$routerlabel);
					if ($rpos===false) {}else{
						$rp = $rpos + strlen($routerlabel);
						$rl = strlen($buffer) - $rp1;
						$routerid = trim(substr($buffer,$rp,$rl));
					}

					$pos = strpos($buffer,'Network');
					if ($pos===false) {}else{
						$npos = strpos($buffer,'Network');
						$hpos = strpos($buffer,'Next Hop');
						$mpos = strpos($buffer,'Metric');
						//$lpos = strpos($buffer,'LocPrf');
						//$wpos = strpos($buffer,'Weight');
						$ppos = strpos($buffer,'Path');
					}

					if ($buffer[0]=='*') {
						$NextHop = trim(substr($buffer, $hpos, $mpos-$hpos));
						if (($NextHop=='0.0.0.0')||($NextHop=='')) {}else{
							//$data[$m]->Network = trim(substr($buffer, $npos, $hpos-$npos));
							$data[$m]['prefix'] = trim(substr($buffer, $npos, $hpos-$npos));
							//$data[$m]['Metric']  = trim(substr($buffer, $mpos, $lpos-$mpos));
							//$data[$m]['LocPrf']  = trim(substr($buffer, $lpos, $wpos-$lpos));
							//$data[$m]['Weight']  = trim(substr($buffer, $wpos, $ppos-$wpos));
							$data[$m]['pathstr'] = trim(substr($buffer, $ppos));
							$m++;
						}
					}
				}

			}else{
				echo logtime() . " [ROUTER] -> Router looks down. Skipping...\n";		
			}

			// PROCESS GATHERED DATA FROM ROUTER 
			for ( $i=0; $i< count ($data); $i++ )  {
				//echo $data[$i]['pathstr'] . "\n\n";

				$ases = explode (" ", $data[$i]['pathstr'] );

				//Add network prefix to DB
				$NETWORK_AS = as_announcer_from_as_path($data[$i]['pathstr'], $ROUTERAS);
				if ($data[$i]['prefix'] && $NETWORK_AS ){
					ad2dbcclass ($NETWORK_AS, $data[$i]['prefix'], $ROUTERAS, TRUE);
					ad2tempdbcclass ($NETWORK_AS, $data[$i]['prefix']);
				}

				$CONFED = FALSE;

				$PREFIX_ASES = count($ases);
				for ( $e=0; $e< $PREFIX_ASES; $e++ )  {

					$ep1 = $e + 1;
					$em1 = $e - 1;

					if ($PREFIX_ASES <= 30){
						if (( $ases[$e] != 'i' && $ases[$e] != 'e' && $ases[$e] != '?' && $ases[$e] != '' ) ){

							//DETECT 1 HOP LINKS FIRST
							if ( ($ases[$ep1]  == 'i'  || $ases[$ep1] == 'e' || $ases[$ep1] == '?' ) && ( $ases[$em1] == '' || $ases[$em1] == '0' ) ){

								$CONFED = detect_confed($ases[$e], 'start', FALSE);
								if ($CONFED == FALSE){
									add2db($ROUTERAS, $ases[$e], TRUE);
									add2tempdb($ROUTERAS, $ases[$e], TRUE);
								}else{
									//echo  logtime() . " ---> IN CONFED - Ignoring AS ".$ases[$e]."\n";
								}
								$CONFED = detect_confed($ases[$e], 'end', FALSE);

							//DETECT THE REST OF THE LINKS
							}elseif ($ases[$ep1]  != '') {

								$CONFED = detect_confed($ases[$e], 'start', FALSE);
								if ($CONFED == FALSE){
									$IS_PREPEND = FALSE;
									$PREPEND_CHECK = detect_prepends($e, $ep1, $ases, FALSE, FALSE, $ROUTERAS);
									if ($PREPEND_CHECK === 'NOPREPEND' ){
										if ( ( $ases[$e] == 'i' || $ases[$e] == 'e' || $ases[$e] == '' || $ases[$e] == '?'  || $ases[$e] == '0' ) || ( $ases[$ep1] == 'i' || $ases[$ep1] == 'e' || $ases[$ep1] == '' || $ases[$ep1] == '?' || $ases[$ep1] == '0' ) ){}else{
											add2db($ases[$e], $ases[$ep1], $ROUTERAS, TRUE);
											add2tempdb($ases[$e], $ases[$ep1], TRUE);
										}
									}
								}else{
									//echo  logtime() . " ---> IN CONFED - Ignoring AS ".$ases[$e]."\n";
								}
								$CONFED = detect_confed($ases[$e], 'end', FALSE);

							}
						}
					}
				}
			}

			//reset vars just in case :P
			$data     = FALSE;
			$BGPLINES = FALSE;
			$ases     = FALSE;
		
		}

		
		//DISABLE DOWNED LINKS 
		$SQL = "SELECT links.id, links.node1, links.node2, links.state
				FROM links LEFT JOIN links_temp ON ( (links.node1 = links_temp.node1 OR links.node1 = links_temp.node2)  AND  (links.node2 = links_temp.node2 OR links.node2 = links_temp.node1))
				WHERE links.state ='up' AND (links_temp.node1 IS NULL OR links_temp.node2 IS NULL)";
		$SELECT  = mysql_query($SQL, $db);
		if (mysql_num_rows($SELECT) ){
			echo  logtime() . "\n -> DISABLING NON ACTIVE LINKS.\n";
			$t = 0;
			while ($DAT = mysql_fetch_array($SELECT)){
				$IDs[$t] =  $DAT['id'];
				echo  logtime() . " ---> Link ".$DAT['node1'] . "-" . $DAT['node2']." is set to be disabled.\n";
				$t++;
			}
			mysql_query("UPDATE links SET state = 'down', `date` = UNIX_TIMESTAMP ( ) WHERE id IN (".join (",", $IDs).")", $db);
			echo  logtime() . "---> Disabled non active links.\n\n";
		}

		//DISABLE DOWNED C-CLASSES 
		$SQL = "SELECT cclass.id, cclass.Node_id, cclass.CClass, cclass.state
				FROM cclass LEFT JOIN cclass_temp ON ( (cclass.Node_id = cclass_temp.Node_id OR cclass.Node_id = cclass_temp.CClass)  AND  (cclass.CClass = cclass_temp.CClass OR cclass.CClass = cclass_temp.Node_id))
				WHERE cclass.state ='up' AND (cclass_temp.Node_id IS NULL OR cclass_temp.CClass IS NULL)";
		$SELECT  = mysql_query($SQL, $db);
		if (mysql_num_rows($SELECT) ){
			echo  logtime() . "\n -> DISABLING NON ACTIVE C-CLASSES.\n";
			$t = 0;
			while ($DAT = mysql_fetch_array($SELECT)){
				$IDs[$t] =  $DAT['id'];
				echo  logtime() . " ---> C-Class ".$DAT['CClass'] . " from #" . $DAT['Node_id']." is set to be disabled.\n";
				$t++;
			}
			mysql_query("UPDATE cclass SET state = 'down', `date` = UNIX_TIMESTAMP ( ) WHERE id IN (".join (",", $IDs).")", $db);
			echo  logtime() . "---> Disabled non active c-classes.\n\n";
		}

		//DISABLE DOWNED PREPENDS 
		$SQL = "SELECT prepends.id, prepends.nodeid, prepends.parent_nodeid, prepends.state
				FROM prepends LEFT JOIN prepends_temp ON ( (prepends.nodeid = prepends_temp.nodeid OR prepends.nodeid = prepends_temp.parent_nodeid)  AND  (prepends.parent_nodeid = prepends_temp.parent_nodeid OR prepends.parent_nodeid = prepends_temp.nodeid))
				WHERE prepends.state ='up' AND (prepends_temp.nodeid IS NULL OR prepends_temp.parent_nodeid IS NULL)";
		$SELECT  = mysql_query($SQL, $db);
		if (mysql_num_rows($SELECT) ){
			echo  logtime() . "\n -> DISABLING NON ACTIVE PREPENDS.\n";
			$t = 0;
			while ($DAT = mysql_fetch_array($SELECT)){
				$IDs[$t] =  $DAT['id'];
				echo  logtime() . " ---> PREPEND ".$DAT['nodeid'] . " - " . $DAT['parent_nodeid']." is set to be disabled.\n";
				$t++;
			}
			mysql_query("UPDATE prepends SET state = 'down', `date` = UNIX_TIMESTAMP ( ) WHERE id IN (".join (",", $IDs).")", $db);
			echo  logtime() . "---> Disabled non active prepends.\n\n";
		}

		//DELETE DOWNED LINKS OLDER THAN 30 DAYS
		mysql_query("DELETE FROM links WHERE date <= '".(time()-2592000)."' AND state = 'down' ", $db);
		
		//DELETE DOWNED C-CLASS OLDER THAN 30 DAYS
		mysql_query("DELETE FROM cclass WHERE date <= '".(time()-2592000)."' AND state = 'down' ", $db);

		//DELETE DOWNED PREPENDS OLDER THAN 30 DAYS
		mysql_query("DELETE FROM prepends WHERE date <= '".(time()-2592000)."' AND state = 'down' ", $db);

		
		echo  "\n" . logtime() . " ---> DATA GATHERING COMPLETE!\n\n\n";

	}

	//Wait for 1 second before starting again
	sleep (1);

}
?>