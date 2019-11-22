//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.custom_js.init(); });
try { repod; } catch(a) { repod = {}; }

repod.custom_js = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("custom_js_enabled") ? repod_jsuite_getCookie("custom_js_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'custom_js_enabled',label:'Custom JS',hover:'Include your own JavaScript'},popup:{label:'Edit',title:'Custom JS',type:'textarea',variable:'custom_js_defined',placeholder:'Input custom JavaScript here.'}});
		this.update();
	},
	update: function() {
		if (repod.custom_css.config.enabled) {
			$('<script type="text/javascript">'+repod_jsuite_getCookie("custom_js_defined")+'</script>').appendTo("head");
		}
	}
}
