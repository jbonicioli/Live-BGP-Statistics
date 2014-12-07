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

//Grab BGP Routing table from Router and put it in an Array
function bgppaths2array ($router, $GETAS=FALSE, $PRINT=TRUE){
	global $db;

	if ($router['services'] == 'mikrotik'){
		
		//MIKROTIK ROUTER MODE
		if ($GETAS == TRUE){
			$AS = mikrotik_get_AS($router["address"], $router["user"], $router["password"], $router["port"]);
			if ($AS){
				return $AS;
			}else{
				return false;
			}
		}else{
			$BGPLINES = mikrotik_get_bgp_table($router["address"], $router["user"], $router["password"], $router["port"]);
		}

    }else{

    	//FALLBACK TO QUAGGA MODE
		$password = $router['password'];
		$address = $router['address'];
		$port = $router['port'];

		if ($GETAS == TRUE){
			$command = "show ip bgp summary";
		}else{
			$command = "show ip bgp";
		}

		//$command = $request . (!empty ($argument) ? (" " . safeOutput ($argument)) : "");
		$link = fsockopen ($address, $port, $errno, $errstr, 5);
		if (!$link){
			//printError ("Error connecting to router");
			if ($PRINT == TRUE){
				echo  logtime() . " [BGP] ->\t Error connecting to router: ".$errstr."\n\n";
			}
			return;
		}else{
			echo  logtime() . " [BGP] ->\t CONNECTED OK! \n";		
		}

		//socket_set_timeout ($link, 5);
		stream_set_blocking ($link, TRUE);
		stream_set_timeout ($link, 5);

		//$username = $router[$routerid]["username"];
		//if (!empty ($username)) fputs ($link, "{$username}\n");
		
		fputs ($link, "{$password}\nterminal length 0\n{$command}\n");
		echo  logtime() . " [BGP] ->\t SENT COMMAND $command\n";

		if ($GETAS == FALSE){
			//send many 'enters' to telnet in case the shell is interactive and waits for --more--
			//pretty LAME way to do this... needs to be fixed.
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');
			fputs ($link, "\n");
			usleep ('20000');

			echo  logtime() . " [BGP] ->\t SENT MANY ENTERS\n";

			// let daemon print bulk of records uninterrupted
			//if (empty ($argument) && $request > 0) 
			sleep (3);
		}

		fputs ($link, "quit\n");
		echo  logtime() . " [BGP] ->\t SENT QUIT\n";

        while (!feof ($link)) $readbuf = $readbuf . fgets ($link, 256);

		echo  logtime() . " [BGP] ->\t GOT BUFFER\n";

		fclose ($link);

		echo  logtime() . " [BGP] ->\t DISCONNECED OK!\n";

		//print_r($readbuf);
		$start = strpos ($readbuf, $command);
		$len = strpos ($readbuf, "quit") - $start;
		//while ($readbuf[$start + $len] != "\n") $len--;

		$DATA = substr($readbuf, $start, $len);
		//print_r($DATA);
		//$DATA = $readbuf; 
		//echo $DATA;

		echo  logtime() . " [BGP] ->\t FORMATED AND RETURNING BGP ROUTING DATA\n";

		$DATAparts = explode ("\n", $DATA);
		//print_r($DATAparts);

		if ($DATAparts){
			return $DATAparts;
		}else{
			return false;
		}

	}	

    
	//print_r ($BGPLINES);
	if ($BGPLINES){
		return $BGPLINES;
	}else{
		return FALSE;
	}

}


// Utility function to print date/time before each output
function logtime (){
	return "[" . date("M d H:i:s") . "]";
}


