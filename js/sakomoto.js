//This JavaScript is native to Sakomoto

alert = function(text="Alert!"){
        alert_already=document.getElementById("alert_container");
        if(alert_already)alert_already.remove();

        alert_container=document.createElement("center");
        alert_container.style.position="fixed";
        alert_container.style.top=0;
        alert_container.style.left=0;
        alert_container.style.bottom=0;
        alert_container.style.right=0;
        alert_container.style.width="100%";
        alert_container.style.height="100%";
        alert_container.style.backgroundColor="rgba(0,0,0,0.25)";
        alert_container.setAttribute("id","alert_container");
        alert_container.addEventListener("click",function(){alert_container.remove();});
        document.body.appendChild(alert_container);
        
        alert_box=document.createElement("center");
        alert_box.style.minHeight="2em";
        alert_box.style.width="500px";
        alert_box.style.borderStyle="solid";
        alert_box.style.borderWidth="1px";
        alert_box.style.marginTop="20px";
        alert_box.style.position="relative";
        alert_box.setAttribute("class","reply");
        alert_box.innerHTML="<p>"+text+"</p>";
        alert_box.addEventListener("click",function(e){e.stopPropagation();});
        alert_container.appendChild(alert_box);
        
        alert_close=document.createElement("a");
        alert_close.style.position="absolute";
        alert_close.style.top="5px";
        alert_close.style.right="5px";
        alert_close.style.lineHeight="10px";
        alert_close.style.fontSize="20px";
        alert_close.style.fontWeight="bold";
        alert_close.style.outline="none";
        alert_close.style.textDecoration="none";
        alert_close.style.fontFamily="Arial";
        if(repod&&repod.buttons)$(alert_close).append(repod.buttons.get("cross"));
        else alert_close.innerHTML='<big><b>&times;</b></big>';
        alert_close.addEventListener("click",function(){alert_container.remove();});
        alert_close.setAttribute("href","javascript:void(0);");
        alert_box.appendChild(alert_close);
        
        return text;
}

function changeStyle(title) {
	var links = document.getElementsByTagName('link');
	for (var i = links.length - 1; i >= 0; i--) {

		if (links[i].getAttribute('rel').indexOf('style') > -1 && links[i].getAttribute('title')) {
			links[i].disabled = true;
			if (links[i].getAttribute('title') == title) {
				links[i].disabled = false;
                                localStorage.setItem("style",links[i].getAttribute('title'));
/*				var d = new Date();
				d.setTime(d.getTime() + (24 * 60 * 60 * 1000));
				var expires = "expires=" + d.toUTCString();
				document.cookie = "style=" + links[i].getAttribute('title') + ";" + expires + "; path=/";*/
			}
		}
	}
}

function getCookie(cname) {
	/* http://www.w3schools.com/js/js_cookies.asp */
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return false;
}

function getUserFav(defstyle) {
	if (localStorage.getItem('style')) {
		changeStyle(localStorage.getItem('style'));
	} else {
		changeStyle(defstyle);
	}
}

function toggleHidden(){
        [].slice.call(document.getElementsByClassName("unimportant")).forEach(function(feild){
                feild.style.display=(feild.style.display=="table-row"?"none":"table-row");
        });
}

function closeWebm(thisclose){
        thisclose.parentNode.parentNode.getElementsByClassName("fileWebm")[0].style.display="";
        frame=thisclose.parentNode.parentNode.getElementsByTagName("video")[0];
        frame.parentNode.removeChild(frame);
        thisclose.parentNode.parentNode.removeChild(thisclose.parentNode);
}

function insert(pn){
        combox=document.getElementById("com");
        combox.value+=">>"+pn+"\n";
        combox.focus();
}

window.onload=function(){
        /*//Use jQuery version instead
	[].slice.call(document.getElementsByClassName("fileThumb")).forEach(function(thumblink){
		thumblink.childNodes[0].addEventListener("load",function(){
			this.style.opacity="1";
		});
		thumblink.addEventListener("click",function(ev){
			ev.preventDefault();
			var im=thumblink.childNodes[0];
			if(im.hasAttribute("data-thumb")){
				im.src=im.getAttribute("data-thumb");
				im.removeAttribute("data-thumb");
				im.style.float="left";
			}else{
				im.setAttribute("data-thumb",im.src);
				im.src=thumblink.href;
				if(im.hasAttribute("width"))im.removeAttribute("width");
				if(im.hasAttribute("height"))im.removeAttribute("height");
				im.style.float="unset";
				im.style.display="block";
				im.style.opacity="0.5";
			}
		});
	});*/
        
        toggleHidden();toggleHidden();
        
        var delsub=document.getElementById("delSub");
        var styleselcell=delsub.insertRow().insertCell();
        styleselcell.align="right";
        styleselcell.appendChild(document.createElement("label"));
        styleselcell.firstChild.innerHTML="Style: ";
        stylesel=document.createElement("select");
        var opt;
        [].slice.call(document.getElementsByTagName("link")).forEach(function(style){
                if(!style.title)return;
                opt=document.createElement("option");
                opt.innerHTML=style.title;
                stylesel.appendChild(opt);
        });
        stylesel.value=getCookie("style");
        stylesel.addEventListener("change",function(ev){
                changeStyle(stylesel.value);
        });
        styleselcell.firstChild.appendChild(stylesel);
        
        [].slice.call(document.getElementsByClassName("fileWebm")).forEach(function(webmthumb){
                webmlink=webmthumb.parentNode.getAttribute("href");
//                webmthumb.parentNode.href=phpplayer+"?v="+webmlink.replace(/^..\//,'');
                webmthumb.addEventListener("click",function(e){
                        e.preventDefault();
                        this.style.display="none";
                        this.parentNode.parentNode.innerHTML+='<video controls="" autoplay="" name="media"><source src="'+webmlink+'" type="video/webm"></video>';
/*                        player=document.createElement("iframe");
                        player.src=this.parentNode.href;
                        this.parentNode.appendChild(player);
                        this.style.display="none";
                        player.frameBorder=0;
                        player.onload=function(){
//                                player.style.width=player.contentWindow.document.querySelector("#playercontent video").videoWidth+"px";
//                                player.style.height=player.contentWindow.document.querySelector("#playercontent video").videoHeight+"px";
                                player.style.minWidth="calc( 200px + 1.6em )";
                                player.style.margin="5px";
                        }*/
                });
        });
        
        getUserFav(cssdef);
        /*
        var postForm=document.getElementById("postform");
        [].slice.call(document.getElementsByClassName("thread")).forEach(function(thread){
                thisqr=document.createElement("div");
                thisqr.className="postarea";
                thisform=document.getElementById("postform").cloneNode(true);
                thisform.id="qr"+thread.id.slice(1);
                thisform.style.display="none";
                thisform.innerHTML+="<input type=\"hidden\" name=\"resto\" value=\""+thread.id.slice(1)+"\"/>";
                thisqr.appendChild(thisform);
                thisexpand=document.createElement("div");
                thisexpand.className="reply";
                thisexpand.innerHTML="[<a href=\"javascript:void(0);\" onclick=\"qrexpand(this)\">Reply</a>]";
                thisexpand.style.display="inline-block";
                thisexpand.style.padding="2px";
                thisqr.appendChild(thisexpand);
                thread.appendChild(thisqr);
        });*/
}
/*
function qrexpand(ex){console.log();
        document.getElementById("qr"+ex.parentNode.parentNode.parentNode.id.slice(1)).style.display="initial";
        ex.parentNode.style.display="none";
        ex.parentNode.parentNode.querySelector("#verifimg").click();
}
*/