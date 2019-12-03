//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.persistent_board_titles.init(); });
try { repod; } catch(a) { repod = {}; }
repod.persistent_board_titles = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("persistent_board_titles_enabled") ? repod_jsuite_getCookie("persistent_board_titles_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'persistent_board_titles_enabled',label:'Persistent Board Titles',hover:'Force board titles to be persistent, even if the board titles are updated.'}});
		this.update();
	},
	update: function() {
		if (repod.persistent_board_titles.config.enabled) {
                        $(".boardNav").each(function(){
                                $(this).css("position","fixed");
                                $(this).css("top","0");
                                $(this).css("left","0");
                                $(this).css("right","0");
                                $(this).css("border-style","none");
                                $(this).css("border-bottom-style","solid");
                                $(this).css("border-width","1px");
                                $(this).css("padding","2px");
                                $(this).css("padding-right","4px");
                                $(this).css("padding-left","4px");
                                $(this).css("box-shadow","0 1px 2px rgba(0,0,0,0.15)")
                                $(this).addClass("reply");
                                $("body").css("padding-top",$(this).height()+"px");
                        });
		}
	}
}
