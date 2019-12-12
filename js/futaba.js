/*Fixes for Sakomoto*/
document.write(`
<style>
#slptmp{display:none;}
.slp1{
        border-style:solid;
        border-width:1px;
        cursor:pointer;
        box-shadow:2px 2px 3px 0px #777;
        border-radius:5px;
        white-space:nowrap;
        padding:3px;
        font-size:small;
}
</style>
`);
defCom="";

/********************* polifill *********************/

(function () {//getElementsByClassName polyfill for IE6-8
	if (!document.getElementsByClassName) {
	  document.getElementsByClassName = function(search) {
	    var d = document, elements, pattern, i, results = [];
	    if (d.querySelectorAll) { // IE8
	      return d.querySelectorAll("." + search);
	    }
	    if (d.evaluate) { // IE6, IE7
	      pattern = ".//*[contains(concat(' ', @class, ' '), ' " + search + " ')]";
	      elements = d.evaluate(pattern, d, null, 0, null);
	      while ((i = elements.iterateNext())) {
	        results.push(i);
	      }
	    } else {
	      elements = d.getElementsByTagName("*");
	      pattern = new RegExp("(^|\\s)" + search + "(\\s|$)");
	      for (i = 0; i < elements.length; i++) {
	        if ( pattern.test(elements[i].className) ) {
	          results.push(elements[i]);
	        }
	      }
	    }
	    return results;
	  }
	}
})();

(function() {//addEventListener polyfill for IE8
  if (!Event.prototype.preventDefault) {
    Event.prototype.preventDefault=function() {
      this.returnValue=false;
    };
  }
  if (!Event.prototype.stopPropagation) {
    Event.prototype.stopPropagation=function() {
      this.cancelBubble=true;
    };
  }
  if (!Element.prototype.addEventListener) {
    var eventListeners=[];
    var addEventListener=function(type,listener /*, useCapture (will be ignored) */) {
      var self=this;
      var wrapper=function(e) {
        e.target=e.srcElement;
        e.currentTarget=self;
        if (typeof listener.handleEvent != 'undefined') {
          listener.handleEvent(e);
        } else {
          listener.call(self,e);
        }
      };
      if (type=="DOMContentLoaded") {
        var wrapper2=function(e) {
          if (document.readyState=="complete") {
            wrapper(e);
          }
        };
        document.attachEvent("onreadystatechange",wrapper2);
        eventListeners.push({object:this,type:type,listener:listener,wrapper:wrapper2});
        
        if (document.readyState=="complete") {
          var e=new Event();
          e.srcElement=window;
          wrapper2(e);
        }
      } else {
        this.attachEvent("on"+type,wrapper);
        eventListeners.push({object:this,type:type,listener:listener,wrapper:wrapper});
      }
    };
    var removeEventListener=function(type,listener /*, useCapture (will be ignored) */) {
      var counter=0;
      while (counter<eventListeners.length) {
        var eventListener=eventListeners[counter];
        if (eventListener.object==this && eventListener.type==type && eventListener.listener==listener) {
          if (type=="DOMContentLoaded") {
            this.detachEvent("onreadystatechange",eventListener.wrapper);
          } else {
            this.detachEvent("on"+type,eventListener.wrapper);
          }
          eventListeners.splice(counter, 1);
          break;
        }
        ++counter;
      }
    };
    Element.prototype.addEventListener=addEventListener;
    Element.prototype.removeEventListener=removeEventListener;
    if (HTMLDocument) {
      HTMLDocument.prototype.addEventListener=addEventListener;
      HTMLDocument.prototype.removeEventListener=removeEventListener;
    }
    if (Window) {
      Window.prototype.addEventListener=addEventListener;
      Window.prototype.removeEventListener=removeEventListener;
    }
  }
})();

