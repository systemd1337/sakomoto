//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.mark_quote.init(); });
try { repod; } catch(a) { repod = {}; }

repod.mark_quote = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.mark_quote.update);
                this.mark_op.config={
                         enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_mark_op") ? repod_jsuite_getCookie("repod_mark_op") === "true" : true
                };
                this.mark_cross.config={
                         enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_mark_cross") ? repod_jsuite_getCookie("repod_mark_cross") === "true" : true
                };
		this.config = {
		}
                if (repod.suite_settings) {
                        repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.mark_op.config.enabled,variable:'repod_mark_op',label:'Mark OP Quotes',hover:'Add \'(OP)\' to OP quotes.'}});
                        repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.mark_cross.config.enabled,variable:'repod_mark_cross',label:'Mark Cross-thread Quotes',hover:'Add \'(Cross-thread)\' to cross-threads quotes.'}});
                }
		this.update();
	},
	update: function() {
		repod.mark_quote.mark_op.update();
                if($(".thread").length!=1)return;
		repod.mark_quote.mark_cross.update();
	},
        mark_op:{
                config:{},
                update:function(){
                        $(".thread").each(function(){
                                op=$(this).find(".post.op .postNum a:contains('No.')").attr("href").split("#p")[1];
                                $(".quotelink").each(function(){
                                        refpost=$(this).attr("href").split("#p")[1];
                                        if(refpost==op)$(this).append($("<b> (OP)</b>"));
                                });
                        });
                }
        },
        mark_cross:{
                config:{},
                update:function(){
                        $(".quotelink").each(function(){
                                postNo=$(this).parent().parent().parent().children(".postInfo").children(".postNum").children("a:contains('No.')").attr("href");
                                if(!postNo)return;
                                postNo=postNo.split("#p")[1];
                                resto=$("#p"+postNo).parent().attr("id").slice(1);
                                refno=$(this).attr("href").split("#p")[1];
                                if($("#p"+refno).length&&$("#p"+refno).parent().attr("id").slice(1)==resto)return;
                                $(this).append($("<b> (Cross-thread)</b>"));
                        });
                }
        }
}
