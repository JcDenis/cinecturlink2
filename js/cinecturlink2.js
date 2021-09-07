/* -- BEGIN LICENSE BLOCK ----------------------------------
 * This file is part of cinecturlink2, a plugin for Dotclear 2.
 * 
 * Copyright (c) 2009-2010 JC Denis and contributors
 * jcdenis@gdwd.com
 * 
 * Licensed under the GPL version 2.0 license.
 * A copy of this license is available in LICENSE file or at
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -- END LICENSE BLOCK ------------------------------------*/

;if(window.jQuery) (function($){
	$.fn.openGoogle = function(lang,target){
		return this.each(function(){
			$(this).click(function(){
				var val = $(target).attr('value');
				if (val!=''){
					searchwindow=window.open('http://www.google.com/search?hl='+lang+'&q='+val,'search','scrollbars=yes,width=800,height=600,resize=yes,toolbar=yes,menubar=yes');
					searchwindow.focus();
				}
				return false;
			});
		});
	}
	$.fn.openAmazon = function(lang,target){
		return this.each(function(){
			$(this).click(function(){
				var val = $(target).attr('value');
				if (val!=''){
					searchwindow=window.open('http://www.amazon.fr/exec/obidos/external-search?keyword='+val+'&mode=blended','search','scrollbars=yes,width=800,height=600,resize=yes,toolbar=yes,menubar=yes');
					searchwindow.focus();
				}
				return false;
			});
		});
	}
	$.fn.fillLink = function(target){
		return this.each(function(){
			$(this).change(function(){
				$(target).attr('value',$(this).attr('value'));
				return false;
			});
		});
	}
})(jQuery);

var dragsort = ToolMan.dragsort();
$(function(){
 dragsort.makeTableSortable($('#links-list-cat').get(0),
 dotclear.sortable.setHandle,dotclear.sortable.saveOrder);
});
dotclear.sortable = {
  setHandle: function(item) {
	var handle = $(item).find('td.handle').get(0);
	while (handle.firstChild) {
		handle.removeChild(handle.firstChild);
	}
	item.toolManDragGroup.setHandle(handle);
	handle.className = handle.className+' handler';
  },
  saveOrder: function(item) {
	var group = item.toolManDragGroup;
	var order = document.getElementById('cats_order');
	group.register('dragend', function() {
		order.value = '';
		items = item.parentNode.getElementsByTagName('tr');
		for (var i=0; i<items.length; i++) {
			order.value += items[i].id.substr(2)+',';
		}
	});
  }
};
$(function() {
	
	$filtersform = $('#filters-form');
	$filtersform.before('<p><a id="filter-control" class="form-control" href="plugin.php?p=cinecturlink2" style="display:inline">'+dotclear.msg.filter_posts_list+'</a></p>')
	
	if( dotclear.msg.show_filters == 'false' ) {
		$filtersform.hide();
	} else {
		$('#filter-control')
			.addClass('open')
			.text(dotclear.msg.cancel_the_filter);
	}
	
	$('#filter-control').click(function() {
		if( $(this).hasClass('open') ) {
			if( dotclear.msg.show_filters == 'true' ) {
				return true;
			} else {
				$filtersform.hide();
				$(this).removeClass('open')
					   .text(dotclear.msg.filter_posts_list);
			}
		} else {
			$filtersform.show();
			$(this).addClass('open')
				   .text(dotclear.msg.cancel_the_filter);
		}
		return false;
	});
});