/********************* pull-down menu *********************/
/*
function usrdelsend(e){//user-delete send by pull-down menu
	e.preventDefault();
	var t=e.target;
	var no=t.parentNode.parentNode.getAttribute("data-no");
//console.log(no);//
	var data = {"responsemode": "ajax"};
	for(var i=0;i<t.length;i++){
		var value="";
		var ti=t[i];
		switch(ti.type){
			case "checkbox":
				if(ti.checked){
					value="on";
				}
				break;
			default:
				value=ti.value;
		}
		if(ti.name!=""){
			data[ti.name]=value;
		}
	}
	var xmlhttp = false;
	if(typeof ActiveXObject != "undefined"){
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e) {
			xmlhttp = false;
		}
	}
	if(!xmlhttp && typeof XMLHttpRequest != "undefined") {
		xmlhttp = new XMLHttpRequest();
	}
	xmlhttp.open("POST", "/"+b+"/futaba.php?guid=on");
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 ) {
			var res=xmlhttp.responseText;
			if(res=="ok"){
				var threno=getAncestorElement(document.getElementById("delcheck"+no),"thread").getAttribute("data-res");
//				console.log(threno+" "+no);
				replaceRes(threno,no);
				var pdm=document.getElementById("pdm");
				if(pdm){
					pdm.parentNode.removeChild(pdm);
				}
//				console.log(res);
			}else{
				alert(res);
			}
			return false;
		}
	};
	xmlhttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	xmlhttp.send( EncodeHTMLForm( data ) );
	return false;
}
function EncodeHTMLForm( data ){//Convert data to HTML form format
	var params = [];
	for( var name in data ){
		var value = data[ name ];
		var param = encodeURIComponent( name ) + '=' + encodeURIComponent( value );
		params.push( param );
	}
	return params.join( '&' ).replace( /%20/g, '+' );
}
/*
function delsend(e,no,reason){//del-form send by pull-down menu
	var data = { "mode":"post" , "b": b , "d": no ,"reason": reason , "responsemode": "ajax"};
	var xmlhttp = false;
	if(typeof ActiveXObject != "undefined"){
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e) {
			xmlhttp = false;
		}
	}
	if(!xmlhttp && typeof XMLHttpRequest != "undefined") {
		xmlhttp = new XMLHttpRequest();
	}
	xmlhttp.open("POST", "/del.php");
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 ) {
			var res=xmlhttp.responseText;
			var pdm=document.getElementById("pdm");
			var pdd=document.getElementById("pdd");
//			console.log(res);
			if(res=="ok"){
				var stt = document.createElement('div');
				stt.className = 'pddtip';
				stt.style.position = 'absolute';
				stt.style.left = e.target.offsetLeft +20+ "px";
				stt.style.top = e.target.offsetTop + "px";
				stt.innerHTML = "";
				e.target.offsetParent.appendChild(stt);
				setTimeout(function(){
					if(stt){stt.parentNode.removeChild(stt);}
				},1000);
				setTimeout(function() {
					if(pdm){
						pdm.parentNode.removeChild(pdm);
					}
					if(pdd){
						pdd.parentNode.removeChild(pdd);
					}
				},1000);
			}else{
				alert(res);
			}
		}
	};
	xmlhttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	xmlhttp.send( EncodeHTMLForm( data ) );
	return false;
}*//*
function delopen(e,t){//open delete form
	e.stopPropagation();
	var no = t.innerHTML.replace("No.","");
	if (false && !window.FormData) {
		var pdm=document.getElementById("pdm");
		if(pdm){
			pdm.parentNode.removeChild(pdm);
		}
		self.location.href="/del.php?b="+b+"&d="+no;
		return;
	}
	if(document.getElementById("pdd")){
		pdd.parentNode.removeChild(pdd);
		return;
	}
	var principle=[
	["101","中傷・侮辱・名誉毀損"],
	["102","脅迫・自殺"],
	["103","個人情報・プライバシー"],
	["104","つきまとい・ストーカー"],
	["105","連投・負荷増大・無意味な羅列"],
	["106","広告・spam"],
	["107","売春・援交"],
	["108","侵害・妨害"],
	["109","板違い"],
	["110","荒らし・嫌がらせ・混乱の元"],
	["111","政治・宗教・民族"],
	["201","グロ画像"],
	["202","猥褻画像・無修正画像"],
	["302","エロ画像"],
	["303","児童ポルノ画像(３次)"]];
	var exception=[
	["79","111","政治・民族"],
	["35","111","宗教・民族"],
	["80","111","宗教・民族"],
	["38","111","宗教"],
	["o","201","グロ画像(３次)"],
	["51","201","グロ画像(３次)"],
	["9","109",""],
	["23","109",""],
	["b","109",""],
	["jun","109",""]];
//	var p=getAncestorElement(t,"thre");
	var resto=e.target.parentNode.getAttribute("data-res")
	var p=document.getElementById("delcheck"+resto).parentNode;
	var m = document.createElement('div');
		m.className = "pdd";
		m.id = "pdd";
	var t1=fragmentFromString('<div class="pddt1" id="pddt1">No.'+no+'の削除依頼をします<br>理由を押してください</div>');
	m.appendChild(t1);
	var xy=getScrollPosition(e.target.parentNode);
	var bodyHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	var bodyWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
	var mtop=(xy.y)-0;
	var mleft=180;
	if(mtop<getScroll().y){mtop=getScroll().y;}
	if(bodyWidth-getPosition(e.target.parentNode).x<420){
		mleft=-240;
		if(xy.x<mleft){mleft=xy.x;}
	}
	m.style.top = mtop+"px";//pdmの右
	m.style.left = (xy.x)+mleft+"px";//pdmの右
	forprinciple:
	for(var i=0;i<principle.length;i++){//change for each borad
		var p1=principle[i][1];
		for(var j=0;j<exception.length;j++){
			if(exception[j][0]==b && exception[j][1]==principle[i][0]){
				if(exception[j][2]==""){
					continue forprinciple;
				}
				p1=exception[j][2];
				break;
			}
		}
		var d1=document.createElement('div');
		d1.innerHTML=p1;
		d1.className="pdds";
		(function(k){
			d1.addEventListener("click",function(){delsend(arguments[0],no,principle[k][0]);},false);
		})(i);
		m.appendChild(d1);
	}
	p.appendChild(m);
	drag=new dragElement();
	drag.Init("pdd","pddt1");
//	console.log("pdd open");
	return true;
}*/
function adjustElement(el){//Adjust element to fit in window
	var winh = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	var winw = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
	if(getPosition(el).y<0){el.style.top=getScroll().y+2+"px";}
	var ymax=winh-el.clientHeight;
	if(getPosition(el).y>ymax){el.style.top=ymax+getScroll().y-22+"px";}
	if(getPosition(el).x<0){el.style.left=getScroll().x+2+"px";}
	var xmax=winw-el.clientWidth;
	if(getPosition(el).x>xmax){el.style.left=xmax+getScroll().x-22+"px";}
}
var drag;
var dragElement=function(){
	'use strict';
	var dragging,useCapture,mEle,gEle;
	var ios43,startOffsetX,startOffsetY;
	var pd,pu,pm,touchActionBackUp;
	function InitDragElement(moveElementId,grabElementId){
		mEle = document.getElementById(moveElementId);
		gEle = document.getElementById(grabElementId);
		mEle.style.position="absolute";
		ios43 = window.navigator.userAgent.toLowerCase().indexOf('applewebkit/533');
		var supportsPassive = false;
		try {
			var opt = Object.defineProperty({}, 'passive', {
				get: function() {
					supportsPassive = true;
				}
			});
			var listner=function(){};
			window.addEventListener( "checkpassive", listner, opt );
			window.removeEventListener( "checkpassive", listner, opt );
		} catch ( err ) {}
		if(supportsPassive){
			useCapture={ passive: false };
		}else{
			useCapture=false;
		}
		if (window.PointerEvent) {
				pd='pointerdown';pu='pointerup';pm='pointermove';
		}else{
			if('ontouchstart' in window){
				pd='touchstart';pu='touchend';pm='touchmove';
			}else{
				pd='mousedown';pu='mouseup';pm='mousemove';
			}
		}
		gEle.addEventListener(pd,MouseDown,false);
		adjustElement(mEle);
	}
	function adjTouch(touch){//iOS4.3バグ対応
		if(touch.clientX==touch.pageX && touch.clientY==touch.pageY && ios43 !== -1){
			var scX = (window.pageXOffset !== undefined) ? window.pageXOffset :
				(document.documentElement || document.body.parentNode || document.body).scrollLeft;
			var scY = (window.pageYOffset !== undefined) ? window.pageYOffset :
				(document.documentElement || document.body.parentNode || document.body).scrollTop;
			return {x:scX,y:scY};
		}else{
			return {x:0,y:0};
		}
	}
	function getxy(e){//マウスの座標を得る
		var cx,cy;
		if(!e){
			e = window.event;
		}
		if("touches" in e && e.touches.length>0){
			var adj=adjTouch(e.touches[0]);
			cx=e.touches[0].clientX - adj.x;
			cy=e.touches[0].clientY - adj.y;
		}else{
			cx = e.clientX;
			cy = e.clientY;
		}
		return {x:cx,y:cy};
	}
	function MouseDown(e){// 掴む
	  dragging = true;
		e.preventDefault();
		touchActionBackUp=mEle.style.touchAction;
		mEle.style.touchAction="none";
		var xy=getxy(e);
		startOffsetY = xy.y - getPosition(gEle).y;
		startOffsetX = xy.x - getPosition(gEle).x;
		document.addEventListener(pu,MouseUp,false);
		document.addEventListener(pm,MouseMove,useCapture);
		return false;
	}
	function MouseUp(e){// 離す
		if (dragging) {
			dragging = false;
		}
		adjustElement(mEle);
		mEle.style.touchAction=touchActionBackUp;
		document.removeEventListener(pu,MouseUp,false);
		document.removeEventListener(pm,MouseMove,useCapture);
	}
	function MouseMove(e){//動かす
		var mouseXY,scrollXY;
		if(!dragging){
			return;
		}// ドラッグ途中
		e.preventDefault();
		mouseXY=getxy(e);
		scrollXY=getScroll();
	  mEle.style.top = ( mouseXY.y + scrollXY.y - startOffsetY) + 'px';
	  mEle.style.left = ( mouseXY.x + scrollXY.x - startOffsetX) + 'px';
	}
	return {"Init":InitDragElement};
};
/*
function pdqw(p,e,noflag){//pull-down quote write button listner
	var no=0,ti="";
	if(!p){return;}
	var ftxa=document.getElementById("com");
	if(!ftxa){return;}
	if(noflag==2){
		var ch=p.children;
		for(var i=0;i<ch.length;i++){
			if(ch[i].tagName.toLowerCase()=="a" && ch[i].href.match(/\.(jpg|png|gif|mp4|webm|webp)$/)){
				ti=ch[i].innerHTML+"\n";
				break;
			}
		}
	}else{
		ti=p.getElementsByTagName('blockquote')[0].innerHTML;
		if(ti==defCom||noflag==1){
			no=getNumFromTD(p);
			ti="No."+no+"\n";
		}else{
			ti=ti.replace(/<br *\/?>(.)/gi,'\n>$1');
			ti=ti.replace(/<("[^"]*"|'[^']*'|[^'">])*>/g,'')+'\n';
		}
	}
	ftxa.value+=unescapeHTML('>'+ti);
	scrollToEle(ftxa);
	var pdm=e.target.parentNode;
	pdm.parentNode.removeChild(pdm);
}
*/
function scrollToEle(el){//Scroll to focus on specified element
	if(typeof el.scrollIntoView=='function'){
		el.scrollIntoView({behavior: 'smooth',block:"center"});
		var scrollTimeout;
		var func=function(e) {
			clearTimeout(scrollTimeout);
			scrollTimeout = setTimeout(function() {
				el.focus();
				clearTimeout(scrollTimeout);
				document.removeEventListener('scroll', func,false);
			}, 100);
		};
		document.addEventListener('scroll', func,false);
	}else{
		var winh = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
		window.scroll(0,el.offsetTop - winh/2);
		el.focus();
	}
}
/*
function pdmopen(t){//open pull-down menu
	var no = t.innerHTML.replace("No.","");
//	var p = document.getElementsByClassName("thre")[0];
	var p=getAncestorElement(t,"thread");
	var m = document.createElement('div');
		m.className = "pdmm";
		m.id = "pdm";
	var xy=getScrollPosition(t);
	var winh = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	if(window.screen.width<=799){
		m.style.top = (xy.y)+40+"px";
		m.style.left = (xy.x-60)+"px";
	}else{
		m.style.top = (xy.y)+20+"px";
		m.style.left = (xy.x-10)+"px";
	}
	m.style.position = "absolute";
	var d1=document.createElement('div');
		d1.innerHTML="本文を引用";
		if(document.getElementsByClassName("thread").length>1){
			d1.className="pdms pdmshide";
		}else{
			d1.addEventListener('click', function(){pdqw(t.parentNode,arguments[0],0);}, false);
			d1.className="pdms";
		}

	var d4=document.createElement('div');
		d4.innerHTML="発言No.を引用";
		if(document.getElementsByClassName("thread").length>1){
			d4.className="pdms pdmshide";
		}else{
			d4.addEventListener('click', function(){pdqw(t.parentNode,arguments[0],1);}, false);
			d4.className="pdms";
		}

	var d5=document.createElement('div');
		d5.innerHTML="画像ファイル名を引用";
		if(document.getElementsByClassName("thread").length>1){
			d5.className="pdms pdmshide";
		}else{
			d5.addEventListener('click', function(){pdqw(t.parentNode,arguments[0],2);}, false);
			d5.className="pdms";
		}

	var d2=document.createElement('div');
		d2.innerHTML="削除依頼(del)";
		d2.addEventListener('click', function(){delopen(arguments[0],t);}, false);
		d2.className="pdms";

	var d3=document.createElement('div');
		d3.className="pdmf";
	var f1=document.createElement('form');
		f1.action='/'+b+'/futaba.php?guid=on';
		f1.method="POST";
		f1.addEventListener('submit', function(){return usrdelsend(arguments[0]);}, false);
		f1.innerHTML='削除キー<input type="password" name="pwd" size="8" value="'+getCookie("pwdc")+'"><br>'+
			'<input type="checkbox" name="onlyimgdel" value="on">画像だけ<input type="submit" value="記事削除">'+
			'<input type="hidden" name="'+no+'" value="delete"><input type="hidden" name="mode" value="usrdel">';
	d3.appendChild(f1);
	m.appendChild(d1);
	m.appendChild(d4);
	m.appendChild(d5);
	m.appendChild(d2);
	m.appendChild(d3);
	m.setAttribute('data-no', no);
	m.setAttribute('data-res', p.getAttribute('data-res'));
	p.appendChild(m);
	adjustElement(m);
	if(winh<getPosition(t).y+t.clientHeight+m.clientHeight){
		m.style.top = xy.y-m.clientHeight-5+"px";
	}
//console.log("open pdm");
	return true;
}

/********************* quote popup *********************/
function getNumFromTD(el){//Determine article number from td element
	var spanel=el.getElementsByTagName("span");
	for (var i=0;i<spanel.length;i++) {
		if(spanel[i].className=="cno"){
			return parseInt(spanel[i].innerText.replace("No.",""),10);
		}
	}
	return 0;
}

