//This JavaScript is native to Sakomoto

$(document).ready(function(){repod.thread_expansion.init();});
repod_suite_settings_pusher = []; //Legacy support. New scripts should push their information to repod.thread_updater.callme instead.
try { repod; } catch(a) { repod = {}; }
repod.thread_expansion = {
	init:function() {
		if (repod.suite_settings) {
                        this.config = {
                                enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_thread_expansion_enabled") ? repod_jsuite_getCookie("repod_thread_expansion_enabled") === "true" : true,
                        }
			repod.suite_settings.info.push({menu:{category:'Navigation',read:this.config.enabled,variable:'repod_thread_expansion_enabled',label:'Thread expansion',hover:'Expand threads inline on board indexes'}});
		}
		this.update();
	},
	update: function() {
                if($(".thread").length>1&&!repod.thread_expansion.config.enabled||~repod.suite_settings)return;
                $(".omittedposts").each(function(){
                        expand_plus=$("<a></a>");
                        expand_plus.attr("href","javascript:void(0);");
                        expand_plus.css("margin-right","3px");
                        expand_plus.css("margin-left","2px");
                        expand_plus.attr("title","Expand thread");
                        expand_plus.click(function(){
                                repod.thread_expansion.expand($(this).parent().parent().attr("id").substr(1));
                        });
                        expand_plus.append(repod.buttons.get("post_expand_plus"));
                        $(this).prepend(expand_plus);
                });
	},
        expand:function(thread){
                $("#t"+thread+" .omittedposts")[0].innerHTML="Loading&hellip;";
                var url=phpself+"?res="+thread
		$.ajax({url:url,success:function(result){
                        $("#t"+thread)[0].innerHTML=$(result).find(".thread")[0].innerHTML;
		}});
        }
}