function detect_prepends ($AS1, $AS2, $AS_PATH, $IS_PREPEND, $PRINT, $ROUTERAS){
	global $db, $IS_PREPEND;
	//echo "NUMBER: " . $i ."\n";
	if ($AS_PATH[$AS1] == $AS_PATH[$AS2]){
        $ASM1 = $AS1 - 1;
		$IS_PREPEND = TRUE;
		detect_prepends ($ASM1, $AS2, $AS_PATH, $IS_PREPEND, $PRINT, $ROUTERAS);
    }elseif ($IS_PREPEND == TRUE){
    	
    	if ($AS1 == '-1'){
			$AS_PATH[$AS1] = $ROUTERAS;
		}

		if ($PRINT == TRUE){
			echo  logtime() . " -->PREPEND DETECTED - Ignoring Link ". $AS_PATH[$AS1+1] ."-". $AS_PATH[$AS2] ." originated by ". $AS_PATH[$AS1] ."\n";
		}

		add2dbprepends($AS_PATH[$AS2], $AS_PATH[$AS1], TRUE);
		add2tempdbprepends($AS_PATH[$AS2], $AS_PATH[$AS1], FALSE);
		
		return "PREPEND";

	}else{
        return 'NOPREPEND';
	}

}

//Utility Function to detect wheather the ASes to come are inside a BGP Confederation.
function detect_confed ($AS, $MODE, $PRINT=FALSE){
	global $CONFED;

	if ($MODE == 'start'){
 		if (strstr($AS, '(')){
			if ($PRINT == TRUE){
				echo  logtime() . " --->CONFED STARTED!\n";
			}
			return TRUE;
		}else{
			return $CONFED;
		}
 	}

	if ($MODE == 'end'){
 		if (strstr($AS, ')')){
			if ($PRINT == TRUE){
				echo  logtime() . " --->CONFED END!\n";
			}
			return FALSE;
		}else{
			return $CONFED;
		}
 	}

}


//Utility Function to INSERT or UPDATE database.
function add2db ($AS1, $AS2, $ROUTERAS, $PRINT=FALSE){
	global $db;

	if ($AS1 == $AS2){
		echo  "\n\n" .logtime() . " SOMETHING WENT BAD! $AS1 - $AS2 shouldn't be sent here!!!\n\n";
	}

	if (!is_numeric($AS1)){
		return false;
	}

	if (!is_numeric($AS2)){
		return false;
	}

	$SELECT_LINK = mysql_query ("SELECT id FROM links WHERE node1 = '" . $AS1 ."' AND node2 = '" . $AS2 ."'", $db);
	$SELECT_LINK_DOWN = mysql_query ("SELECT id FROM links WHERE node1 = '" . $AS1 ."' AND node2 = '" . $AS2 ."' AND state = 'down' ", $db);
    if (mysql_num_rows($SELECT_LINK) == 0 ){
        if (mysql_query (  "INSERT INTO links  ( node1, node2, `date`, state, active, byrouter ) VALUES ( '" . $AS1 ."', '" . $AS2 . "', UNIX_TIMESTAMP( ), 'up', '1', '".$ROUTERAS."' )", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " Link '" . $AS1 ."-" . $AS2 . "' successfuly inserted.\n";
			}
		}
    }elseif (mysql_num_rows($SELECT_LINK_DOWN) > '0'){
		if (mysql_query (  "UPDATE links  SET  `date` = UNIX_TIMESTAMP( ), state='up', byrouter=".$ROUTERAS." WHERE node1 = '" . $AS1 ."' AND node2 = '" . $AS2 ."' ", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " Link '" . $AS1 ."-" . $AS2 . "' successfuly updated.\n";
			}
		}
	}

	$SELECT_LINK_DOWN = mysql_query ("SELECT id FROM links WHERE node1 = '" . $AS2 ."' AND node2 = '" . $AS1 ."' AND state = 'down' ", $db);
    if (mysql_num_rows($SELECT_LINK_DOWN) > '0'){
        if (mysql_query (  "UPDATE links  SET  `date` = UNIX_TIMESTAMP( ), state='up', byrouter=".$ROUTERAS."  WHERE node1 = '" . $AS2 ."' AND node2 = '" . $AS1 ."' ", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " Link '" . $AS2 ."-" . $AS1 . "' successfuly updated.\n";
			}
		}
    }
}

//Utility Function to INSERT or UPDATE database.
function add2tempdb ($AS1, $AS2, $PRINT=FALSE){
	global $db;
	mysql_query (  "INSERT INTO links_temp  ( node1, node2) VALUES ( '" . $AS1 ."', '" . $AS2 . "' )", $db);
}