function getAncestorElement(ele,className){//Find ancestors
	while(ele && ele!=document){
		if(ele.className==className){
			return ele;
		}
		ele=ele.parentNode;
	}
	return document;
}

function searchQtSrc(str,thisnum){//search quote source
	var no,el;
	if(str.length<1){return 0;}
	var threEle=getAncestorElement(document.getElementById("delcheck"+thisnum),"thread");
	if(/^([1-9]|[1-9][0-9]|[1-9][0-9][0-9]|1[0-9][0-9][0-9]|2000)$/.test(str)){
		var rsc=str;
		var rscs=threEle.getElementsByClassName("del");
		for(var i=0;i<rscs.length;i++){
			if(rscs[i].innerHTML==rsc){
				no=rscs[i].id.replace("delcheck","");
				return no;
			}
		}
	}
	if(/^(No)?\.?[0-9]+$/.test(str)){
		no=str.replace(/^(No)?\.?/,"");
		if(document.getElementById("delcheck"+no)){
			return no;
		}
	}
	fnReg=/^[0-9]+\.(jpg|png|gif|webm|mp4|webp)$/;
	if(fnReg.test(str)){
		no=str.replace(/^&gt;/,"");
		el=document.querySelectorAll(".thread a")
		for(i=0;i<el.length;i++){
			if(el[i].innerHTML==no){
				ret=getNumFromTD(el[i].parentNode);
				if(ret>0 && ret<thisnum){return ret;}
			}
		}
	}
	el=threEle.getElementsByTagName("blockquote");
	var removetag=/<("[^"]*"|'[^']*'|[^'">])*>/g;
	str=unescapeHTML(str.replace(removetag,''));
	if(str==defCom){return 0;}
	str=str.replace(/[\\^$.*+?()[\]{}|]/g, '\\$&');//reg escape
	var i,j,ret,reg,regs=['^'+str+'$',''+str+''];
	for(j=0;j<regs.length;j++){
		reg=new RegExp(regs[j],'gm');
//		for(i=0;i<el.length;i++){
		for(i=el.length-1;0<=i;i--){
			var target=unescapeHTML(el[i].innerHTML.replace(/<br>/g,"\n").replace(removetag,''));
			if(defCom==target){continue;}
			if(target.match(reg)){
				ret=getNumFromTD(el[i].parentNode);
				if(ret>0 && ret<thisnum){return ret;}
			}
		}
	}
	if(str.length<5){return 0;}
	var minSimi=9999;
	var minSimiEle={};
	for(i=0;i<el.length;i++){
		var com=el[i].innerHTML;
		var lines=com.split(/<br>/i);
		for(j=0;j<lines.length;j++){
			var line=lines[j];
			line=line.replace(removetag,'');
			var simi=levenshtein(line,str);
//			console.log(simi+":"+line)
			if(simi!=0 && simi<minSimi){
				minSimi=simi;
				minSimiEle=el[i];
			}
		}
	}
	if(minSimi<10){
		ret=getNumFromTD(minSimiEle.parentNode);
//console.log("ret:"+ret);
		if(ret>0 && ret<thisnum){return ret;}
	}
	return 0;
}
var qtEleArr=[];//array for quote popup
var qtSerial=0;//serial number for quote popup element id
function getCotentsByNo(no){//Create contents for quote popup
	var frag = document.createDocumentFragment();
	if(no==0){
		frag.appendChild(document.createTextNode("Unable to find source."));
		return frag;
	}
	var p=document.getElementById("delcheck"+no).parentNode;
	var jmp=document.createElement('span');
//	jmp.innerHTML="jump";
	jmp.innerHTML=document.getElementById("delcheck"+no).innerHTML;
	jmp.className="qtjmp";
	jmp.addEventListener("click",function (){
		if(!p.offsetHeight){
			dispdel=1;
			document.getElementById("ddbut").innerHTML=dispdel?"Hide":"Show";
			ddrefl();
			reszk();
		}
//		console.log(getScrollPosition(p).y);
		window.scroll(0,getScrollPosition(p).y);
	},false);
	var EScsb=p.getElementsByClassName("subject")[0];
	var csb;
	if(EScsb){
		csb=EScsb.cloneNode(true);
	}
	var d1=document.createTextNode("Name");
	var cnm;
	var EScnm=p.getElementsByClassName("nameBlock")[0];
	if(EScnm){
		var cnm=EScnm.cloneNode(true);
	}
	var cnw=p.getElementsByClassName("dateTime")[0].cloneNode(true);
	var cno=p.getElementsByClassName("postNum")[0].cloneNode(true);
	var com=p.getElementsByTagName("blockquote")[0].cloneNode(true);
	var marl=parseInt(com.style.marginLeft,10);
	if(marl>0){com.style.marginLeft=(marl/2)+"px";};
	var sd=document.createElement('span');
	sd.className="qsd";
	sd.addEventListener("click",qsd,false);
	sd.setAttribute('data-no', no);
	ESsd=p.getElementsByClassName("sod")[0];
	if(ESsd){
		sd.innerHTML=ESsd.innerHTML;
	}
	var imgele=p.getElementsByTagName("img")[0];
	if(imgele){
		var w=parseInt(imgele.getAttribute("width"),10)/2;
		var h=parseInt(imgele.getAttribute("height"),10)/2;
		var linkele=imgele.parentNode;
		var linkimg=linkele.cloneNode(false);
		var cimg=imgele.cloneNode(false);
		cimg.style.width=w+"px";
		cimg.style.height=h+"px";
		linkimg.appendChild(cimg);
		var filename=linkele.getAttribute("href").replace(/^.*\//,"");
		var fsize=imgele.getAttribute("alt");
		var linkfn=linkele.cloneNode(false);
		linkfn.innerHTML=filename;
		var d2=document.createTextNode("-("+fsize+")");
	}
	frag.appendChild(jmp);
	if(EScsb){
		frag.appendChild(csb);
	}
	if(EScnm){
		frag.appendChild(d1);
		frag.appendChild(cnm);
	}
	frag.appendChild(cnw);
	frag.appendChild(cno);
	frag.appendChild(sd);
	if(imgele){
		frag.appendChild(document.createElement('br'));
		frag.appendChild(fragmentFromString(" &nbsp; &nbsp; "));
		frag.appendChild(linkfn);//link+filename
		frag.appendChild(d2);//-(12300 B)
		frag.appendChild(document.createElement('br'));
		frag.appendChild(linkimg);//link+img
	}
	frag.appendChild(com);
	return frag;
}
function qtHasChildNode(el){//find child for quote popup
	for(var i=0;i<qtEleArr.length;i++){
		if(el===qtEleArr[i][2]){
			return true;
		}
		if(el===qtEleArr[i][0]){
			return true;
		}
	}
	return false;
}
function isEmptyArr(arr){//Checks if the array is empty
	for(var i=0;i<arr.length;i++){
		if(arr[i]!=0){
			return false;
		}
	}
	return true;
}
function closeqtpop(e){//close quote popup
	var t,m,len=qtEleArr.length-1;
	qtloop:
	for(var i=len;i>=0;i--){
		if(qtEleArr[i]==0){continue;}
		if(qtHasChildNode(qtEleArr[i][1])){continue;}
		for(var j=0;j<2;j++){
			t=qtEleArr[i][j];
			var xy=getScrollPosition(t);
			var w=t.offsetWidth;
			var h=t.offsetHeight;
			if(parseInt(xy.x,10)-2<=e.pageX && e.pageX<=parseInt(xy.x+w,10)+2 && parseInt(xy.y,10)-2<=e.pageY && e.pageY<=parseInt(xy.y+h,10)+2){
				continue qtloop;
			}
		}
		qtEleArr[i][1].parentNode.removeChild(qtEleArr[i][1]);
		qtEleArr[i]=0;
	}
	if(isEmptyArr(qtEleArr)){
		qtEleArr=[];
		document.removeEventListener("mousemove",closeqtpop,false);
	}
}
function openqtpop(e){//open quote popup
	var t = e.target;
	if(qtHasChildNode(t)){
		return;
	}
	var reg = /^&gt;/;
	if(!reg.test(t.innerHTML)){
		return;
	}
	var searchstr=t.innerHTML.replace(reg,"");
	var thisnum=getNumFromTD(t.parentNode.parentNode);
	var qtSrcNo=searchQtSrc(searchstr,thisnum);
	if(qtSrcNo==0 && document.getElementsByClassName('thread').length>1){
		return;
	}
//	var p = document.getElementsByClassName("thre")[0];
	var p=getAncestorElement(t,"thread");
	var m = document.createElement('div');
	m.className = "qtd";
	m.id = "qtd"+qtSerial;
	qtSerial++;
	var xy = getScrollPosition(t);
	var x = e.pageX-25;
	if(document.body.clientWidth-480<x){x=document.body.clientWidth-480;}
	if(x<0){x=0;}
	m.style.top = (xy.y+t.offsetHeight)+"px";
	m.style.left = (x)+"px";
	m.style.position = "absolute";
	var d1=getCotentsByNo(qtSrcNo);
	m.appendChild(d1);
	p.appendChild(m);
	var addEvent=false;
	if(isEmptyArr(qtEleArr)){
		addEvent=true;
	}
	qtEleArr.push([t,m,t.parentNode.parentNode]);
	m.addEventListener("mouseout",closeqtpop,false);
	if(addEvent){
		document.addEventListener("mousemove",closeqtpop,false);
	}
}

function qtpopup(e){//quote popup listner
	var t,timeout;
	if ((t = e.target) == document) {
		return;
	}
	if(t.parentNode && t.parentNode.nodeName.toLowerCase() == 'blockquote'){
		timeout=setTimeout(function(){openqtpop(e);},300);
		t.addEventListener('mouseleave', function(){clearTimeout(timeout)}, false);//add EventListener for quote popup
	}
}/*
function thumbonclick(e){//click event listner for video tag & pull-down menu
	var t;
	if("touches" in e && e.touches.length>0){
		e=e.touches[0];
	}
	if ((t = e.target) == document) {
		return;
	}
	if(t.parentNode && t.parentNode.nodeName.toLowerCase() == 'a'){
		if (webmopen(t)) {
			e.preventDefault();
		}
	}

	var c=t;
	var pdd=document.getElementById("pdd");
	var pdm=document.getElementById("pdm");
	var removepdm=false;
	var removepdd=false;
	var pdmno=-1;
	pdmchk:if(pdm){
		pdmno=pdm.getAttribute("data-no");
		while(c) {
			if(c == document){break;}
			if(c.id=="pdd" || c.id=="pdm"){break pdmchk;}
			c = c.parentNode;
		}
		removepdm=true;
		pdm.parentNode.removeChild(pdm);
	}

	var c=t;
	pddchk:if(pdd){
		while(c) {
			if(c == document){break;}
			if(c.id=="pdd"){break pddchk;}
			c = c.parentNode;
		}
//		console.log("pdd close");
		removepdd=true;
		pdd.parentNode.removeChild(pdd);
	}

	if(t.nodeName.toLowerCase() == 'span' && t.className == 'cno'){
		if(!removepdm||parseInt(t.innerHTML.replace("No.",""),10)!=pdmno){
			if (pdmopen(t)) {
				e.preventDefault();
			}
		}
	}
}*/
/*
function qsd(e){//send sodane for qtpop
	var xmlhttp = false;
	if(typeof ActiveXObject != "undefined"){
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e) {
			xmlhttp = false;
		}
	}
	if(!xmlhttp && typeof XMLHttpRequest != "undefined") {
		xmlhttp = new XMLHttpRequest();
	}
	var sno=e.target.getAttribute("data-no");
	xmlhttp.open("GET", "/sd.php?"+b+"."+sno);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 ) {
			var i=parseInt(xmlhttp.responseText,10);
			if(isNaN(i)){i=0;}
//			document.getElementById("sd"+sno).innerHTML="そうだねx"+i;
			document.getElementById("sd"+sno).innerHTML="そうだねx"+i;
			e.target.innerHTML="そうだねx"+i;
		}
	};
	xmlhttp.send(null);
	return false;
}
*/
/********************* selection quote *********************/
function slpwrite(e){
//	e.stopPropagation();
	var no=0,ti="";
	var ftxa=document.getElementById("com");
	if(!ftxa){return;}
	var slptmp=document.getElementById("slptmp");
	if(!slptmp){return;}
	var txt=slptmp.innerHTML;
//txt='>'+txt.replace(/\r\n/gm, "\n").replace(/\r/gm, "\n").replace(/\n+/gm, "\n").replace(/\n\z/, "").replace(/\n/g, "\n>")+"\n";
	txt='>'+txt.replace(/\r\n/gm, "\n").replace(/\r/gm, "\n").replace(/\n+/g, "\n").replace(/\n$/, "").replace(/\n/g, "\n>")+"\n";
	ftxa.value+=unescapeHTML(txt);
	scrollToEle(ftxa);
	var slpel=document.getElementById("slp");
	if(slpel){
		slpel.parentNode.removeChild(slpel);
	}
}

function slpopen(e){//open select popup button
	var p = document.getElementsByClassName("thread")[0];
	var m = document.createElement('div');
		m.className = "slp";
		m.id = "slp";
	var xy={x:e.pageX,y:e.pageY};
	var winh = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	if(window.screen.width<=799){
		m.style.top = (xy.y)-25+"px";
		m.style.left = (xy.x)-15+"px";
	}else{
		m.style.top = (xy.y)-38+"px";
		m.style.left = (xy.x)-25+"px";
	}
	m.style.position = "absolute";
	var d1=document.createElement('div');
	d1.innerHTML="Quote";
	d1.addEventListener('click', function(){slpwrite(arguments[0]);}, false);
	d1.className="slp1 reply";
	var d2=document.createElement('span');
	d2.innerHTML=getSelectTxt();
	d2.id="slptmp";
	m.appendChild(d1);
	m.appendChild(d2);
	p.appendChild(m);
	adjustElement(m);
	return true;
}
function getSelectTxt(){
	var selectStr="",selection;
	if(document.selection) {//for IE8
//		selectStr = document.selection.createRange().text;
	}else{
		selection = window.getSelection();
		if(true || window.navigator.userAgent.toLowerCase().match(/trident.*rv:11\./)){
			if(selection.rangeCount<1){return "";}
			var els=selection.getRangeAt(0).cloneContents().childNodes;
			for(var i=0;i<els.length;i++){
				if(els[i].nodeType==1){
					selectStr+=els[i].outerHTML;
				}
				if(els[i].nodeType==3){
					selectStr+=els[i].nodeValue;
				}
			}
//console.log(selectStr);
			selectStr=selectStr.replace(/<br>|<blockquote>/ig,"\n");
			selectStr=selectStr.replace(/<\/?font("[^"]*"|'[^']*'|[^'">])*>/ig,'');
			selectStr=selectStr.replace(/(<("[^"]*"|'[^']*'|[^'">])*>)+/g,' ');
			selectStr=selectStr.replace(/(^|\n) +/gm,'$1');
			selectStr=selectStr.replace(/ +(\n|$)/gm,"$1");
			selectStr=selectStr.replace(/\n+/gm,'\n');
			selectStr=selectStr.replace(/^\n/gm,'');
