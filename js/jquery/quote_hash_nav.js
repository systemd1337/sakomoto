//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.hash_nav.init(); });
try { repod; } catch(a) { repod = {}; }
repod.hash_nav = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.hash_nav.update);
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("hash_nav_enabled") ? repod_jsuite_getCookie("hash_nav_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.enabled,variable:'hash_nav_enabled',label:'Quote Hash Navigation',hover:'Include an extra link after quotes for autoscrolling to quoted posts.'}});
		this.update();
	},
	update: function() {
		if (repod.hash_nav.config.enabled) {
                        $(".quotelink").each(function(){
                                $(this).after('&nbsp;<a href="'+$(this).attr("href")+'">#</a>');
                        });
		}
	}
}