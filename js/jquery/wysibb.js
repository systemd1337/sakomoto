//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.wysibb.init(); });
try { repod; } catch(a) { repod = {}; }
repod.wysibb = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("wysibb_enabled") ? repod_jsuite_getCookie("wysibb_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.enabled,variable:'wysibb_enabled',label:'Enable WysiBB Editor',hover:''}});
		this.update();
	},
	update: function() {
		if (repod.wysibb.config.enabled) {
                        $("head").append($('<script src="'+js_dir+'wysibb/jquery.wysibb.min.js"></script>'));
                        $("head").append($('<link rel="stylesheet" href="'+js_dir+'wysibb/theme/default/wbbtheme.css" type="text/css"/>'));
                        allButtons={};
                        buttons='';
                        distinct=[];
                        for(tag in bbcodes){
                                transform={};
                                htm="<"+bbcodes[tag]+">{SELTEXT}</"+bbcodes[tag].split(' ')[0]+">";
                                transform[htm]="["+tag+"]{SELTEXT}[/"+tag+"]";
                                allButtons[tag]={
                                        transform:(transform),
                                        buttonText:tag
                                };
                                if(!distinct.includes(htm))
                                        buttons+=tag+',';
                                distinct.push(htm);
                        }
                        smileList=[];
                        for(emote in emotes){
                                smileList.push({
                                        title:emote,
                                        bbcode:":"+emote+":",
                                        img:'<img src="'+emotes_dir+emotes[emote]+'" alt="'+emote+'"/>'
                                });
                        }
                        /*
                        allButtons["quote"]={
                                transform:{'<span class="unkfunc">&gt;{SELTEXT}</span><br/>':">{SELTEXT}\n"},
                        }
                        */
                        $("form textarea[name='com']").each(function(){
                                $(this).wysibb({
                                        buttons:buttons,
                                        allButtons:allButtons,
                                        smileList:smileList,
                                        traceTextarea:true
                                });
                        });
                        if($("#quickReply").length)$(".qu").attr("href","javascript:void(0);");
		}
	}
}
