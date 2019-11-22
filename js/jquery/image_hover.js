//RePod - Displays the original image when hovering over its thumbnail.
$(document).ready(function() { repod.image_hover.init(); });
try { repod; } catch(a) { repod = {}; }
repod.image_hover = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.image_hover.update);
		this.config = {
			enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_image_hover_enabled") ? repod_jsuite_getCookie("repod_image_hover_enabled") === "true" : true,
			follow_enabled: repod.suite_settings && !!repod_jsuite_getCookie("repod_image_hover_follow_enabled") ? repod_jsuite_getCookie("repod_image_hover_follow_enabled") === "true" : true,
			selector: ".postimg"
		}
		repod.suite_settings && repod.suite_settings.info.push({mode:'modern',menu:{category:'Images',read:this.config.enabled,variable:'repod_image_hover_enabled',label:'Image hover',hover:'Expand images on hover, limited to browser size'}});
		repod.suite_settings && repod.suite_settings.info.push({mode:'modern',menu:{category:'Images',read:this.config.enabled,variable:'repod_image_hover_follow_enabled',label:'Image hover follows cursor',hover:'Hovered image follows cursor'}});
		this.update();
	},
	update: function() {
		if (this.config.enabled) {
			$(document).on("mouseover", this.config.selector, function() { repod.image_hover.display($(this)); });
			$(document).on("mouseout", this.config.selector, function() { repod.image_hover.remove_display() });
                        if(this.config.follow_enabled)$(this.config.selector).mousemove(function(event){
                                repod.image_hover.curpos.x=event.clientX;
                                repod.image_hover.curpos.y=event.clientY;
                                hoverimg=$("img#img_hover_element");
                                himgh=hoverimg.outerHeight();
                                himgw=hoverimg.outerWidth();
                                if(repod.image_hover.curpos.y+himgw>$(window).width())
                                        hoverimg.css("right","0");
                                else hoverimg.css("left",(repod.image_hover.curpos.x+10)+"px");
                                if(repod.image_hover.curpos.y+himgh>$(window).height())
                                        hoverimg.css("bottom","0px");
                                else hoverimg.css("top",(repod.image_hover.curpos.y+10)+"px");
                        });
		}
	},
        curpos:{x:0,y:0},
	display: function(e) {
                if(!$(e).parent().attr("href").split('.')[1].match(/(j(fif|ng|pe?g?)[0-9]?|png|(x|w)?bmp|giff?|svgz?|ico|webp)$/i))return;
		if (!$(e).data("o-s")) {
			$("body").append("<img id='img_hover_element' src='"+$(e).parent().attr("href")+"'/>");
                        hoverimg=$("img#img_hover_element");
                        hoverimg.css("max-width","100%");
                        hoverimg.css("max-height","100%");
                        hoverimg.css("position","fixed");
                        hoverimg.css("pointer-events","none");
                        
                        if(!repod.image_hover.config.follow_enabled){
                                hoverimg.css("top","0");
                                hoverimg.css("right","0");
                                return;
                        }
		}
	},
	remove_display: function() { $("img#img_hover_element").remove(); }
};