//console.log(escape(selectStr));
		}else{
			selectStr = selection.toString().replace(/^ */gm,'').replace(/^\n\n/gm,'');
		}
	}
	return selectStr;
}

function selectpop(e){
	setTimeout(function(){
		var slpel=document.getElementById("slp");
		if(slpel){
			slpel.parentNode.removeChild(slpel);
		}
		var txt=getSelectTxt();
//	console.log(txt+" "+txt.length);
		if(txt.length>0){
			slpopen(e);
		}
	},50);
}

/********************* thread post/display  *********************/
function onddbut(){//button for display deleted article
	dispdel=1-dispdel;
	document.getElementById("ddbut").innerHTML=dispdel?"Hide":"Show";
	ddrefl();
	reszk();
}
/*function ptfk(resn) { //submit reply
	document.getElementById('js').value = 'on';
	document.getElementById('scsz').value = screen.width + 'x' + screen.height + 'x' + screen.colorDepth;
	var cacv = '';
	try {
		cacv = caco();
	} catch (e) {
	}
	document.getElementById('pthc').value = cacv;
	try {
		lspt = window.localStorage.futabapt;
		fpt = true;
	} catch (e) {
		fpt = false;
	}
	if (fpt) {
		if (lspt != null && lspt != '') {
			document.getElementById('pthb').value = lspt;
		} else {
			if (cacv != null && cacv != '') {
				try {
					window.localStorage.futabapt = cacv;
				} catch (e) {
				};
			}
		}
	}
	if (resn > 0 && reszflg) {
		var scrly = document.documentElement.scrollTop || document.body.scrollTop;
		document.cookie = 'scrl=' + resn + '.' + scrly + '; max-age=60;';
	}
	if(document.getElementById('oejs')){
		tegakiJs.oeUpdate();
	}
	var sph = (function () {
		var u = 'undefined';
		return {
			0: typeof window.addEventListener == u && typeof document.documentElement.style.maxHeight == u,
			1: typeof window.addEventListener == u && typeof document.querySelectorAll == u,
			2: typeof window.addEventListener == u && typeof document.getElementsByClassName == u,
			3: !!document.uniqueID && document.documentMode == 9,
			4: !!document.uniqueID && document.documentMode == 10,
			5: !!document.uniqueID && document.documentMode == 11,
			6: !!document.uniqueID,
			7: '-ms-scroll-limit' in document.documentElement.style && '-ms-ime-align' in document.documentElement.style && !window.navigator.msPointerEnabled,
			8: 'MozAppearance' in document.documentElement.style,
			9: !!window.sidebar,
			10: typeof window.navigator.onLine != u,
			11: !!window.sessionStorage,
			12: (function x(){})[-5]=='x',
			13: typeof document.currentScript != u,
			14: typeof (EventSource) != u,
			15: !!window.crypto && !!window.crypto.getRandomValues,
			16: !!window.performance && !!window.performance.now,
			17: !!window.AudioContext,
			18: !!window.indexedDB,
			19: !!window.styles,
			20: !!window.navigator.sendBeacon,
			21: !!navigator.getGamepads || !!navigator.getGamepads,
			22: !!window.navigator.languages,
			23: !!window.navigator.mediaDevices,
			24: !!window.caches,
			25: !!window.createImageBitmap,
			26: typeof window.onstorage != u,
			27: !!window.navigator.getBattery,
			28: !!window.opera,
			29: !!window.chrome && typeof window.chrome.webstore != u,
			30: !!window.chrome && 'WebkitAppearance' in document.documentElement.style,
			31: typeof document.ontouchstart != u,
			32: typeof window.orientation != u
		}
	}) ();
	var k,i = 0;
	for (k in sph) i += sph[k] * Math.pow(2, k);
	k = document.getElementById('ptua');
	if (k) k.value = i;
	var fm = document.getElementById('fm');
	var oReq = new XMLHttpRequest();
 if (!resn || !fm || !fm.action || fm.method.toLowerCase() !== "post" || !window.FormData) { return true; }
	var tretmes=document.getElementById("retmestip");
	if(!tretmes){
		var inputtags=fm.getElementsByTagName("input");
		for(var i in inputtags){
			if(inputtags[i].type=="submit"){
				tretmes=document.createElement('span');
				tretmes.id="retmestip";
				tretmes.style.marginLeft="8px";
				inputtags[i].parentNode.appendChild(tretmes);
				break;
			}
		}
	}
	tretmes.innerHTML = "・・・";
	oReq.onload = function (){
		var restxt=this.responseText,stok;
//		console.log(restxt);
		if(restxt=="ok"){
			fm.reset();
			if(t=document.getElementById("oebtnf")){t.style.display="block";}
			if(t=document.getElementById("oebtnfm")){t.style.display="none";}
			if(t=document.getElementById("oebtnj")){t.style.display="block";}
			if(t=document.getElementById("oebtnjm")){t.style.display="none";}
			if(t=document.getElementById("oebtnud")){t.style.display="none";}
			if(t=document.getElementById("ftxa")){t.style.visibility="visible";}
			if(t=document.getElementById("baseform")){t.value="";}
			if(document.getElementById('oejs')){
				tegakiJs.reset();
			}
			if(t=document.getElementById('swfContents')){t.innerHTML='<div id="oe3"></div>';}
			swfloaded=false;
			scrlf(resn);
			l();
			tretmes.innerHTML = "完了";
			if(typeof(stok)!="undefined" && stok>0){
				clearTimeout(stok);
			}
			stok=setTimeout(function(){tretmes.innerHTML="";},2000);
			var cont=document.getElementById("contres");
			if(cont && reszflg==0){
				var bodyHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
				window.scroll(0,cont.offsetTop - bodyHeight/2);
			}
		}else{
			if(restxt.match(/<html[> ]|<body[> ]/i)){
				restxt="エラー\n"+restxt.match(/<title(?:| [^>]*)>(.*?)<\/title(?:| [^>]*)>/i)[1];
			}
			alert(restxt);
			tretmes.innerHTML = "";
		}
	};
	oReq.open("post", fm.action);
	var oData = new FormData(fm);
	oData.append("responsemode", "ajax");
	if(iOSversion()==11){
		var tmp_file = oData.get("upfile");//palemoon27 bug as "FormData.get"
		if((typeof tmp_file)=="object" && ("size" in tmp_file) && tmp_file["size"]==0){//iOS11.x bug
			oData["delete"]("upfile");//IE7 bug as "FormData.delete"
		}
	}
	oReq.send(oData);
	return false;
}
*/
function iOSversion() {
  if (/iP(hone|od|ad)/.test(navigator.platform)) {
    var v = (navigator.appVersion).match(/OS (\d+)_(\d+)_?(\d+)?/);
    return parseInt(v[1], 10);
  }
}

