//This JavaScript is native to Sakomoto

$(document).ready(function(){repod.post_menu.init();});
try{repod;}catch(e){repod={};}

repod.post_menu = {
        init:function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.post_menu.update);
		this.config = {
			post_filter_enabled: repod.suite_settings && repod_jsuite_getCookie("post_filter_enabled") ? repod_jsuite_getCookie("post_filter_enabled") === "true" : false,
                        post_filter_popup_data:{label:'Edit',title:'Filters',type:'textarea',variable:'post_filters',placeholder:'type/pattern/options'}
                }
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Miscellaneous',read:this.config.post_filter_enabled,variable:'post_filter_enabled',label:'Filter specific threads/posts',hover:'Enable pattern-based filters'},popup:repod.post_menu.config.post_filter_popup_data});
                this.update();
        },
        update:function() {
                if(repod.post_menu.config.post_filter_enabled){
                        if(!repod_jsuite_getCookie("post_filters"))
                                repod_jsuite_setCookie("post_filters",'');
                        filters=repod_jsuite_getCookie("post_filters").split("\n");
                        $(".post").each(function(){
                                for(i=filters.length;i;){
                                        i--;
                                        thisfilter=filters[i].split('/')
                                        type=thisfilter[0];
                                        if(thisfilter.length<3)continue;
                                        regex=new RegExp(thisfilter[1],thisfilter[2]);
                                        switch(type.toLowerCase()){
                                                case "comment":
                                                case "com":
                                                        if($(this).find("blockquote").text().match(regex))
                                                                repod.post_menu.hidePost($(this).find(".qu").text());
                                                        break;
                                                case "subject":
                                                case "sub":
                                                        if($(this).find(".subject").text().match(regex))
                                                                repod.post_menu.hidePost($(this).find(".qu").text());
                                                        break;
                                                case "name":
                                                case "nameblock":
                                                case "email":
                                                case "trip":
                                                case "user":
                                                        if($(this).find(".nameBlock").text().match(regex))
                                                                repod.post_menu.hidePost($(this).find(".qu").text());
                                                        break;
                                                case "id":
                                                case "posterid":
                                                case "userid":
                                                        if($(this).find(".posteruid").text().match(regex))
                                                                repod.post_menu.hidePost($(this).find(".qu").text());
                                                        break;
                                                case "num":
                                                case "number":
                                                case "no":
                                                case "no.":
                                                        if($(this).find(".postNum").text().match(regex))
                                                                repod.post_menu.hidePost($(this).find(".qu").text());
                                                        break;
                                                case "fname":
                                                case "filename":
                                                case "ftext":
                                                case "filetext":
                                                case "file":
                                                        if($(this).find(".fileText").text().match(regex))
                                                                repod.post_menu.hidePost($(this).find(".qu").text());
                                                        break;
                                                case "*":
                                                case "":
                                                        if($(this).text().match(regex))
                                                                repod.post_menu.hidePost($(this).find(".qu").text());
                                                        break;
                                        }
                                }
                        });
                }
                $(".backlink").each(function(){
                        post=$(this).siblings(".postNum").children("a:contains('No.')").attr("href").split("#p")[1];
                        postMenuBtn=$('<a href="javascript:void(0)" title="Post menu" class="postMenuBtn" id="pmb'+post+'">&#9654;</a>');
                        postMenuBtn.css("text-decoration","none");
                        postMenuBtn.css("display","inline-block");
                        postMenuBtn.css("transition","transform 0.1s linear");
                        postMenuBtn.css("outline","none");
                        postMenuBtn.css("width","1em");
                        postMenuBtn.css("text-align","center");
                        postMenuBtn.insertBefore(this);
                        postMenuBtn.data("post",post);
                        if(getCookie("hidepost"+post)=="true")repod.post_menu.hidePost(post);
                        $(document).on("click","#pmb"+post,function(){
                                if($(this).data("open")=="true"){
                                        repod.post_menu.close(this);
                                }else{
                                        $(this).data("open","true");
                                        $(this).css("transform","rotate(90deg)");
                                        postMenu=repod.post_menu.newPostMenu($(this).data("post"),
                                                $(this).offset().left,
                                                $(this).offset().top+$(this).outerHeight());
                                        $("body").append(postMenu);
                                        that=this;
                                        postItems=postMenu.children("ul");
                                        postItems.append(repod.post_menu.newItem("Report post",function(){
                                                window.location.href=phpself+"?mode=report&no="+$(that).data("post");
                                        }));
                                        p=$("#p"+$(that).data("post")+" .post");
                                        if(!p.length)p=$("#p"+$(that).data("post"));
                                        if(p.hasClass("post-hidden")){
                                                postItems.append(repod.post_menu.newItem("Unide post",function(){
                                                        $(p).children(".file").attr("style","");
                                                        $(p).children(".fileThumb").attr("style","");
                                                        $(p).children("blockquote").attr("style","");
                                                        $(p).children(".postInfo").attr("style","");
                                                        $(p).removeClass("post-hidden");
                                                        set_cookie("hidepost"+$(that).data("post"),"false");
                                                }));
                                        }else{
                                                postItems.append(repod.post_menu.newItem("Hide post",function(){
                                                        repod.post_menu.hidePost($(that).data("post"));
                                                        set_cookie("hidepost"+$(that).data("post"),"true");
                                                }));
                                        }
                                        filedata=$(this).parent().siblings(".file");
                                        dels=[repod.post_menu.newItem("Post",function(){window.location.href=phpself+"?mode=del&no="+$(that).data("post");})];
                                        if(filedata.length)dels.push(repod.post_menu.newItem("File",function(){window.location.href=phpself+"?mode=del&onlyimgdel=on&no="+$(that).data("post");}));
                                        postItems.append(repod.post_menu.newItemDir("Delete",dels));    
                                        if(filedata.length){
                                                file=location.href.substring(0,location.href.lastIndexOf("/"))+"/"+filedata.children(".fileThumb").attr("href");
                                                postItems.append(repod.post_menu.newItemDir("Image search",[
                                                        repod.post_menu.newItem("Google",function(){window.open("http://www.google.com/searchbyimage?image_url="+file,"_blank");}),
                                                        repod.post_menu.newItem("IQDB",function(){window.open("http://iqdb.org/?url="+file,"_blank");}),
                                                        repod.post_menu.newItem("SauceNAO",function(){window.open("http://saucenao.com/search.php?db=999&url="+encodeURI(file),"_blank");}),
                                                        repod.post_menu.newItem("WAIT",function(){window.open("https://trace.moe/?url="+encodeURI(file),"_blank");}),
                                                        repod.post_menu.newItem("Yandex",function(){window.open("https://yandex.com/images/search?rpt=imageview&url="+encodeURI(file),"_blank");})
                                                ]));
                                        }
                                        var selected=window.getSelection().toString();
                                        if(repod.post_menu.config.post_filter_enabled)
                                                postItems.append(repod.post_menu.newItemDir("Filter",[
                                                        repod.post_menu.newItem("Name",function(){repod.post_menu.addFilter("name",$(p).find(".nameBlock").text());}),
                                                        repod.post_menu.newItem("Subject",function(){repod.post_menu.addFilter("subject",$(p).find(".subject").text());}),
                                                        repod.post_menu.newItem("Comment",function(){repod.post_menu.addFilter("comment",$(p).find("blockquote").text());}),
                                                        repod.post_menu.newItem("Filename",function(){repod.post_menu.addFilter("filename",$(p).find(".fileText a").text());}),
                                                        repod.post_menu.newItem("No.",function(){repod.post_menu.addFilter("no",$(p).find(".qu").text());}),
                                                        repod.post_menu.newItem("ID",function(){repod.post_menu.addFilter("id",$(p).find(".posteruid b").text());}),
                                                        repod.post_menu.newItem("Filter selected text",function(){repod.post_menu.addFilter("*",selected);})
                                                ]));
                                }
                        });
                        postMenuBtn.after(" ");
                });
        },
        addFilter:function(what,text){
                repod_jsuite_setCookie("post_filters",
                        (repod_jsuite_getCookie("post_filters")?repod_jsuite_getCookie("post_filters")+'\n':'')+what+'/'+text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')+'/');
                        
                repod.suite_settings.spawn.popup(repod.post_menu.config.post_filter_popup_data);
                $('#settings_popup_window input').each(function(){$(this).click(function(){window.location.reload();});});
        },
        close:function(pm){
                $(pm).data("open","false");
                $(pm).css("transform","unset");
                $(".pm"+$(pm).data("post")).remove();
        },
        closeAll:function(){
                $(".postMenuBtn").each(function(){repod.post_menu.close(this);});
        },
        newItem:function(text,onclick){
                item=$('<li class="reply"></li>');
                item.css("cursor","pointer");
                item.css("font-size","12px");
                item.css("position","relative");
                item.css("padding","2px");
                item.css("padding-left","4px");
                item.css("padding-right","4px");
                item.hover(
                function(){$(this).css("background-color","rgba(0,0,0,0.1)");},
                function(){$(this).css("background-color","rgba(0,0,0,0)");},
                );
                item.append(text);
                $(item).click(function(e){
                        e.preventDefault();
                        onclick();
                        repod.post_menu.closeAll();
                });
                return item;
        },
        newItemDir:function(name,items){
                itemThis=repod.post_menu.newItem(name+" &raquo;",function(){});
                dirMenu=repod.post_menu.newPostMenu("dir",0,0);
                items.forEach(function(item){
                        dirMenu.find("ul").append(item);
                });
                dirMenu.css("display","none");
                dirMenu.css("z-index","1");
                itemThis.mouseover(function(){$(this).find(".pmdir").css("display","inline-block");});
                itemThis.mouseout(function(){$(this).find(".pmdir").css("display","none");});
                dirMenuContainer=$("<div></div>");
                dirMenuContainer.css("float","right");
                dirMenuContainer.append(dirMenu);
                itemThis.append(dirMenuContainer);
                return itemThis;
        },
        newPostMenu:function(ident,x,y){
                postMenu=$('<div class="reply postMenu pm'+ident+'"></div>');
                postMenu.css("border-style","solid");
                postMenu.css("border-width","1px");
                postMenu.css("padding","0");
                postMenu.css("position","absolute");
                postMenu.css("margin-left","4px");
                if(y)postMenu.css("top",(y)+"px");
                if(x)postMenu.css("left",(x)+"px");
                
                pmItems=$("<ul></ul>");
                pmItems.css("margin","0");
                pmItems.css("padding","0");
                pmItems.css("list-style","none");
                pmItems.css("white-space","nowrap");
                postMenu.append(pmItems);
                
                return postMenu;
        },
        hidePost:function(post){
                p=$("#p"+post+" .post");
                if(!p.length)p=$("#p"+post);
                $(p).children(".file").css("display","none");
                $(p).children(".files").css("display","none");
                $(p).children(".fileThumb").css("display","none");
                $(p).children("blockquote").css("display","none");
                $(p).children(".postInfo").css("filter","opacity(0.5)");
                $(p).addClass("post-hidden");
        }
};
