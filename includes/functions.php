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

//Start gzip compression & session
if(php_sapi_name() != "cli" && stristr($_SERVER['PHP_SELF'], "js.php") == FALSE  && stristr($_SERVER['PHP_SELF'], "css.php") == FALSE && stristr($_SERVER['PHP_SELF'], "bgpcollect.php") == FALSE) {
	ob_start();
	session_start();
}

//Set some global parameters
error_reporting(E_ALL ^ E_NOTICE);


//MySQL Connection script
$db = @mysql_connect( $CONF['db_host'], $CONF['db_user'], $CONF['db_pass'] );
//@mysql_query('set names utf8'); 
@mysql_select_db($CONF['db'],$db);

//Connect to BGP Looking Glass NG MySQL Database
$db2 = @mysql_connect($CONF['db2_host'], $CONF['db2_user'], $CONF['db2_pass']);

//In case of mysql error, exit with message
if (mysql_error()){
	exit("<html>\n<head>\n<title>Error!</title>\n</head>\n<body>\nAn error occured while connecting to database.</body>\n</html>");
}

//Set global var $SECTION
if (isset($_GET['section'])){
	$SECTION = $_GET['section'];
}

//Set global vars for TITLE, Section heading & CSS class for section heading
if (isset($SECTION) && $SECTION == 'bgp_nodes_peers'){
	$maintitle_class = 'maintitle_bgp_nodes_peers';
	$maintitle_title = 'Live Node\'s BGP Peers';
}elseif (isset($SECTION) && $SECTION == 'bgp_peers'){
	$maintitle_class = 'maintitle_bgp_peers';
	$maintitle_title = 'Total BGP Peers List';
}elseif (isset($SECTION) && $SECTION == 'bgp_prefixes'){
	$maintitle_class = 'maintitle_bgp_prefixes';
	$maintitle_title = 'Announced BGP Prefixes List';
}elseif (isset($SECTION) && $SECTION == 'bgp_prepends'){
	$maintitle_class = 'maintitle_bgp_prepends';
	$maintitle_title = 'BGP Prepends';
}elseif (isset($SECTION) && $SECTION == 'bgp_illegal_prefixes'){
	$maintitle_class = 'maintitle_bgp_illegal_prefixes';
	$maintitle_title = 'Wrong Prefix Announcements';
}elseif (isset($SECTION) && $SECTION == 'bgp_map'){
	$maintitle_class = 'maintitle_bgp_map';
	$maintitle_title = 'Live BGP Network Map';
}else{
	$maintitle_class = 'maintitle_home';
	$maintitle_title = 'Dashboard';
}


// create sort link for table listings
function create_sort_link($attr, $title) {
	global $_SERVER, $_GET, $search_vars, $SECTION;

	if ($_GET['sort'] == $attr) {
		if ($_GET['by'] == "desc") {
			$by_value = "asc";
			$image = " <img src=\"images/sort_down.gif\" align=\"absmiddle\" />";
		} else {
			$by_value = "desc";
			$image = " <img src=\"images/sort_up.gif\" align=\"absmiddle\" />";
		}
	}

	return "<a href=\"index.php?section=$SECTION&sort=".$attr."&by=".$by_value."&pageno=".$_GET['pageno']. $search_vars ."\">".$title."</a> ". $image;
}

//Leftover function from older system. For this system it should always return true.
function staff_help(){
	return TRUE;
}   


//convert seconds to formated days, hours, minutes, seconds
function sec2hms ($oldTime, $newTime, $padHours = false, $padDays=false)  {  

	$sec = $newTime - $oldTime;

	// start with a blank string  
	$hms = "";  

	$days = intval(intval($sec) / 86400 );  

	if ($days > 0){
		// add days to $hms (with a leading 0 if asked for)  
		$hms .= ($padDays) ? str_pad($days, 2, "0", STR_PAD_LEFT). "d : " : $days. "d : ";  
	}    

	// do the hours first: there are 3600 seconds in an hour, so if we divide  
	// the total number of seconds by 3600 and throw away the remainder, we¢re  
	// left with the number of hours in those seconds  
	$hours = intval( ($sec / 3600) % 24);  

	// add hours to $hms (with a leading 0 if asked for)  
	$hms .= ($padHours) ? str_pad($hours, 2, "0", STR_PAD_LEFT). "h : " : $hours. "h : ";  


	// dividing the total seconds by 60 will give us the number of minutes  
	// in total, but we¢re interested in *minutes past the hour* and to get  
	// this, we have to divide by 60 again and then use the remainder  
	$minutes = intval(($sec / 60) % 60);  

	// add minutes to $hms (with a leading 0 if needed)  
	$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). "m : ";  

	// seconds past the minute are found by dividing the total number of seconds  
	// by 60 and using the remainder  
	$seconds = intval($sec % 60);  

	// add seconds to $hms (with a leading 0 if needed)  
	$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT) ."s";  

	// done!  
	return $hms;  

}    

?>