function ucount(){//count unique user
//	var uuc=getCookie("uuc");
//	if(uuc!=1){
//		document.write("<img src=\"//dec.2chan.net/bin/uucount.php?"+Math.random()+"\" width=2 height=2>");
//		document.cookie="uuc=1; max-age=3600; path=/;";
//	}
//	var flashvars={};
//	var params={};
//	var attributes={id:"cnt"};
//	swfobject.embedSWF("/bin/count.swf","usercounter","0","0","9.0.124","/bin/expressInstall.swf",flashvars,params,attributes);
}/*
function setpthd(prop){//catch flash variable
	document.getElementById("pthd").value=prop;
}*//*
var contd,contdbk;//reload status
function scrlf(resn){//reload updated article
	var xmlhttp = false;
	if(typeof ActiveXObject != "undefined"){
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		}catch(e){
			xmlhttp = false;
		}
	}
	if(!xmlhttp && typeof XMLHttpRequest != "undefined") {
		xmlhttp = new XMLHttpRequest();
	}
	contd=document.getElementById("contdisp");
	if(typeof(contdbk)=="undefined"){
		contdbk=contd.innerHTML;
	}
	contd.innerHTML="・・・";
	xmlhttp.open("HEAD", "/"+b+"/res/"+resn+".htm?"+Math.random());
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 ) {
			var xhst=xmlhttp.status;
			if(xhst==404){
				contd.innerHTML="<font color=\"#ff0000\">スレッドがありません<\/font>";
				return;
			}
			if(xhst!=200){
				contd.innerHTML = "<font color=\"#ff0000\">通信エラー<\/font>";
				return;
			}
			var reloadhtml=false;
			if (!window.JSON) {
				var wdl = Date.parse(window.document.lastModified);
				if('\v'!='v' && window.execScript){
					var wdld=new Date();
					wdl-=wdld.getTimezoneOffset()*60000;
				}
				var xgl = Date.parse(xmlhttp.getResponseHeader("Last-Modified"));
				if(wdl==xgl){
					contd.innerHTML = "新着無し";
					if(typeof(stof)!="undefined"&&stof>0){
						clearTimeout(stof);
					}
					stof=setTimeout(function(){contd.innerHTML=contdbk;},1000);
				}else{
					reloadhtml=true;
				}
			}else{
				var max= getThreadMax();
//console.log("max="+max);
				if(!getJson(resn,parseInt(max,10)+1)){
					reloadhtml=true;
				}
			}
			if(reloadhtml){
				var scrly = document.documentElement.scrollTop || document.body.scrollTop;
				document.cookie="scrl="+resn+"."+scrly+"; max-age=60;";
				location.href="/"+b+"/res/"+resn+".htm";
			}
		}
	};
	xmlhttp.send(null);
	return false;
}*/
function getThreadMax(){//get max number in thread
	var th = document.getElementsByClassName("thread")[0];
	var inps=th.getElementsByTagName('span');
	var max=0;
	for( var i = 0; i < inps.length; i++  ){
		var no=inps[i].id;
		if(!no){
			continue;
		}
		no=no.replace("delcheck","");
		if(max<parseInt(no,10)){
			max=parseInt(no,10);
		}
	}
	return max;
}

