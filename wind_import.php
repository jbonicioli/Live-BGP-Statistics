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

if(php_sapi_name() != "cli") {
	die("Please run this script from terminal");
}

require("includes/config.php");
require("includes/functions.php");
require('includes/simple_html_dom.php');

function find_element($url,$id,$class,$attribute){
	$find_string=$id."[".$class."=".$attribute."]";
	$ret = "";
	foreach($url->find($find_string) as $e){
		//echo($e);
		$ret.=$e;
	}
	return $ret;
}	

/* Usage
Grab some XML data, either from a file, URL, etc. however you want. Assume storage in $strYourXML;

$objXML = new xml2Array();
$arrOutput = $objXML->parse($strYourXML);
print_r($arrOutput); //print it out, or do whatever!

*/
class xml2Array {
	var $arrOutput = array();
	var $resParser;
	var $strXmlData;

	function parse($strInputXML) {
		$this->resParser = xml_parser_create ();
		xml_set_object($this->resParser,$this);
		xml_set_element_handler($this->resParser, "tagOpen", "tagClosed");

		xml_set_character_data_handler($this->resParser, "tagData");

		$this->strXmlData = xml_parse($this->resParser,$strInputXML );
		if(!$this->strXmlData) {
			die(sprintf("XML error: %s at line %d",
			xml_error_string(xml_get_error_code($this->resParser)),
			xml_get_current_line_number($this->resParser)));
		}

		xml_parser_free($this->resParser);

		return $this->arrOutput;
	}

	function tagOpen($parser, $name, $attrs) {
		$tag=array("name"=>$name,"attrs"=>$attrs);
		array_push($this->arrOutput,$tag);
	}

	function tagData($parser, $tagData) {
		if(trim($tagData)) {
			if(isset($this->arrOutput[count($this->arrOutput)-1]['tagData'])) {
				$this->arrOutput[count($this->arrOutput)-1]['tagData'] .= $tagData;
			}else{
				$this->arrOutput[count($this->arrOutput)-1]['tagData'] = $tagData;
			}
		}
	}

	function tagClosed($parser, $name) {
		$this->arrOutput[count($this->arrOutput)-2]['children'][] = $this->arrOutput[count($this->arrOutput)-1];
		array_pop($this->arrOutput);
	}
}

$WIND_DATA = file_get_contents("http://".$CONF['WIND_DOMAIN']."/?page=gmap&subpage=xml&show_p2p=1&show_aps=1&show_clients=1&show_unlinked=1");
sleep (1);

$objXML = new xml2Array();
$arrOutput = $objXML->parse($WIND_DATA);
//print_r($arrOutput);

mysql_query ( "TRUNCATE TABLE nodes ", $db);
//echo mysql_error();

for ( $o=0; $o< count($arrOutput[0]['children'][0]['children']); $o++ )  {

	//Loop over all nodes
	if ($arrOutput[0]['children'][0]['children'][$o]['attrs']['NAME']){

		//Get node page from wind to fetch node owner and C Classes
		$WIND_NODE_PAGE = file_get_html('http://wind.awmn.net/?page=nodes&node='.$arrOutput[0]['children'][0]['children'][$o]['attrs']['ID']);

		//loop over all TR elements
		foreach($WIND_NODE_PAGE->find('tr') as $row) {

			//Extract node owner
			if ($row->find('td.table-node-key',0)->plaintext == 'Διαχειριστής'){
				$nodeowner = str_replace (" |Αποστολή μηνύματος|", "",  $row->find('td',1)->plaintext );
			}

		}

		//Extact c-classes
		$CCLASSES = false;
		$CCLASS = false;
		foreach($WIND_NODE_PAGE->find('td.table-node-value2') as $ips) {
			$ip = $ips->plaintext; 
			if (strstr ($ip,".0") && strstr ($ip,".255")){
				$ip_parts = explode(" ", trim($ip));
				//print_r($ip_parts);
				$CCLASSES[] = $ip_parts[0] ;
			}     			
		}
		if (is_array($CCLASSES)){
			$CCLASS = implode("\n", $CCLASSES);
		}

		//clear DOM		
		$WIND_NODE_PAGE->clear(); 
		unset($WIND_NODE_PAGE);

		//Insert to DB
		$INSERT_NODE_NAME = mysql_query("INSERT INTO nodes (
										 	Node_id, 
										 	Node_name, 
										 	Node_area, 
										 	lat, 
										 	lon,
										 	Owner, 
										 	`C-Class`
										 ) VALUES (
										 	'".$arrOutput[0]['children'][0]['children'][$o]['attrs']['ID']."', 
										 	'".addslashes($arrOutput[0]['children'][0]['children'][$o]['attrs']['NAME'])."', 
										 	'". addslashes($arrOutput[0]['children'][0]['children'][$o]['attrs']['AREA']) ."', 
										 	'". addslashes($arrOutput[0]['children'][0]['children'][$o]['attrs']['LAT']) ."', 
											'". addslashes($arrOutput[0]['children'][0]['children'][$o]['attrs']['LON']) ."',
											'".$nodeowner."', 
											'".$CCLASS."' 

										 ) 
		", $db);

		if ($INSERT_NODE_NAME) {
			echo $arrOutput[0]['children'][0]['children'][$o]['attrs']['NAME'] . " inserted OK.\n";
		}else{
			echo mysql_error();
		}

		//usleep(450000);
		sleep(1);
	}

}

?>