//Utility Function to INSERT or UPDATE database.
function add2dbprepends ($NODEID, $PARENTNODEID, $PRINT=FALSE){
	global $db;

	if (!is_numeric($NODEID)){
		return false;
	}

	if (!is_numeric($PARENTNODEID)){
		return false;
	}

	$SELECT_LINK = mysql_query ("SELECT id FROM prepends WHERE nodeid = '" . $NODEID ."' AND parent_nodeid = '" . $PARENTNODEID ."'", $db);
	$SELECT_LINK_DOWN = mysql_query ("SELECT id FROM prepends WHERE nodeid = '" . $NODEID ."' AND parent_nodeid = '" . $PARENTNODEID ."' AND state = 'down' ", $db);
    if (mysql_num_rows($SELECT_LINK) == 0 ){
    	if (mysql_query (  "INSERT INTO prepends  ( nodeid, parent_nodeid, `date`, state) VALUES ( '" . $NODEID ."', '" . $PARENTNODEID . "', UNIX_TIMESTAMP( ), 'up')", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . "PREPEND '" . $NODEID ."-" . $PARENTNODEID . "' successfuly inserted.\n";
			}
		}
    }elseif (mysql_num_rows($SELECT_LINK_DOWN) > '0'){
		if (mysql_query (  "UPDATE prepends  SET  `date` = UNIX_TIMESTAMP( ), state='up'  WHERE nodeid = '" . $NODEID ."' AND parent_nodeid = '" . $PARENTNODEID ."' ", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " PREPEND '" . $NODEID ."-" . $PARENTNODEID . "' successfuly updated.\n";
			}
		}
    }

	$SELECT_LINK_DOWN = mysql_query ("SELECT id FROM prepends WHERE nodeid = '" . $NODEID ."' AND parent_nodeid = '" . $PARENTNODEID ."' AND state = 'down' ", $db);
	if (mysql_num_rows($SELECT_LINK_DOWN) > '0'){
		if (mysql_query (  "UPDATE prepends  SET  `date` = UNIX_TIMESTAMP( ), state='up'  WHERE nodeid = '" . $NODEID ."' AND parent_nodeid = '" . $PARENTNODEID ."' ", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " PREPEND '" . $NODEID ."-" . $PARENTNODEID . "' successfuly updated.\n";
			}
		}
	}
}

//Utility Function to INSERT or UPDATE database.
function add2tempdbprepends ($NODEID, $PARENTNODEID, $PRINT=FALSE){
	global $db;
	mysql_query (  "INSERT INTO prepends_temp  ( nodeid, parent_nodeid) VALUES ( '" . $NODEID ."', '" . $PARENTNODEID . "' )", $db);
}


//Utility Function to INSERT or UPDATE database.
function ad2dbcclass ($AS, $CCLASS, $SEENBY, $PRINT=FALSE){
	global $db;

	if (!is_numeric($AS)){
		return false;
	}

	$SELECT_LINK = mysql_query ("SELECT id FROM cclass WHERE Node_id = '" . $AS ."' AND CCLass = '".$CCLASS."' ", $db);
	$SELECT_LINK_DOWN = mysql_query ("SELECT id FROM cclass WHERE Node_id = '" . $AS ."' AND state = 'down' AND CCLass = '".$CCLASS."' ", $db);
	if (mysql_num_rows($SELECT_LINK) == 0 ){
		if (mysql_query (  "INSERT INTO cclass  ( Node_id, CClass, `date`, state, Seenby ) VALUES ( '" . $AS ."', '" . $CCLASS . "', UNIX_TIMESTAMP( ), 'up', '".$SEENBY."' )", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " C-Class " . $CCLASS . " from #" . $AS ." successfuly inserted.\n";
			}
		}
	}elseif (mysql_num_rows($SELECT_LINK_DOWN) > '0'){
		if (mysql_query (  "UPDATE cclass  SET  `date` = UNIX_TIMESTAMP( ), state='up', Seenby = '".$SEENBY."' WHERE Node_id = '" . $AS ."' AND CCLass = '".$CCLASS."' ", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " C-Class ".$CCLASS." from #" . $AS ." successfuly updated.\n";
			}
		}
	}

	$SELECT_LINK_DOWN = mysql_query ("SELECT id FROM cclass WHERE Node_id = '" . $AS ."' AND CClass = '" . $CCLASS ."' AND state = 'down' ", $db);
	if (mysql_num_rows($SELECT_LINK_DOWN) > '0'){
		if (mysql_query (  "UPDATE cclass  SET  `date` = UNIX_TIMESTAMP( ), state='up', Seenby='".$SEENBY."'  WHERE Node_id = '" . $AS ."' AND CClass = '" . $CCLASS ."' ", $db)){
			if ($PRINT == TRUE){
				echo  logtime() . " C-Class '" . $CCLASS ." from #" . $AS . "' successfuly updated.\n";
			}
		}
	}	
}

