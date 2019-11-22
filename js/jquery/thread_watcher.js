//This JavaScript is native to Sakomoto
//This JavaScript relies on the Sakomoto API

$(document).ready(function() { repod.thread_watcher.init(); });
try { repod; } catch(a) { repod = {}; }
repod.thread_watcher = {
	init: function() {
		repod.thread_updater && repod.thread_updater.callme.push(repod.thread_watcher.update);
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("thread_watcher_enabled") ? repod_jsuite_getCookie("thread_watcher_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Monitoring',read:this.config.enabled,variable:'thread_watcher_enabled',label:'Thread Watcher',hover:"Keep track of threads you're watching and see when they receive new posts"}});
		this.update();
	},
	update: function() {
		if (repod.thread_watcher.config.enabled) {
                        if(!window.localStorage.getItem("watched_threads"))
                                window.localStorage.setItem("watched_threads",'');
                        tw=$('<ul class="reply" id="threadWatcher"></ul>');
                        tw.css("list-style","none");
                        tw.css("padding","2px");
                        tw.css("position","fixed");
                        tw.css("left","5px");
                        tw.css("top","50%");
                        tw.css("border-style","solid");
                        tw.css("border-width","1px");
                        twheader=$('<li><b>Thread Watcher</b></li>');
                        twheader.css("cursor","move");
                        Draggable.set(twheader[0]);
                        twreload=$('<a href="javascript:void(0);" id="twreload"></font></a>');
                        twreload.css("line-height","0");
                        twreload.css("text-decoration","none");
                        twreload.css("outline","none");
                        twreload.css("margin-left","4px");
                        twreload.css("margin-right","4px");
                        twreload.click(function(){
                                $(this).html(repod.buttons.get("post_expand_rotate"));
                                repod.thread_watcher.reload();
                        })
                        twheader.append(twreload);
                        twclose=$('<a href="javascript:void(0);"></a>');
                        twclose.append(repod.buttons.get("cross"));
                        twclose.find("img").css("margin","0.1em");
                        twclose.find("img").css("float","right");
                        twclose.click(function(){$("#threadWatcher").remove();});
                        twheader.append(twclose);
                        twheader.append($('<hr style="margin:3px;clear:both;"/>'));
                        tw.append(twheader);
                        $("body").append(tw);
                        
                        repod.thread_watcher.reload();
		}
	},
        reload:function(){
                $(".watched").remove();
                $(".watchBtn").remove();
                watched=window.localStorage.getItem("watched_threads").split("|");
                
                $(".post.op .postInfo").each(function(){
                        watchadd=$('<a href="javascript:void(0);" class="watchBtn"></a>&nbsp;');
                        watchadd.append(repod.buttons.get("watch_thread_off"));
                        watchadd.css("text-decoration","none");
                        watchadd.css("outline","none");
                        watchadd.click(function(){
                                repod.thread_watcher.toggleWatch($(this).parent().attr("id").substr(1));
                        });
                        $(this).before(watchadd);
                });
                
                for(i=watched.length;i;){
                        i--;
                        if(watched[i]){
                                watch=$('<li class="watched" id="watch'+watched[i]+'"></li>');
                                removewatch=$('<a href="javascript:void(0);"><b><font face="arial">&times;</font></b></a>');
                                removewatch.data("thread",watched[i]);
                                removewatch.css("margin-right","4px");
                                removewatch.css("text-decoration","none");
                                removewatch.css("outline","none");
                                removewatch.click(function(){
                                        repod.thread_watcher.toggleWatch($(this).data("thread"));
                                });
                                watch.append(removewatch);
                                watchlink=$('<a class="watchlink"></a>');
                                watchlink.attr("href",phpself+"?res="+watched[i]);
                                watchlink.html("Loading&hellip;");
                                $.ajax(phpapi+"?mode=thread&res="+watched[i]).done(function(result){
                                        if(result["posts"])$("#watch"+result["posts"][0]["no"]+" .watchlink").html("No."+result["posts"][0]["no"]+" - "+(result["posts"][0]["sub"]?result["posts"][0]["sub"]:result["posts"][0]["com"]));
                                        else watchlink.html("<s>Thread no longer exists</s>");
                                });
                                
                                watch.append(watchlink);
                                tw.append(watch);
                                toggleBtn=$("#p"+watched[i]+" .watchBtn");
                                if(toggleBtn.length)
                                        toggleBtn.html(repod.buttons.get("watch_thread_on"));
                        }
                }
                
                $("#twreload").html(repod.buttons.get("refresh"));
        },
        toggleWatch:function(thread){
                if($("#watch"+thread).length)
                        window.localStorage.setItem("watched_threads",window.localStorage.getItem("watched_threads")
                                .replace(thread+"|",''));
                else
                        window.localStorage.setItem("watched_threads",
                                window.localStorage.getItem("watched_threads")+thread+'|');
                
                repod.thread_watcher.reload();
        }
}

//Draggable helper from 4chan extension https://github.com/4chan/4chan-JS/blob/master/extension.js#L8149
var Draggable = {
  el: null,
  key: null,
  scrollX: null,
  scrollY: null,
  dx: null, dy: null, right: null, bottom: null, offsetTop: null,
  
  set: function(handle) {
    handle.addEventListener('mousedown', Draggable.startDrag, false);
  },
  
  unset: function(handle) {
    handle.removeEventListener('mousedown', Draggable.startDrag, false);
  },
  
  startDrag: function(e) {
    var self, doc, offs;
    
    if (this.parentNode.hasAttribute('data-shiftkey') && !e.shiftKey) {
      return;
    }
    
    e.preventDefault();
    
    self = Draggable;
    doc = document.documentElement;
    
    self.el = this.parentNode;
    
    self.key = self.el.getAttribute('data-trackpos');
    offs = self.el.getBoundingClientRect();
    self.dx = e.clientX - offs.left;
    self.dy = e.clientY - offs.top;
    self.right = doc.clientWidth - offs.width;
    self.bottom = doc.clientHeight - offs.height;
    
    if (getComputedStyle(self.el, null).position != 'fixed') {
      self.scrollX = window.pageXOffset;
      self.scrollY = window.pageYOffset;
    }
    else {
      self.scrollX = self.scrollY = 0;
    }
    
//    self.offsetTop = Main.getDocTopOffset();
    
    document.addEventListener('mouseup', self.endDrag, false);
    document.addEventListener('mousemove', self.onDrag, false);
  },
  
  endDrag: function() {
    document.removeEventListener('mouseup', Draggable.endDrag, false);
    document.removeEventListener('mousemove', Draggable.onDrag, false);
    if (Draggable.key) {
      Config[Draggable.key] = Draggable.el.style.cssText;
      Config.save();
    }
    delete Draggable.el;
  },
  
  onDrag: function(e) {
    var left, top, style;
    
    left = e.clientX - Draggable.dx + Draggable.scrollX;
    top = e.clientY - Draggable.dy + Draggable.scrollY;
    style = Draggable.el.style;
    if (left < 1) {
      style.left = '0';
      style.right = '';
    }
    else if (Draggable.right < left) {
      style.left = '';
      style.right = '0';
    }
    else {
      style.left = (left / document.documentElement.clientWidth * 100) + '%';
      style.right = '';
    }
    if (top <= Draggable.offsetTop) {
      style.top = Draggable.offsetTop + 'px';
      style.bottom = '';
    }
    else if (Draggable.bottom < top &&
      Draggable.el.clientHeight < document.documentElement.clientHeight) {
      style.bottom = '0';
      style.top = '';
    }
    else {
      style.top = (top / document.documentElement.clientHeight * 100) + '%';
      style.bottom = '';
    }
  }
};
