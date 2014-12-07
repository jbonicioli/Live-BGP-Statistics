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

?>
				Records per page: 
                <span style="color:#ccc">
					<a href="index.php?section=<?=$SECTION;?>&<?=$url_vars?>&items_per_page=20"<? if ($num == 20) { ?> style="font-weight:bold;"<? } ?>>20</a> | 
					<a href="index.php?section=<?=$SECTION;?>&<?=$url_vars?>&items_per_page=50"<? if ($num == 50) { ?> style="font-weight:bold;"<? } ?>>50</a> | 
					<a href="index.php?section=<?=$SECTION;?>&<?=$url_vars?>&items_per_page=100"<? if ($num == 100) { ?> style="font-weight:bold;"<? } ?>>100</a> |
					<a href="index.php?section=<?=$SECTION;?>&<?=$url_vars?>&items_per_page=200"<? if ($num == 200) { ?> style="font-weight:bold;"<? } ?>>200</a> 
                </span>