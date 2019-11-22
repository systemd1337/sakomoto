//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.hide_bl.init(); });
try { repod; } catch(a) { repod = {}; }
repod.hide_bl = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.hide_bl.update);
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("hide_bl_enabled") ? repod_jsuite_getCookie("hide_bl_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.enabled,variable:'hide_bl_enabled',label:'Disable backlinks',hover:'Remove backlinks from the page.'}});
		this.update();
	},
	update: function() {
		if (repod.hide_bl.config.enabled) $(".backlink").remove();
	}
}