function replaceRes(threadNo,no) {//get article by json
	var xmlhttp2 = createXMLHttpRequest();
	if(!xmlhttp2){return false;}
	xmlhttp2.onreadystatechange = function () {
		if (xmlhttp2.readyState == 4) {
			if (xmlhttp2.status == 200) {
				var restxt=xmlhttp2.responseText;
				if(restxt.slice(0,1)!="{"){restxt="{}";}
				var ret=JSON.parse(restxt);
				var data = ret["res"];
				for (var no in data){
					var t=makeArticle(ret,no);
					var el=document.getElementById("delcheck"+no);
					while(el && el!=document){
						if(el.tagName.toLowerCase()=="table"){
							break;
						}
						el=el.parentNode;
					}
					el.parentNode.replaceChild(t,el);
				}
			}
		}
	};
	xmlhttp2.open("GET", "/"+b+"/futaba.php?mode=json&res="+threadNo+"&start="+no+"&end="+no+"&"+Math.random());
	xmlhttp2.send(null);
	return true;
}
/*
function getJson(res,start) {//get article by json
	var xmlhttp2 = createXMLHttpRequest();
	if(!xmlhttp2){return false;}
	xmlhttp2.onreadystatechange = function () {
		if (xmlhttp2.readyState == 4) {
			if (xmlhttp2.status == 200) {
				var restxt=xmlhttp2.responseText;
				if(restxt.slice(0,1)!="{"){restxt="{}";}
				updateres(JSON.parse(restxt),res,true);
				//ad reload
				var ife = document.getElementsByTagName('iframe');
				for(i=0;i<ife.length;i++){
				//	ife[i].parentNode.replaceChild(ife[i].cloneNode(), ife[i]);
				}
			} else {
				
			}
		}
	};
	xmlhttp2.open("GET", "/"+b+"/futaba.php?mode=json&res="+res+"&start="+start+"&"+Math.random());
	xmlhttp2.send(null);
	return true;
}
*/
function makeArticle(ret,no){//Create an article called from user-delete and updateres
	var data = ret["res"];
//console.log(data);
	var t = document.createElement('table');
		t.border=0;
		if(data[no].del=='del'||data[no].del=='selfdel'||data[no].del=='admindel'){
			t.className="deleted";
		}
	var tb = document.createElement('tbody');
	var tr = document.createElement('tr');
	var rts = document.createElement('td');
		rts.className="rts";
		rts.appendChild(document.createTextNode("…"));
	var rtd = document.createElement('td');
		rtd.className="rtd";
	var inp = document.createElement('span');
		inp.id="delcheck"+no;
		inp.className="rsc";
		inp.innerHTML=data[no].rsc;
	var t1,t2,t3,t12,a5;
	t12 = document.createElement('span');
	t12.className="cnw";
	if(ret["dispname"]>0){
		t1 = document.createElement('span');
			t1.innerHTML+=data[no].sub;
			t1.className="csb";
		t2 = document.createTextNode('Name');
		t3 = document.createElement('span');
		if(data[no].email!=''){
			a5 = document.createElement('a');
			a5.href="mailto:"+(unescapeHTML(data[no].email));
			a5.innerHTML+=(data[no].name);
			t3.appendChild(a5);
		}else{
			t3.innerHTML+=(data[no].name);
		}
		t3.className="cnm";
		t12.appendChild(fragmentFromString(data[no].now));
	}else{
		if(data[no].email!=''){
			var t12a = document.createElement('a');
			t12a.innerHTML+=(data[no].now);
			t12a.href="mailto:"+(unescapeHTML(data[no].email));
			t12.appendChild(t12a);
		}else{
			t12.innerHTML+=(data[no].now);
		}
	}
	var t11='';
	if(data[no].id!=''){
		t11=' '+data[no].id+' ';
	}
	var t4 = document.createTextNode(t11);
	var t4a = document.createElement('span');
		t4a.className="cno";
		t4a.innerHTML="No."+no;
	var a2;
	if(ret["dispsod"]>0){
		a2 = document.createElement('a');
		a2.appendChild(document.createTextNode("+"));
		a2.className="sod";
		a2.id="sd"+no;
		a2.href="javascript:void(0)";
		a2.onclick=new Function("sd("+no+");return(false);");
	}
	if(data[no].ext!=''){
		var b1=document.createElement('br');
		var t5 = document.createTextNode(" \u00A0 \u00A0 ");
		var a3 = document.createElement('a');
			a3.appendChild(document.createTextNode(data[no].tim + data[no].ext));
			a3.href=data[no].src;
			a3.target="_blank";
		var t6 = document.createTextNode('-('+data[no].fsize+' B) ');
		var t7 = document.createElement('span');
			t7.appendChild(document.createTextNode('サムネ表示'));
			t7.style.fontSize="small";
		var b2=document.createElement('br');
		var a4=document.createElement('a');
			a4.href=data[no].src;
			a4.target="_blank";
		var i1=document.createElement('img');
			i1.src=data[no].thumb;
			i1.border=0;
			i1.align="left";
			i1.width=data[no].w;
			i1.height=data[no].h;
			i1.hspace=20;
			i1.alt=data[no].fsize+" B";
	}
	var bl=document.createElement('blockquote');
	if(data[no].w>0){
		bl.style.marginLeft=(data[no].w+40)+"px";
	}
	if(data[no].del=='del'){
		var t9=document.createElement('span');
		t9.style.color="#ff0000";
		t9.appendChild(document.createTextNode('スレッドを立てた人によって削除されました'));
		bl.appendChild(t9);
		bl.appendChild(document.createElement('br'));
	}
	if(data[no].host!=''){
		bl.appendChild(document.createTextNode('['));
		var t10=document.createElement('span');
		t10.style.color="#ff0000";
		t10.appendChild(document.createTextNode(data[no].host));
		bl.appendChild(t10);
		bl.appendChild(document.createTextNode(']'));
		bl.appendChild(document.createElement('br'));
	}

	bl.innerHTML+=data[no].com;
	rtd.appendChild(inp);
	if(ret["dispname"]>0){
		rtd.appendChild(t1);
		rtd.appendChild(t2);
		rtd.appendChild(t3);
	}
	rtd.appendChild(t12);
	rtd.appendChild(t4);//ID
	rtd.appendChild(t4a);//No
	if(ret["dispsod"]>0){
		rtd.appendChild(a2);
	}
	if(data[no].ext!=''){
		rtd.appendChild(b1);
		rtd.appendChild(t5);
		rtd.appendChild(a3);
		rtd.appendChild(t6);
		rtd.appendChild(t7);
		rtd.appendChild(b2);
		a4.appendChild(i1);
		rtd.appendChild(a4);
	}
	rtd.appendChild(bl);
	tr.appendChild(rts);
	tr.appendChild(rtd);
	tb.appendChild(tr);
	t.appendChild(tb);
	return t;
}
/*
function updateres(ret,res,makecache){//append updated article
	var data = ret["res"],updated=false;
	var th = document.getElementById("delcheck"+res).parentNode;
	var max=0;
	if(makecache){
		var ss,sstmp;
		if(window.sessionStorage){
			sstmp=window.sessionStorage.getItem(b+"_"+res);
		}
		if(sstmp){
			ss=JSON.parse(sstmp);
		}else{
			ss={};
		}
		if(!("res" in ss)){
			ss={"res":{}};
		}
	}
	for (var no in data){
		if(document.getElementById("delcheck"+no) ){
			continue;
		}
		var t=makeArticle(ret,no);
		th.appendChild(t);
		if(max<no){max=no;}
		reszk();
		updated=true;
		if(makecache){
			if(!(no in ss["res"])){
				ss["res"][no]=data[no];
			}
		}
	}
//	window.localStorage.cachedetect=JSON.stringify({"href":location.href,"max":max});
	if('sd' in ret){
		for (var so in ret["sd"]){
			var elesd=document.getElementById("sd"+so);
			if(elesd){
				if(ret["sd"][so]>0){
					elesd.innerHTML='そうだねx'+ret["sd"][so];
				}else{
					elesd.innerHTML='+';
				}
			}
		}
	}
	if(makecache){
		for(var key in ret){
			if(key!="res"){
				ss[key]=ret[key];
			}
		}
		window.sessionStorage[b+"_"+res] = JSON.stringify(ss);
	}
	if('die' in ret){
		contdbk=ret["die"]+'頃消えます';
//		th.getElementsByClassName("cntd")[0].innerHTML=ret["die"]+'頃消えます';
		//document.getElementsByClassName("cntd")[0].innerHTML=ret["die"]+'頃消えます';
	}
	if('omittedposts' in ret && ret["omittedposts"]!=''){
		var maxres=document.getElementsByClassName("omittedposts");
		for (var i=0;i<maxres.length;i++) {
			maxres[i].innerHTML='';
			maxres[i].appendChild(document.createTextNode(ret["omittedposts"]));
			maxres[i].appendChild(document.createElement('br'));
		}
	}
	if(!updated){
		contd.innerHTML = "新着無し";
		if(typeof(stof)!="undefined"&&stof>0){
			clearTimeout(stof);
		}
		stof=setTimeout(function(){contd.innerHTML=contdbk;},1000);
	}else{
		contd.innerHTML=contdbk;
	}
	var cdel=document.getElementById("contdisp");
	if('old' in ret && cdel!=null){
		if(ret['old']==1){
			cdel.style.color="#ff0000";
		}else{
			cdel.style.color="";
		}
	}
}
*/
function createXMLHttpRequest() {//create xhr
	if (window.XMLHttpRequest) { return new XMLHttpRequest() }
	if (window.ActiveXObject) {
		try { return new ActiveXObject("Msxml2.XMLHTTP.6.0") } catch (e) { }
		try { return new ActiveXObject("Msxml2.XMLHTTP.3.0") } catch (e) { }
		try { return new ActiveXObject("Microsoft.XMLHTTP") } catch (e) { }
	}
	return false;
}

function unescapeHTML(str) {//unescape comment string
	var div = document.createElement("div");
	div.innerHTML = str.replace(/</g,"&lt;")
	.replace(/>/g,"&gt;")
	.replace(/\r/g, "&#13;")
	.replace(/\n/g, "&#10;");
	return div.textContent || div.innerText;
}
function scrll(){//scroll when reload
  var scrly=getCookie("scrl").split(".");
  if(scrly[1]!=null &&  scrly[1]>0 && document.getElementsByName("resto").item(0).value == scrly[0]){
   window.scroll(0,scrly[1]);
  }
  document.cookie="scrl=; max-age=0;";
}
if(getCookie("reszc")==1){//hide moved form
  document.cookie="reszc=1; max-age=864000; path=/"+b+";";
  document.write("<style TYPE=\"text/css\">#ftbl{position:absolute;visibility:hidden;}<\/style>");
}
var reszflg=0;//flag for moving form

//function delsubmit(e){//button to delete own article
////	e.preventDefault();
//	var fm=document.getElementById("hideform").parentNode;
//	fm.onlyimgdel.checked=document.delform2.onlyimgdel.checked;
//	fm.pwd.value=document.delform2.pwd.value;
//	fm.submit();
//	return false;
//}

function goBottom(){
	var cont=document.getElementById("bottom");
	if(cont){
		var bodyHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
		window.scroll(0,cont.offsetTop - bodyHeight/2);
	}
}

