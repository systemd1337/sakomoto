//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.thread_nav.init(); });
try { repod; } catch(a) { repod = {}; }
repod.thread_nav = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("thread_nav_enabled") ? repod_jsuite_getCookie("thread_nav_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Navigation',read:this.config.enabled,variable:'thread_nav_enabled',label:'Thread Navigation',hover:'Add links to threads on the index to jump between threads.'}});
		this.update();
	},
	update: function() {
		if (repod.thread_nav.config.enabled&&$(".thread").length>1) {
                        ts=$(".thread").length;
                        $(".thread").each(function(){
                                tn=$('<div class="thread_nav" align="right" id="tn'+ts+'"></div>');
                                tn.css("float","right");
                                tn.css("padding-top","1.6em");
                                tn.append('<font size="+1" face="arial"><a href="#" class="tn_down" title="Next thread">&#9660;</a>&nbsp;<a href="#" class="tn_up" title="Previous thread">&#9650;</a></font>');
                                $(this).prepend(tn);
                                ts--;
                        });
                        ts=$(".thread").length;
                        $(".thread_nav .tn_down").each(function(){
                                if(ts-1)$(this).attr("href","#tn"+(ts-1));
                                else $(this).attr("href","#tn"+($(".thread").length));
                                ts--;
                        });
                        ts=$(".thread").length;
                        $(".thread_nav .tn_up").each(function(){
                                if((ts+1)>$(".thread").length)$(this).attr("href","#tn1");
                                else $(this).attr("href","#tn"+(ts+1));
                                ts--;
                        });
                        if($(".thread").length>1){
                                //$("body").css("padding-bottom","100%");
                        }
		}
	}
}