//Utility Function to INSERT or UPDATE database.
function ad2tempdbcclass ($AS, $CCLASS, $PRINT=FALSE){
	global $db;
	mysql_query (  "INSERT INTO cclass_temp  ( Node_id, CClass) VALUES ( '" . $AS ."', '" . $CCLASS . "' )", $db);
}



//GET AS for GIVEN ROUTER
function routerAS_from_ip($IP, $mtik=false){
	$ROUTERAS = bgppaths2array($IP, TRUE, FALSE);
	if ($mtik == false){
		//print_r($ROUTERAS);
		$ROUTERAS = $ROUTERAS[1];
		$ROUTERAS_EXPL = explode (" ", $ROUTERAS);
		$ROUTERAS =  $ROUTERAS_EXPL[count($ROUTERAS_EXPL)-1];
	}

	return trim($ROUTERAS);
}

//GET ANNOUNCER AS FROM AS PATH
function as_announcer_from_as_path ($PATH, $ROUTERAS){
	$AS = FALSE;
	$path_expl = explode(" ", $PATH);
	$path_expl = array_reverse($path_expl);
	if ($path_expl[0] == 'i' || $path_expl[0] == '?' || $path_expl[0] == 'e'){
		if ($path_expl[1] != '' ){
			$AS = $path_expl[1];
		}else{
			$AS = $ROUTERAS;			
		}
		return $AS;
	}  
}


// MIKROTIK FUNCTIONS

function ssh_client2($IP,$PORT,$USER,$PASS,$COMMAND){

	$ssh = new Net_SSH2($IP, $PORT);
	if (!$ssh->login($USER, $PASS)) {
		return false;
	}

	if ($data = $ssh->exec($COMMAND) ){
		return $data;
	}else{
		return false;
	}

	$ssh->disconnect();	

} 

function mikrotik_get_AS ($IP, $USER, $PASS, $PORT){

	$SSH = ssh_client2($IP,$PORT,$USER,$PASS,"/routing bgp instance print where name=default");
	if ($SSH){
		$BGP_INSTANCE = explode(" ", $SSH);
		
		$AS = FALSE;

		foreach($BGP_INSTANCE as $options){
        	if ($AS == FALSE && strstr($options, "as=") ){
				$AS = str_replace("as=", "", $options );		
			}
		}

		return $AS;
	}else{
		return false;
	}
}	

function mikrotik_get_routerID ($IP, $USER, $PASS, $PORT){

	$SSH = ssh_client2($IP,$PORT,$USER,$PASS,"/routing bgp instance print where name=default");
	if ($SSH){
		$BGP_INSTANCE = explode(" ", $SSH);

		$RID = FALSE;

		foreach($BGP_INSTANCE as $options){
        	if ($RID  == FALSE && strstr($options, "router-id=") ){
				$RID  = str_replace("router-id=", "", $options) ;		
			}           	
		}

		return $RID;

	}else{
		return false;
	}
}


