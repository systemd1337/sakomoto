//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.center_threads.init(); });
try { repod; } catch(a) { repod = {}; }
repod.center_threads = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.center_threads.update);
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("center_threads_enabled") ? repod_jsuite_getCookie("center_threads_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'center_threads_enabled',label:'Center threads',hover:'Align threads to the center of page'}});
		this.update();
	},
	update: function() {
		if (repod.center_threads.config.enabled) {
			$(".thread").css("margin","auto");
			$(".thread").css("width","75%");
		}
	}
}