function addScrollButton(){
	if ((navigator.userAgent.indexOf('iPhone') > 0 && navigator.userAgent.indexOf('iPad') == -1) ||
	navigator.userAgent.indexOf('iPod') > 0 || navigator.userAgent.indexOf('Android') > 0) {
		var isTouchSupport = 'ontouchstart' in window;
		var gotop=document.createElement('div');
		gotop.className="gotop";
		if(isTouchSupport){
			var gotopTouched=false;
			gotop.addEventListener('touchstart',function(){gotopTouched=true;},false);
			gotop.addEventListener('touchmove',function(){gotopTouched=false;},false);
			gotop.addEventListener('touchend',function(){if(gotopTouched){window.scroll(0,0);}},false);
		}else{
			gotop.addEventListener('click',function(){window.scroll(0,0);},false);
		}
		document.body.appendChild(gotop);
		var gobtm=document.createElement('div');
		gobtm.className="gobtm";
		if(isTouchSupport){
			var gobtmTouched=false;
			gobtm.addEventListener('touchstart',function(){gobtmTouched=true;},false);
			gobtm.addEventListener('touchmove',function(){gobtmTouched=false;},false);
			gobtm.addEventListener('touchend',function(){if(gobtmTouched){goBottom();}},false);
		}else{
			gobtm.addEventListener('click',function(){goBottom();},false);
		}
		document.body.appendChild(gobtm);
	}
}
/*
function reszt(){//display form on top
	var ofm=document.getElementById("postform");
	var oufm=document.getElementById("bottom");
	if(oufm==null||ofm==null){return;}
	ofm.style.position="static";
	oufm.style.lineHeight="0px";
	oufm.innerHTML="";
	ofm.style.marginLeft="auto";
	ofm.style.visibility="visible";
}
function reszu(){//display form below
	var ofm=document.getElementById("postform");
	var oufm=document.getElementById("bottom");
	if(oufm==null||ofm==null){return;}
	oufm.style.lineHeight=ofm.offsetHeight+"px";
	oufm.innerHTML="&nbsp;";
	ofm.style.position="absolute";
	ofm.style.left="50%";
	ofm.style.marginLeft="-"+(ofm.offsetWidth/2)+"px";
	ofm.style.top=(document.body.offsetTop+oufm.offsetTop)+"px";
	ofm.style.visibility="visible";
}
function reszk(){//set up form position
	if(document.getElementById("postform")==null){
		tmpobj=document.getElementById("reszb");
		if(tmpobj!=null){
			tmpobj.innerHTML="";
		}
		tmpobj=document.getElementById("contres");
		if(tmpobj!=null){
			tmpobj.innerHTML="";
		}
		return;
	}
	var resztmp=getCookie("reszc");
	if(resztmp!=""&&resztmp!=null){
		reszflg=resztmp;
		if(reszflg==1){reszu();}else{reszt();}
	}
}
function reszx(){//change form position
	reszflg=1-reszflg;
	document.cookie="reszc="+reszflg+"; max-age=864000; path=/"+b+";";
	reszk();
	window.scroll(0,document.getElementById("ftbl").offsetTop);
}

function sd(sno){//send sodane
	var xmlhttp = false;
	if(typeof ActiveXObject != "undefined"){
		try {
			xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		} catch (e) {
			xmlhttp = false;
		}
	}
	if(!xmlhttp && typeof XMLHttpRequest != "undefined") {
		xmlhttp = new XMLHttpRequest();
	}
	xmlhttp.open("GET", "/sd.php?"+b+"."+sno);
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState == 4 ) {
			var i=parseInt(xmlhttp.responseText,10);
			if(isNaN(i)){i=0;}
			document.getElementById("sd"+sno).innerHTML="そうだねx"+i;
		}
	};
	xmlhttp.send(null);
	return false;
}
*/
/********************* webm/mp4 show *********************/
/*
function volchangeWebm() {//change volume on video tag
	try{
		window.localStorage.futabavideo=this.volume+","+this.muted+","+this.loop;
	}catch(e){}
}
function adjWebm() {//change width and height on video tag
	var imgWidth, imgHeight, maxWidth, maxHeight, ratio, left;
	left = this.getBoundingClientRect().left;
	thre = document.getElementsByClassName('thread')[0];
	if(thre){
		maxWidth = thre.clientWidth - left - 60;
	}else{
		maxWidth = document.documentElement.clientWidth - left - 240;
	}
	maxHeight = document.documentElement.clientHeight;
	imgWidth = this.videoWidth;
	imgHeight = this.videoHeight;
	if (imgWidth > maxWidth) {
		ratio = maxWidth / imgWidth;
		imgWidth = maxWidth;
		imgHeight = imgHeight * ratio;
	}
	this.style.maxWidth = (0 | imgWidth) + 'px';
	this.style.maxHeight = (0 | imgHeight) + 'px';
	if(typeof reszk=="function"){
		reszk();
	}
}
function close(e) {//close video tag
	var cnt, el;
	e.preventDefault();
	this.removeEventListener('click', close, false);
	cnt = this.parentNode;
	el = cnt.parentNode.getElementsByClassName('extendWebm')[0];
	el.pause();
	el.parentNode.nextSibling.style.display = '';
	el.parentNode.removeChild(el);
	cnt.parentNode.removeChild(cnt);
	if(typeof reszk=="function"){
		reszk();
	}
}
function extWebm(thumb,ext) {//extend video tag
	var d0, d1, d2, d3, s1, s2, el, link, href, vs, fvi;
	link = thumb.parentNode;
	href = link.getAttribute('href');

	d0 = document.createElement('div');
	d1 = document.createElement('div');
	d1.className = 'cancelbk';
	d1.addEventListener('click', close, false);
	d2 = document.createElement('div');
	d2.className = 'cancel';
	d3 = document.createElement('div');
	d3.className = 'clearboth';

	el = document.createElement('video');
	el.autoplay = true;
	el.controls = true;
	try{vs=window.localStorage.futabavideo;fvi=true;}catch(e){fvi=false;}
	if(!fvi||!vs){
		el.volume = 0.5;
		el.loop = true;
		el.muted = false;
	}else{
		var vsvs = vs.split(',');
		el.volume = vsvs[0];
		if(vsvs[1]=='true'){
			el.muted = true;
		}
		if(vsvs[2]=='true'){
			el.loop = true;
		}
	}
	el.className = 'extendWebm';
	el.onloadedmetadata = adjWebm;
	el.onvolumechange = volchangeWebm;
	el.onmouseout = volchangeWebm;
	link.style.display = 'none';

	d0.appendChild(el);
	d1.appendChild(d2);
	d0.appendChild(d1);
	d0.appendChild(d3);
	link.parentNode.insertBefore(d0,link);
//  el.src = href;
	if(ext==".webm"){
		s1 = document.createElement('source');
		s1.src = href;
		s1.type = "video/webm";
	}
	s2 = document.createElement('source');
	s2.src = href.replace(/\.webm$/,".mp4");
	s2.type = "video/mp4";
	if(ext==".webm"){
		el.appendChild(s1);
	}
	el.appendChild(s2);

	return true;
}
function webmopen(thumb){//load movie on video tag
	var href, ext;
	href = thumb.parentNode.getAttribute('href');
	if (ext = href.match(/(\.[^.]+$)/)) {
	if (ext[0] == '.webm'||ext[0] == '.mp4') {
		return extWebm(thumb,ext[0]);
	}
	return false;
	}
}
*/
/********************* miscellaneous *********************/
function getScroll(){//scroll代替
	var supportPageOffset = window.pageXOffset !== undefined;
	var isCSS1Compat = ((document.compatMode || "") === "CSS1Compat");
	var x = supportPageOffset ? window.pageXOffset : isCSS1Compat ? document.documentElement.scrollLeft : document.body.scrollLeft;
	var y = supportPageOffset ? window.pageYOffset : isCSS1Compat ? document.documentElement.scrollTop : document.body.scrollTop;
	return {x: x, y: y};
}

function getScrollPosition(elm){//getBoundingClientRect + scroll
	var xy=getPosition(elm);
	var scroll=getScroll();
	return {x: scroll.x+xy.x, y: scroll.y+xy.y};
}

function getPosition(elm) {//getBoundingClientRect代替
	if(typeof elm.getBoundingClientRect == 'function'){
		return {x: elm.getBoundingClientRect().left,
						y: elm.getBoundingClientRect().top};
	}else{
		var xPos = 0, yPos = 0;
		while(elm) {
			xPos += (elm.offsetLeft + elm.clientLeft);
			yPos += (elm.offsetTop  + elm.clientTop);
			elm = elm.offsetParent;
		}
		var isCSS1Compat = ((document.compatMode || "") === "CSS1Compat");
		xPos -= (isCSS1Compat ? document.documentElement.scrollLeft : document.body.scrollLeft);
		yPos -= (isCSS1Compat ? document.documentElement.scrollTop : document.body.scrollTop);
		return { x: xPos, y: yPos };
	}
}

function fragmentFromString(strHTML) {//html文字列から断片を生成する
	var frag = document.createDocumentFragment(),tmp = document.createElement('body'), child;
	tmp.innerHTML = strHTML;
	while (child = tmp.firstChild) {
		frag.appendChild(child);
	}
	return frag;
}

//var scriptSource = (function() {//get this script source name
//	var scripts = document.getElementsByTagName('script'),
//		script = scripts[scripts.length - 1];
//	if (script.getAttribute.length !== undefined) {
//		return script.getAttribute('src')
//	}
//	return script.getAttribute('src', 2)
//}());

function levenshtein(s1, s2) {//Levenshtein distance
	// http://kevin.vanzonneveld.net
	// +            original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
	// +            bugfixed by: Onno Marsman
	// +             revised by: Andrea Giammarchi (http://webreflection.blogspot.com)
	// + reimplemented by: Brett Zamir (http://brett-zamir.me)
	// + reimplemented by: Alexander M Beedie
	// *                example 1: levenshtein('Kevin van Zonneveld', 'Kevin van Sommeveld');
	// *                returns 1: 3
	if (s1 == s2) {
		return 0;
	}
	var s1_len = s1.length;
	var s2_len = s2.length;
	if (s1_len === 0) {
		return s2_len;
	}
	if (s2_len === 0) {
		return s1_len;
	}  // BEGIN STATIC
	var split = false;
	try {
		split = !('0') [0];
	} catch (e) {
		split = true; // Earlier IE may not support access by string index
	}  // END STATIC
	if (split) {
		s1 = s1.split('');
		s2 = s2.split('');
	}
	var v0 = new Array(s1_len + 1);
	var v1 = new Array(s1_len + 1);
	var s1_idx = 0,
	s2_idx = 0,
	cost = 0;
	for (s1_idx = 0; s1_idx < s1_len + 1; s1_idx++) {
		v0[s1_idx] = s1_idx;
	}
	var char_s1 = '',
	char_s2 = '';
	for (s2_idx = 1; s2_idx <= s2_len; s2_idx++) {
		v1[0] = s2_idx;
		char_s2 = s2[s2_idx - 1];
		for (s1_idx = 0; s1_idx < s1_len; s1_idx++) {
			char_s1 = s1[s1_idx];
			cost = (char_s1 == char_s2) ? 0 : 1;
			var m_min = v0[s1_idx + 1] + 1;
			var b = v1[s1_idx] + 1;
			var c = v0[s1_idx] + cost;
			if (b < m_min) {
				m_min = b;
			}
			if (c < m_min) {
				m_min = c;
			}
			v1[s1_idx + 1] = m_min;
		}
		var v_tmp = v0;
		v0 = v1;
		v1 = v_tmp;
	}
	return v0[s1_len];
}

