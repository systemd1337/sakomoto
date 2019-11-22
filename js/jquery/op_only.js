//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.op_only.init(); });
try { repod; } catch(a) { repod = {}; }
repod.op_only = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("op_only_enabled") ? repod_jsuite_getCookie("op_only_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.enabled,variable:'op_only_enabled',label:'Show only OP in threads list',hover:''}});
		this.update();
	},
	update: function() {
                if($(".thread").length>1){
                        reptoggle=$('<span class="replytoggle">[<a href="javascript:void(0);">Hide replies</a>]</span>');
                        reptoggle.children("a").click(function(){
                                if($(this).text()=="Hide replies"){
                                        $(this).parent().parent().children("*:not(.op):not(.thread_nav):not(br):not(.replytoggle)").each(function(){$(this).css("display","none");});
                                        $(this).text("Show replies");
                                }else{
                                        $(this).parent().parent().children("*:not(.op):not(.thread_nav):not(br):not(.replytoggle)").each(function(){$(this).css("display","");});
                                        $(this).text("Hide replies");
                                }
                        });
                        $(".thread").each(function(){
                                if($(this).find(".post.reply").length)
                                        $(this).append(reptoggle.clone());
                        });
                        if(repod.op_only.config.enabled)$(".replytoggle a").click();
                }
	}
}
