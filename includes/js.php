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

ob_start("ob_gzhandler");
header('Content-type: application/javascript');

header("Expires: Sat, 26 Jul ".(date("Y")+1)." 05:00:00 GMT"); // Date in the future
	
$js_files = array(
	'./jquery/jquery-1.9.1.min.js',
    './jquery/jquery-ui-1.10.4.custom/js/jquery-ui-1.10.4.custom.min.js',
	'./jquery/tipsy/javascripts/jquery.tipsy.js',
);


foreach($js_files AS $key => $file) {
	include($file);
	echo "\n\n";
}

?>

$(function() {
    $('.tip_north').tipsy({gravity: 'n', fade: true});
    $('.tip_south').tipsy({gravity: 's', fade: true});
    $('.tip_southwest').tipsy({gravity: 'sw', fade: true});
    $('.tip_northeast').tipsy({gravity: 'se', fade: true});
    $('.tip_east').tipsy({gravity: 'e', fade: true});
    $('.tip_west').tipsy({gravity: 'w', fade: true});
     
    $('.tip').tipsy();

	// clear search field on focus and revert on blur
   	var input = $('#nodeidsearch');
    input.focus(function() {
         $(this).val('');
    }).blur(function() {
         var el = $(this);
         if(el.val() == '')
             el.val(el.attr('title'));
    });                    
        
});