/********************* flash/canvas drawing  *********************/
function FlashPlayerVer(){//get flash version
	var flashplayer_ver=0;
	if(navigator.plugins && navigator.mimeTypes["application/x-shockwave-flash"]){
		var plugin = navigator.mimeTypes["application/x-shockwave-flash"].enabledPlugin;
		if(plugin){
			flashplayer_ver=parseInt(plugin.description.match(/\d+\.\d+/));
		}
	}else{
		try{
			var flashOCX=new ActiveXObject("ShockwaveFlash.ShockwaveFlash").GetVariable("$version").match(/([0-9]+)/);
			if(flashOCX){
				flashplayer_ver = parseInt(flashOCX[0]);
			}
		}catch(e){}
	}
	if(flashplayer_ver<=6){
		flashplayer_ver=0;
	}
	return flashplayer_ver;
}/*
function setBase(prop){//catch canvas from tegakiFlash
	document.getElementById("baseform").value=prop;
}*/
var swfloaded=false;//flag for tegakiFlash loaded
/*
function ChangeDraw(mode){//change form to tegakiFlash or tegakiJs
	var d=document,txvs,oevs,t,f=false,fm=false,j=false,jm=false,v="visible",h="hidden",b="block",n="none";
	if(!swfloaded){
		swfloaded=true;
		if(mode=="j"){
			tegakiJs.Init("oe3",344,135);
		}else{
			var flashvars={};
			var params={};
			var attributes={id:"oe3"};
			swfobject.embedSWF("/bin/oe3_7.swf","oe3","390","135","9.0.124","/bin/expressInstall.swf",flashvars,params,attributes);
			d.getElementById("oe3").style.visibility=h;
		}
	}
	if(d.getElementById("oe3").style.visibility==v){
		txvs=v;oevs=h;
		if(mode=="f"){
			f=true;
		}else{
			j=true;
		}
		if('ontouchstart' in window){
//			d.body.setAttribute('style','-webkit-touch-callout:default;-webkit-user-select:text;');
			d.body.style.setProperty('-webkit-touch-callout','default');
			d.body.style.setProperty('-webkit-user-select','text;');
		}
	}else{
		txvs=h;oevs=v;
		if(mode=="f"){
			fm=true;
		}else{
			jm=true;
		}
		if('ontouchstart' in window){
//			d.body.setAttribute('style','-webkit-touch-callout:none;-webkit-user-select:none;');
			d.body.style.setProperty('-webkit-touch-callout','none');
			d.body.style.setProperty('-webkit-user-select','none');
		}
	}
	d.getElementById("com").style.visibility=txvs;
	d.getElementById("oe3").style.visibility=oevs;
	if(t=d.getElementById("oebtnf")){t.style.display=f?b:n;}
	if(t=d.getElementById("oebtnfm")){t.style.display=fm?b:n;}
	if(t=d.getElementById("oebtnj")){t.style.display=j?b:n;}
	if(t=d.getElementById("oebtnjm")){t.style.display=jm?b:n;}
	if(t=d.getElementById("oebtnud")){t.style.display=jm?b:n;}
}
function isSupportsSvg() {//SVG support check for tegakiJs
	return document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1");
}
function canvasSvgAvailable(){//canvas support check for tegakiJs
	var canvas = document.createElement("canvas"),ctx;
	return !!(canvas.getContext && (ctx=canvas.getContext("2d"))
		&& "getImageData" in ctx && isSupportsSvg())
}
function setoebtn(){//set button tegakiJs
	var t="";
	if(FlashPlayerVer()){
		t+='<div id="oebtnf" onclick="ChangeDraw(\'f\')">手書き<\/div>';
		t+='<div id="oebtnfm" onclick="ChangeDraw(\'f\')" style="display:none;">文字入力<\/div>';
	}
	if(canvasSvgAvailable()){
		t+='<div id="oebtnj" onclick="ChangeDraw(\'j\')">手書きjs<\/div>';
		t+='<div id="oebtnjm" onclick="ChangeDraw(\'j\')" style="display:none;">文字入力<\/div>';
	}
	document.getElementById("oebtnd").innerHTML=t;
}
var dispdel=0;//flag for display deleted article
function ddrefl(){//display or hide for deleted article
  var c=0;
  var ddtags=document.getElementsByTagName("table");
  for(var i in ddtags){
    if(ddtags[i].className=="deleted"){
      ddtags[i].style.display=dispdel?(/*@cc_on!@*//*true?"table":"block"):"none";c++;
    }
  }
}
//Futaba tegakiJS removed
/********************* on load *********************/

function l(e){//set password from cookie
	var P=getCookie("pwdc"),N=getCookie("namec"),i;
	with(document){
		for(i=0;i<forms.length;i++){
			if(forms[i].pwd)with(forms[i]){
				if(!pwd.value)pwd.value=P;
			}
			if(forms[i].name)with(forms[i]){
				if(!name.value)name.value=N;
			}
		}
	}
}
/*function m(){//rewrite thread when reload
	var e1=document.getElementById("contres");
	if(e1){
		var thre=document.getElementsByClassName("thread");
		if(thre){
			var res=parseInt(thre[0].getAttribute("data-res"),10);
			var ss,sstmp;
			if(window.sessionStorage){
				sstmp=window.sessionStorage.getItem(b+"_"+res);
			}
			if(sstmp){
				ss=JSON.parse(sstmp);
			}else{
				ss={};
			}
			var lm=Date.parse(window.document.lastModified)/1000;
			if(ss["nowtime"]<lm){return;}
			if(!("res" in ss)){
				ss["res"]={};
			}
			contd=document.getElementById("contdisp");
			if(typeof(contdbk)=="undefined"){
				contdbk=contd.innerHTML;
			}
			contd.innerHTML="・・・";
			updateres(ss,res,false);
		}
	}
}*/
function getCookie(key,tmp1,tmp2,xx1,xx2,xx3){//get cookie
	tmp1=" "+document.cookie+";";
	xx1=xx2=0;
	len=tmp1.length;
	while(xx1<len){
		xx2=tmp1.indexOf(";",xx1);
		tmp2=tmp1.substring(xx1+1,xx2);
		xx3=tmp2.indexOf("=");
		if(tmp2.substring(0,xx3)==key){
			return(unescape(tmp2.substring(xx3+1,xx2-xx1-1)));
		}
		xx1=xx2+1;
	}
	return("");
}

/********************* initialize  *********************/
function Init(){//initialize when page loaded
	addScrollButton();
}
if(document.addEventListener){//add EventListener for initialize
	document.addEventListener('DOMContentLoaded',Init,false);
}else if(document.attachEvent){
	var CheckReadyState = function(){
		if(document.readyState=='complete'){
			document.detachEvent('onreadystatechange',CheckReadyState);
			Init();
		}
	};
	document.attachEvent('onreadystatechange',CheckReadyState);
	(function(){
		try{
			document.documentElement.doScroll( 'left' );
		}catch( e ){
			setTimeout( arguments.callee, 10 );
			return;
		}
		document.detachEvent('onreadystatechange', CheckReadyState);
		Init();
	})();
}
//window.onresize = reszk;//set up form position
//window.onload = reszk;//set up form position

if(document.addEventListener){
	//document.addEventListener((navigator.userAgent.match(/iP(hone|od|ad)/i)) ? 'touchstart' : 'click', thumbonclick, false);//add EventListener for video tag
	document.addEventListener('mouseover', qtpopup, false);//add EventListener for quote popup
	document.addEventListener('blur', closeqtpop, false);//add EventListener for close popup
	document.addEventListener('mouseup', selectpop, false);//add EventListener for select popup
}

/********************* ad *********************/
/*(function(){//display right side ad
	var Ad,adThread;
	if(typeof Ad==="undefined"||Ad===null){Ad={};}
	if(Ad.Thread==null){Ad.Thread={};}
	Ad.Thread=(
		function(){
			function Thread(){}
			Thread.prototype.onScroll=function (){
				var right_ads,right_ad_top,scrollTop,contresoffset,rightadfloatob,radtopob;
				scrollTop=document.documentElement.scrollTop||document.body.scrollTop;
				right_ads=document.getElementById("rightad");//下の起点
				if(!right_ads){return;}
				right_ad_top=right_ads.offsetTop;
				rightadfloatob=document.getElementById("rightadfloat");//バナー
				radtopob=document.getElementById("radtop");
				if(!rightadfloatob||!radtopob){return;}
				contresoffset=radtopob.offsetTop;//上の起点
				if(scrollTop<=contresoffset-20){
					rightadfloatob.className="rad radabsb";
					rightadfloatob.style.top=contresoffset+"px";
				}else{
					rightadfloatob.className=(scrollTop<=right_ad_top-640?"rad radfix":"rad radabs");
					rightadfloatob.style.top="";
				}
			};
			return Thread;
		}
	)();
	adThread=new Ad.Thread();
	if(window.addEventListener){
		window.addEventListener("scroll",adThread.onScroll,false);
		window.addEventListener("DOMContentLoaded",adThread.onScroll,false);
	}else if(window.attachEvent){
		window.attachEvent("onscroll",adThread.onScroll);
	}else{
		window.onscroll=adThread.onScroll;
	}
}).call(this);

var microadCompass = microadCompass || {};//ad by microad
microadCompass.queue = microadCompass.queue || [];//ad by microad
*/
