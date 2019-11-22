//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.infinite_scroll.init(); });
try { repod; } catch(a) { repod = {}; }
repod.infinite_scroll = {
	init: function() {
		this.config = {
			always: repod.suite_settings && repod_jsuite_getCookie("infinite_scroll_always") ? repod_jsuite_getCookie("infinite_scroll_always") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Navigation',read:this.config.enabled,variable:'infinite_scroll_always',label:'Always use infinite scroll',hover:'Enable infinite scroll by default, so reaching the bottom of the board index will load subsequent pages'}});
		this.update();
	},
	update: function() {
                depage=false;
                depage_enable=$('<td>[<a href="javascript:void(0);" title="Toggle infinite scroll">All</a>]</td>');
                depage_enable.find("a").click(function(){
                        depage=true;
                        $(this).replaceWith($("<b>Loading&hellip;</b>"));
                        $(window).scroll();
                });
                $("#pager").find("tr").prepend(depage_enable);
                if(repod.infinite_scroll.config.always)depage_enable.find("a").click();
                page=parseInt($("#pages b").text());
	}
}

$(window).scroll(function() {
        pager=$("#pager");
        if(pager.length&&($(window).height()+$(window).scrollTop())>=pager.offset().top&&depage) {
                depage=false;
                url=(page+1)+phpext;
                
                $.ajax({url:url,
                        success:function(result){
                                $("#board").append($(result).find(".thread"));
                                page++;
                                if(repod.center_threads)repod.center_threads.update();
                                depage=true;
                        },error:function(result){pager.remove();}
                });
        }
});
