//This JavaScript is native to Sakomoto
//Saguaro's "utility quotes" doesn't work because of differences in syntax.
//So here's a complete re-write of the inline quotes part

$(document).ready(function() { repod.inline_quotes.init(); });
try { repod; } catch(e) { repod = {}; }

repod.inline_quotes={
        init:function(){
                this.config = {
                        enabled:(repod.suite_settings&&!!repod_jsuite_getCookie("repod_quotes_inline")?
                                repod_jsuite_getCookie("repod_quotes_inline")==="true":true)
                }
                repod.suite_settings.info.push({menu:{
                        category:'Quotes & Replying',
                        read:this.config.enabled,
                        variable:'repod_quotes_inline',
                        label:'Inline quote links',
                        hover:'Clicking quote links will inline expand the quoted post'}});
                this.update();
        },
        update:function(){
                if(repod.inline_quotes.config.enabled)
                        $(document).on("click",".quotelink",function(e){repod.inline_quotes.inline(e,$(this));});
        },
        inline:function(event,e){
                event.preventDefault();
                target_post=e.attr("href").split("#p")[1];
                post=$("#p"+target_post);
                if(event.shiftKey||!post.length){
                        window.location=$(e).attr("href");
                        return;
                }
                $(".hover_post").remove();
                if($("#inline_"+target_post).length){
                        $("#inline_"+target_post).remove();
                        return;
                }
                if(post.prop("tagName")=="DIV")clone=$("#p"+target_post).clone(true);
                else clone=$("#p"+target_post+" .post").clone(true);
                clone.css("border-style","solid");
                clone.css("border-width","1px");
                clone.css("display","inline-block");
                clone.attr("class","post reply");
                e.parent().after('<div id="inline_'+target_post+'"></div>');
                $("#inline_"+target_post).append(clone);
                $("#inline_"+target_post+" blockquote").after("<br clear=\"both\">");
        }
}
