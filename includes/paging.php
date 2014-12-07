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
<table border="0" align="right" cellpadding="4" cellspacing="0" class="form_paging">
												<tr>
													<td>
														<?
														if ($pageno >= $num) {
															$page_prev = $pageno - $num;
														?>
														<a href="index.php?section=<?=$SECTION;?>&<?=$url_vars?>&pageno=<?=$page_prev?>" class="previous_page" title="Previous page"><span>Previous Page</span></a>
														<?}else{?>
														<span class="previous_page_inactive"><span>Previous page</span></span>
														<?}?>
													</td>
													<td>Page</td>
													<td>
														<form action="index.php?section=<?=$SECTION;?>&<?=$url_vars?>" method="post" name="paging" id="paging" style="margin:0">
														<input name="goto" type="text" value="<?=$current_page?>" size="3" maxlength="3" class="paging_field" onblur="if(this.value=='') this.value='<?=$current_page?>';" onFocus="if(this.value=='<?=$current_page?>') this.value='';" />
														</form>
													</td>
													<td>of <?=$total_pages?></td>
													<td>
														<?
														if ($pageno < ($items_number - $num)) {
															$page_next = $pageno + $num;
														?>
														<a href="index.php?section=<?=$SECTION;?>&<?=$url_vars?>&amp;pageno=<?=$page_next?>" class="next_page" title="Next page"><span>Next Page</span></a>
														<?}else{?>
														<span class="next_page_inactive"><span>Next page</span></span>
														<?}?>
													</td>
												</tr>
												</table>