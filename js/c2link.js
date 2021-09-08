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