//This JavaScript is native to Sakomoto
//Saguaro's "utility quotes" doesn't work because of differences in syntax.
//So here's a complete re-write of the hover part

$(document).ready(function() { repod.quotes_hover.init(); });
try { repod; } catch(e) { repod = {}; }

repod.quotes_hover={
        init:function(){
		repod.thread_updater && repod.thread_updater.callme.push(repod.quotes_hover.update);
                this.config = {
                        enabled:(repod.suite_settings&&!!repod_jsuite_getCookie("repod_quotes_hover")?
                                repod_jsuite_getCookie("repod_quotes_hover")==="true":true)
                }
                repod.suite_settings.info.push({menu:{
                        category:'Quotes & Replying',
                        read:this.config.enabled,
                        variable:'repod_quotes_hover',
                        label:'Quote preview',
                        hover:'Enable inline quote previews'}});
                this.update();
        },
        update:function(){
                if(repod.quotes_hover.config.enabled){
                        $(document).on("mouseover",".quotelink",function(e){ Tip($("#p"+$(this).attr("href").split("#p")[1]).html()); /*repod.quotes_hover.display_hover(e,$(this));*/});
                        $(document).on("mouseout",".quotelink",function(e){ UnTip(); /*$(".hover_post").remove();*/});
                }
        },
        display_hover:function(event,e){
                target_post=e.attr("href").split("#p")[1];
                post=$("#p"+target_post);
                if(post.prop("tagName")=="DIV")clone=$("#p"+target_post).clone(true);
                else clone=$("#p"+target_post+" .post").clone(true);
                clone.css("border-style","solid");
                clone.css("border-width","1px");
                clone.css("position","fixed");
                clone.css("left",(e.offset().left+e.outerWidth()+5)+"px");
                clone.attr("class","hover_post post reply");
                $("body").append(clone);
                clone.css("top",(e.offset().top-$(window).scrollTop()-clone.outerHeight()/2)+"px");
        }
}
