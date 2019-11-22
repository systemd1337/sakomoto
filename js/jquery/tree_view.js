//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.tree_view.init(); });
try { repod; } catch(a) { repod = {}; }
repod.tree_view = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.tree_view.update);
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("tree_view_enabled") ? repod_jsuite_getCookie("tree_view_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.enabled,variable:'tree_view_enabled',label:'Use tree view',hover:''}});
		this.update();
	},
	update: function() {
		if (repod.tree_view.config.enabled&&$(".thread").length==1) {
                        $(".post.op").append($('<br clear="both"/>'));
                        $(".backlink .quotelink").each(function(){repod.tree_view.branchGen(this);});
		}
	},
        branchGen(backlink){
                refpost=$("#"+$(backlink).attr("href").split("#")[1]).clone(true,true);
                if(!refpost.length)return;
                refpost.addClass("leaf");
                refpost.attr("id","leaf"+refpost.attr("id"));
                thispost=$(backlink).parent().parent().parent();
                if(thispost.prop("tagName")!="DIV")thispost=thispost.parent().parent().parent();
                thispost.addClass("hasBranch");
                replybox=$('<table><tbody><tr><td valign="top"><h1 class="pointbox"><font face="arial">&boxur;</font></h1></td><td class="branch"></td></tr></tbody></table>');
                replybox.css("background-color","rgba(0,0,0,0.25)");
                replybox.css("border-style","solid");
                replybox.css("border-color","black");
                replybox.css("border-width","1px");
                replybox.css("margin-bottom","1px");
                replybox.find(".branch").append(refpost);
                replybox.find(".sideArrows").remove();
                replybox.find(".pointbox").css("color","black");
                thispost.after(replybox);
                
                refpost.find(".backlink .quotelink").each(function(){repod.tree_view.branchGen(this);});
        }
}
