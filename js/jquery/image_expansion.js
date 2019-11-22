//RePod - Expands images with their source inside their parent element up to certain dimensions.
$(document).ready(function() { repod.image_expansion.init(); });
try { repod; } catch(a) { repod = {}; }
repod.image_expansion = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.image_expansion.update);
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_image_expansion_enabled") ? repod_jsuite_getCookie("repod_image_expansion_enabled") === "true" : true,
			selector: ".postimg"
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Images',read:this.config.enabled,variable:'repod_image_expansion_enabled',label:'Image expansion',hover:'Enable inline image expansion, limited to browser width'}});
		this.update();
	},
	update: function() {
		if(this.config.enabled&&$(this.config.selector).length){
                        $(this.config.selector).each(function(){
                                if(!$(this).parent().attr("href"))return;
                                if(!$(this).parent().attr("href").split('.')[1].match(/(j(fif|ng|pe?g?)[0-9]?|png|(x|w)?bmp|giff?|svgz?|ico|webp)$/i))return;
                                $(this).attr("title","Click to expand.");
                                $(this).click(function(event){repod.image_expansion.check_image(event,$(this));});
                        });
                        expandops=$('<div align="right"></div>');
                        expall=$('<span>[<a href="javascript:void(0);">Expand all images</a>]&nbsp;<span>');
                        expall.children("a").click(function(){
                                $(repod.image_expansion.config.selector).each(function(){
                                        repod.image_expansion.expand_image(this);
                                });
                                if(!$("#shrinkAll").length){
                                        shrinkall=$('<span id="shrinkAll">[<a href="javascript:void(0);">Shrink all images</a>]&nbsp;<span>');
                                        shrinkall.children("a").click(function(){
                                                $(repod.image_expansion.config.selector).each(function(){
                                                        repod.image_expansion.shrink_image(this);
                                                        shrinkall.remove();
                                                });
                                        });
                                        expandops.append(shrinkall);
                                }
                        });
                        galview=$('<span>[<a href="javascript:void(0);">Open gallery view</a>]&nbsp;</span>');
                        galview.children("a").click(function(){
                                repod.image_expansion.gallery.open();
                        });
                        mute=$('<span>[<a href="javascript:void(0);">Mute all images</a>]&nbsp;</span>');
                        mute.children("a").click(function(){
                                if($(this).text()=="Mute all images"){
                                        $(this).text("Unmute all images");
                                        mutestyle=$('<style id="mutestyle"></style>');
                                        mutestyle.html(repod.image_expansion.config.selector+":not(:hover){opacity:0.03;}");
                                        $("head").append(mutestyle);
                                }else{
                                        $(this).text("Mute all images");
                                        $("#mutestyle").remove();
                                }
                        });
                        expandops.append(expall);
                        expandops.append(galview);
                        expandops.append(mute);
                        $(".postarea").after(expandops);
                }
	},
	check_image: function(event,e) {
		event.preventDefault();
		$(e).data("o-s") ? this.shrink_image(e) : this.expand_image(e);
		$("#img_hover_element").remove();
	},
	expand_image: function(e) {
		$(e).data({"o-h":$(e).css("height"),"o-w":$(e).css("width"),"o-s":$(e).attr("src")}).css({"max-width":(Math.round($("body").width() - ($(e).parent().parent().offset().left * 2))),"width":"auto","height":"auto"});
                $(e).attr("title","Click to shrink.");
		var mp = $(e).parent().attr("href"); mp !== $(e).attr("src") && $(e).attr("src",mp);
	},
	shrink_image: function(e) {
		$(e).attr("src",$(this).data("o-s"));
                $(e).attr("title","Click to expand.");
		$(e).css({"max-height":"","max-width":"","width":$(e).data("o-w")}).attr("src",$(e).data("o-s")).removeData();
	},
        gallery:{
                open:function(){
                        container=$('<table><tbody><tr></tr></tbody></table>');
                        container.css("position","fixed");
                        container.css("left","0");
                        container.css("top","0");
                        container.css("right","0");
                        container.css("bottom","0");
                        container.css("width","100%");
                        container.css("height","100%");
                        container.css("background-color","rgba(0,0,0,0.7)");
                        container.attr("id","gallery");
                        container.click(function(){$(this).remove();});
                        $("body").append(container);
                        area=container.find("tr");
                        
                        ctrlleft=$('<td width="20px" valign="middle" align="center"><div></div></td>');
                        ctrlleft.css("border-style","solid");
                        ctrlleft.css("border-width","1px");
                        ctrlleft.css("border-color","black");
                        ctrlleft.css("background-color","rgba(0,0,0,0.3)");
                        ctrlleft.click(function(e){
                                e.stopPropagation();
                                repod.image_expansion.gallery.switch(parseInt($("#gallery").attr("current"))-1);
                        });
                        arrowl=ctrlleft.find("div");
                        arrowl.css("border-style","solid");
                        arrowl.css("border-right-color","white");
                        arrowl.css("border-width","12px");
                        arrowl.css("border-color","transparent");
                        arrowl.css("border-left-style","none");
                        arrowl.css("border-right-color","white");
                        arrowl.css("transform","translate(-25%)");
                        ctrlleft.css("cursor","pointer");
                        area.append(ctrlleft);
                        
                        imagecontainer=$('<td valign="middle" align="center" id="galleryimg"></td>');
                        imagecontainer.css("position","relative");
                        area.append(imagecontainer);
                        
                        ctrlright=$('<td width="20px" valign="middle" align="center"><div></div></td>');
                        ctrlright.css("border-style","solid");
                        ctrlright.css("border-width","1px");
                        ctrlright.css("border-color","black");
                        ctrlright.css("background-color","rgba(0,0,0,0.3)");
                        ctrlright.click(function(e){
                                e.stopPropagation();
                                repod.image_expansion.gallery.switch(parseInt($("#gallery").attr("current"))+1);
                        });
                        arrowr=ctrlright.find("div");
                        arrowr.css("border-style","solid");
                        arrowr.css("border-left-color","white");
                        arrowr.css("border-width","12px");
                        arrowr.css("border-color","transparent");
                        arrowr.css("border-right-style","none");
                        arrowr.css("border-left-color","white");
                        arrowr.css("transform","translate(25%)");
                        ctrlright.css("cursor","pointer");
                        area.append(ctrlright);
                        
                        thumbnails=$('<td valign="top" align="center" width="150px"><div></div></td>');
                        thumbnails.css("border-style","solid");
                        thumbnails.css("border-width","1px");
                        thumbnails.css("border-color","black");
                        thumbnails.css("background-color","rgba(0,0,0,0.3)");
                        thumbnails.css("position","relative");
                        thumbnailsdiv=thumbnails.find("div");
                        thumbnailsdiv.css("position","absolute");
                        thumbnailsdiv.css("top","0");
                        thumbnailsdiv.css("bottom","0");
                        thumbnailsdiv.css("overflow-y","auto");
                        i=0;
                        $(repod.image_expansion.config.selector).each(function(){
                                if(!$(this).parent().attr("href"))return;
                                i++;
                                thumb=$('<img vspace="2"/>');
                                thumb.css("max-width","100%");
                                thumb.attr("src",$(this).attr("src"));
                                thumba=$('<a href="javascript:void(0);"></a>');
                                thumba.append(thumb);
                                thumba.css("display","block");
                                thumba.attr("id","galthumb"+i);
                                thumba.attr("no",i);
                                thumba.click(function(e){
                                        e.stopPropagation();
                                        repod.image_expansion.gallery.switch($(this).attr("no"));
                                });
                                thumbnailsdiv.append(thumba);
                        });
                        area.append(thumbnails);
                        
                        repod.image_expansion.gallery.switch(1);
                },
                switch:function(thumbno){
                        img=$("<img/>");
                        img.css("max-width","100%");
                        img.css("max-height","100%");
                        img.css("margin","auto");
                        src=$(repod.image_expansion.config.selector+"[src='"+$("#galthumb"+thumbno+" img").attr("src")+"']").parent().attr("href");
                        if(!src)return;
                        img.attr("src",src);
                        imgcon=$("<div></div>");
                        imgcon.css("position","absolute");
                        imgcon.css("top","0");
                        imgcon.css("bottom","0");
                        imgcon.css("width","100%");
                        imgcon.css("display","flex");
                        imgcon.css("align-items","center");
                        imgcon.append(img);
                        $("#galleryimg").html(imgcon);
                        $("#gallery").attr("current",thumbno);
                }
        }
}
