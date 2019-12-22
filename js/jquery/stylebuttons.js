//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.buttons.init(); });
try { repod; } catch(a) { repod = {}; }
repod.buttons = {
	init: function() {
		this.config = {
			altText:{
                                "cross":"X",
                                "post_expand_rotate":"&hellip;",
                                "post_expand_plus":"+",
                                "post_expand_minus":"-",
                                "question":"?",
                                "arrow_up":"&#9650;",
                                "arrow_down":"&#9660;",
                                "arrow_down2":"&#9660;",
                                "down_arrow2":"&#x1F4BE;",
                                "refresh":"&circlearrowright;",
                                "gis":"G",
                                "iqdb":"I",
                                "report":"!",
                                "watch_thread_on":"&starf;",
                                "watch_thread_off":"&star;"
                        },
                        switch:{
                                "giko":"burichan",
                                "yotsuba":"futaba",
                                "yotsuba_b":"burichan",
                                "miku":"burichan",
                                "sakomoto":"futaba"
                        }
		};
                buttonStyle=(repod_jsuite_getCookie("style")?repod_jsuite_getCookie("style"):cssdef).toLowerCase().replace(' ','_');
                for(style in repod.buttons.config.switch){
                        buttonStyle=buttonStyle.replace(style,repod.buttons.config.switch[style]);
                }
                $(".fileDownload").each(function(){$(this).html(repod.buttons.get("down_arrow2"));});
	},
        cache:{},
        findSrc:function(name){
                exts=["png","gif","giff","jpg","jpeg","iso","webp","bmp","wbmp","xbmp"];
                dirs=[buttonStyle+"/","default/"];
                src=false;
                dirs.forEach(function(dir){
                        exts.forEach(function(ext){
                                if(src)return;
                                request=new XMLHttpRequest();
                                src=jsPath+"/buttons/"+dir+name+"."+ext;
                                request.open("GET",src,false);
                                request.send();
                                if(request.status===404)src=false;
                        });
                });
                return src;
        },
        get:function(name){
                if(repod.buttons.cache[name])return repod.buttons.cache[name].clone();
                src=repod.buttons.findSrc(name);
                if(!src){
                        if(repod.buttons.config.altText[name])span=$('<span>'+repod.buttons.config.altText[name]+'</span>');
                        else span=$('<span>'+name+'</span>');
                        repod.buttons.cache[name]=span;
                        return span;
                }
                
                img=$('<img src="'+src+'" alt="'+repod.buttons.config.altText[name]+'" class="styleBtn"/>');
                repod.buttons.cache[name]=img;
                return img;
        }
}