function mikrotik_get_bgp_table ($IP, $USER, $PASS, $PORT){

	if (!$ssh = new Net_SSH2($IP, $PORT)){
		return "<font color=red><code><strong>SSH Error: Cannot Connect.</strong></code></font><br>\n";
	}
	$ssh->setTimeout(20);
	if (!$ssh->login($USER, $PASS)) {
		return "<font color=red><code><strong>SSH Wrong User/Pass.</strong></code></font><br>\n";
	}
	$SSH  = $ssh->exec("/ip route print terse");
	$SSH2 = $ssh->exec("/routing bgp instance print where name=default");

	$BGP_INSTANCE = explode(" ", $SSH2);

	$RID = FALSE;
	$AS = FALSE;

	foreach($BGP_INSTANCE as $options){
    	if ($RID  == FALSE && strstr($options, "router-id=") ){
			$RID  = str_replace("router-id=", "", $options) ;		
		}
		if ($AS == FALSE && strstr($options, "as=") ){
			$AS = str_replace("as=", "", $options );		
		}
    }


	//print_r($SSH);
	if ($SSH && $RID){

		$SSH = explode ("\n", $SSH);

		//MAKE RESULTS QUAGGA STYLE FORMATTED

		$BGPLINES[] = "show ip bgp";
		$BGPLINES[] = "BGP table version is 0, local router ID is $RID";
		$BGPLINES[] = "Status codes: s suppressed, d damped, h history, * valid, > best, i - internal";
		$BGPLINES[] = "Origin codes: i - IGP, e - EGP, ? - incomplete";
		$BGPLINES[] = "";
		$BGPLINES[] = "   Network           Next Hop              Metric LocPrf Weight Path";
		
		$bgpcount = 0;

		for ($i = 0; $i <= count($SSH); $i++) {

			if ($SSH != ''){

				$ARRAY = explode(" ", str_replace ("  ", " ", $SSH[$i]));
				//print_r($ARRAY);

				//echo key (preg_grep("/^dst-address=.*/", $ARRAY) );
				if ($ARRAY[0] == ''){ 
					$STATUS 	= $ARRAY[2];
				}else{
					$STATUS 	= $ARRAY[1];
				}

				$NETWORK 	= str_replace("dst-address=", "", $ARRAY[ key (preg_grep("/^dst-address=.*/", $ARRAY) ) ] );
				$NEXTHOP 	= str_replace("gateway=", "", $ARRAY[key (preg_grep("/^gateway=.*/", $ARRAY) ) ] );
				$AS_PATH 	= str_replace("bgp-as-path=", "", str_replace (",", " ", $ARRAY[key (preg_grep("/^bgp-as-path=.*/", $ARRAY) ) ] ));
				$ORIGIN 	= str_replace("bgp-origin=", "", $ARRAY[key (preg_grep("/^bgp-origin=.*/", $ARRAY) ) ] );
				
				if ($ORIGIN == 'igp'){
					$ORIGIN = 'i';
				}

				if ($ORIGIN == 'egp'){
					$ORIGIN = 'e';
				}

				if ($ORIGIN == 'incomplete'){
					$ORIGIN = '?';	
				}

	            $BGP_STATUS = FALSE;
				$IS_BGP = FALSE;
				if ( $STATUS == "ADb" ){
					$BGP_STATUS = "*>";
					$IS_BGP = TRUE;		
				}
				if ( $STATUS == "Db" ){
					$BGP_STATUS = "*";
					$IS_BGP = TRUE;		
				}

				if ($AS_PATH == ''){
					$AS_PATH_SEP = '';
				}else{
					$AS_PATH_SEP = ' ';						
				}

				$BGP_STATUS = sprintf("%-2s", $BGP_STATUS);
				$NETWORK    = sprintf("%-18s", $NETWORK);
				$NEXTHOP    = sprintf("%-45s", $NEXTHOP);

				if ($IS_BGP == TRUE){ 
					$BGPLINES[] = $BGP_STATUS ." " . $NETWORK . $NEXTHOP . " 0 " . $AS_PATH . $AS_PATH_SEP . $ORIGIN;
					$bgpcount++;
				}
			}
		}

		$BGPLINES[] = " ";
		$BGPLINES[] = "Total number of prefixes " . $bgpcount; 

		//print_r($BGPLINES);

		return implode("\n",$BGPLINES);

	}else{
		return "SSH ERROR";
	}

}

?>