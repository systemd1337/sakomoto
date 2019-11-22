//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.bl_bottom.init(); });
try { repod; } catch(a) { repod = {}; }
repod.bl_bottom = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.bl_bottom.update);
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("bl_bottom_enabled") ? repod_jsuite_getCookie("bl_bottom_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.enabled,variable:'bl_bottom_enabled',label:'Bottom Backlinks',hover:'Place backlinks at the bottom of posts.'}});
		this.update();
	},
	update: function() {
		if (repod.bl_bottom.config.enabled) {
                        $(".post").each(function(){
                                blinks=$(this).children(".postInfo").children(".backlink");
                                if(!blinks)return;
                                bl_bottom=$('<span class="bl_bottom"></span>');
                                bl_bottom.append(blinks);
                                $(this).append(bl_bottom);
                        });
                        $(".postInfo .backlink").remove();
		}
	}
}
