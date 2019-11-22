//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.quick_reply.init(); });
try { repod; } catch(a) { repod = {}; }
repod.quick_reply = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("quick_reply_enabled") ? repod_jsuite_getCookie("quick_reply_enabled") === "true" : true,
			persistent: repod.suite_settings && repod_jsuite_getCookie("persistent_quick_reply_enabled") ? repod_jsuite_getCookie("persistent_quick_reply_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.enabled,variable:'quick_reply_enabled',label:'Quick Reply',hover:'Quickly respond to a post by clicking its post number.'}});
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Quotes & Replying',read:this.config.persistent,variable:'persistent_quick_reply_enabled',label:'Persistent Quick Reply',hover:'Keep Quick Reply window open after posting.'}});
		this.update();
	},
	update: function() {
		if (repod.quick_reply.config.enabled&&$("#postform").length) {
                        if(repod.quick_reply.config.persistent)repod.quick_reply.show();
                        $(".qu").each(function(){
                                $(this).attr("onclick","");
                                $(this).click(function(e){
                                        e.preventDefault();
                                        if(!$("#quickReply").length)repod.quick_reply.show();
                                        $("#quickReply textarea").val($("#quickReply textarea").val()+">>"+$(this).text()+"\n");
                                        $("#quickReply textarea").focus();
                                });
                        });
                        if($(".thread").length==1)
                                $(".ctrl").last().append($('<span style="transform:translate(-50%);position:absolute;left:50%;line-height:2em;">[<a href="javascript:void(0);" onclick="repod.quick_reply.show();">Post a Reply</a>]</span>'));
		}
	},
        show:function(){
                if($("#quickReply").length)return;
                qr=$("<div></div>");
                qr.attr("class","reply");
                qr.attr("id","quickReply");
                qr.css("position","fixed");
                qr.css("right","0");
                qr.css("bottom","0");
                qr.css("border-style","solid");
                qr.css("border-width","1px");
                qrform=$('<form enctype="multipart/form-data" method="post"></form>');
                qrform.attr("action",$("#postform").attr("action"));
                $('#postform input[type="hidden"]').each(function(){
                        thisclone=$(this).clone();
                        thisclone.attr("id","");
                        qrform.append(thisclone);
                });
                $('#postform table input,#postform table textarea,#postform table button').each(function(){
                        if($(this).parent().parent().attr("id")=="painter")return;
                        if($(this).hasClass("noqr"))return;
                        if($(this).attr("name")=="verif"){
                                qrform.append("<div></div>");
                                qrform.append($("#verifimg").clone());
                        }
                        thisclone=$(this).clone();
                        thisclone.attr("id","");
                        thisclone.attr("size","");
                        thisclone.attr("placeholder",$(this).parent().siblings(".postblock").text());
                        thisclone.val("");
                        if($(this).parent().parent().attr("class")=="unimportant"||$(this).parent().attr("class"))
                                thisclone.attr("class","unimportant");
                        inputtype=$(this).attr("type");
                        if(inputtype!="button"&&inputtype!="submit"||$(this).parent().attr("colspan")=="2")
                                qrform.append($('<div></div>'));
                        qrform.append(thisclone);
                });
                qrheader=$("<center></center>");
                if($(".thread").length==1)qrheader.text("Reply to Thread No."+$(".thread").attr("id").substr(1));
                else qrheader.text("Start a New Thread");
                qrheader.attr("class","replymode");
                qrheader.css("cursor","move");
                qrclose=$('<a style="position:absolute;top:4px;right:4px;" href="javascript:void(0);" onclick="$(\'#quickReply\').remove()"></a>');
                qrclose.append(repod.buttons.get("cross"));
                qr.append(qrclose);
                Draggable.set(qrheader[0]);
                $("body").append(qr);
                qr.append(qrheader);
                qr.append(qrform);
//                qr.css("top","calc(50% - "+qr.height()/2+"px)");
                toggleHidden();toggleHidden();
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
