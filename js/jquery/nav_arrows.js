//This JavaScript is native to Sakomoto

$(document).ready(function() { repod.nav_arrows.init(); });
try { repod; } catch(a) { repod = {}; }
repod.nav_arrows = {
	init: function() {
		this.config = {
			enabled: repod.suite_settings && repod_jsuite_getCookie("nav_arrows_enabled") ? repod_jsuite_getCookie("nav_arrows_enabled") === "true" : false
		}
		repod.suite_settings && repod.suite_settings.info.push({menu:{category:'Navigation',read:this.config.enabled,variable:'nav_arrows_enabled',label:'Navigation arrows',hover:'Show top and bottom navigation arrows, hold Shift and drag to move'}});
		this.update();
	},
	update: function() {
		if (repod.nav_arrows.config.enabled) {
                        stickyNav=$('<div id="stickyNav" class="reply" data-shiftkey="1"></div>');
                        stickyNav.css("position","fixed");
                        stickyNav.css("top","50px");
                        stickyNav.css("right","10px");
                        stickyNav.css("border-style","solid");
                        stickyNav.css("border-width","1px");
                        stickyNav.css("font-size","large");
                        navHandle=$('<font face="arial" size="+1"></font>');
                        _top=$('<a href="#top"></a>');
                        _top.append(repod.buttons.get("arrow_up"));
                        _top.attr("title","Top");
                        navHandle.append(_top);
                        bottom=$('<a href="#bottom"></a>');
                        bottom.append(repod.buttons.get("arrow_down"));
                        bottom.attr("title","Bottom");
                        navHandle.append(bottom);
                        stickyNav.append(navHandle);
                        stickyNav.find("a").css("text-decoration","none");
                        stickyNav.find("a").css("display","block");
                        stickyNav.find("a").css("margin","1px");
                        stickyNav.find("a").css("outline","none");
                        stickyNav.find("a").click(function(e){
                                e.preventDefault();
                                if(e.shiftKey)return;
                                window.location=$(this).attr("href");
                        });
                        $("body").append(stickyNav);
                        Draggable.set(navHandle[0]);
		}
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
