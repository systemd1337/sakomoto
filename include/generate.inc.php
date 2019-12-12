<?php
/*
 * Functions for generating the boards' frontend
 */

if(basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
        die("This file is not to be called directly.");

function postLink($matches){
	if($p=mysqli_fetch_array(mysqli_call("SELECT no,resto FROM ".POSTTABLE." WHERE `no`=".$matches[1])))
		return "<a class=\"quotelink\" href=\"".PHP_SELF."?res=".($p["resto"]?$p["resto"]:$p["no"])."#p".$p["no"]."\">&gt;&gt;".$p["no"]."</a>";
	else return $matches[0];
}

function blotter_contents($topN=0){
        $query="SELECT * FROM ".BLOTTERTABLE." ORDER BY `id` DESC";
        if($topN)$query.=" LIMIT ".$topN;
        if(!$result=mysqli_call($query))error("Critical SQL problem!");
        if(!mysqli_num_rows($result))return '';
        $blotter="<table width=\"468\" id=\"blotter\"><thead><tr><th colspan=\"2\"><hr/></th></tr></thead><tbody>";
        while($row=mysqli_fetch_assoc($result)){
                $blotter.="<tr class=\"blotter-msg\">";
                $blotter.="<td width=\"150\"><small>".humantime($row["time"])."</small></td>";
                $blotter.="<td><small>".$row["message"]."</small></td>";
                $blotter.="</tr>";
        }
        $blotter.="<tr><td colspan=\"2\" align=\"right\">";
        $shide=lang("Hide");$sshowb=lang("Show blotter");
        $blotter.= <<<EOF
<script>
/*<!--*/
function toggleBlotter(){
        toggle=document.getElementById("toggleBlotter");
        if(toggle.innerText=="{$shide}"){
                [].slice.call(document.getElementsByClassName("blotter-msg")).forEach(function(msg){msg.style.display="none";});
                toggle.innerText="{$sshowb}";
                document.cookie="hide_blotter=1;";
        }else{
                [].slice.call(document.getElementsByClassName("blotter-msg")).forEach(function(msg){msg.style.display='';});
                toggle.innerText="{$shide}";
                document.cookie="hide_blotter=0;";
        }
}

document.write('[<a href="javascript:void(0);" onclick="toggleBlotter();" id="toggleBlotter">{$shide}</a>]');
if(getCookie("hide_blotter")=="1")toggleBlotter();
/*-->*/
</script>&nbsp;
EOF;
        $blotter.="[<a href=\"".PHP_BLOTTER."\">".lang("Show all")."</a>]";
        $blotter.="</td></tr></tbody></table>";
        return $blotter;
}

function buildPost($post,$res=0){
	global $cache;
        if(!$res)global $res;
        
        if(is_file(CACHE_DIR.$post["no"].".inc.html")&&!($post["resto"]||$res))
                return file_get_contents(CACHE_DIR.$post["no"].".inc.html");
        
	$htm="<".($post["resto"]?"table":"div class=\"post op\"")." id=\"p".$post["no"]."\">";
	if($post["resto"])$htm.="<tr><td class=\"sideArrows\" valign=\"top\">&gt;&gt;</td><td class=\"post reply\">";
	$file='';
        $selfref=PHP_SELF."?res=".($post["resto"]?$post["resto"]:$post["no"])."#p".$post["no"];
        if($post["num_files"]>1)$file.="<table class=\"files\" cellpadding=\"5\"><tbody><tr>";
        $filekeys=["ext","fsize","filename","w","h","tn_w","tn_h"];
        $i2=0;
        for($i=$post["num_files"];$i;$i--){
                foreach($filekeys as $key){
                        if(isset(array_reverse(explode(',',$post[$key]))[$i-1]))
                                $$key=array_reverse(explode(',',$post[$key]))[$i-1];
                        else $$key=false;
                }
                $fileno=($i-1?"_".$i:'');
                $src = IMG_DIR.$post["tim"].$fileno.$ext;
                $hfsize=humanSize($fsize);
                $imgatts="longdesc=\"".$selfref."\" alt=\"".$hfsize."\" hspace=\"20\" vspace=\"3\" border=\"0\"";
                if($post["num_files"]==1)$imgatts.="align=\"left\"";
                if(file_exists($src)){
                        $f=explode('/',$src);
                        $file.="<".($post["num_files"]>1?"td width=\"150\" valign=\"top\" align=\"center\"":"div")." class=\"file\">".
                        "<div class=\"fileText\">".lang("File").": ";
                        $file.="<a class=\"fileDownload\" href=\"".$src."\" download=\"".$filename.$ext."\">&#x1F4BE;</a> ";
                        $file.="<a href=\"".$src."\"";
                        if(!stristr($filename,"<font")){
                                $trunclimit=($post["num_files"]>1?15:40);
                                $truncated=(strlen($filename)>$trunclimit?
                                        substr($filename,0,$trunclimit)."(...)":
                                        $filename).$ext;
                                if($post["num_files"]==1)$file.="onmouseover=\"this.innerText='".$filename.$ext."';\" ";
                                $file.="onmouseout=\"this.innerText='".$truncated."';\"";
                        }else{$truncated=$filename;}
                        $file.=">".$truncated."</a> ";
                        $file.="<span class=\"filesize\">(".$hfsize;
                        if($w*$h)$file.=", ".$w."x".$h;
                        $file.=")</span></div>";
                        $thumbsrc=THUMB_DIR.$post["tim"].$fileno.($post["num_files"]==1?"s":"c").".jpg";
                        $mime=mime_content_type($src);
                        switch(explode('/',$mime)[0]){
                                case "image":
                                        $file.="<a class=\"fileThumb\" href=\"".$src."\" target=\"_blank\">".
                                        "<img src=\"".$thumbsrc."\" class=\"postimg\" ".$imgatts;
                                        if($post["num_files"]==1)$file.=" width=\"".$tn_w."\" height=\"".$tn_h."\"";
                                        $file.="/></a>";
                                        break;
                                default:
                                        $tmp=explode('/',$mime);
                                        //Images from https://userstyles.org/styles/504/link-icons-identifiers-of-links
                                        switch(strtolower($tmp[0])){
                                                //Video
                                                case "video":
                                                        if($ext==".webm"&&file_exists($thumbsrc)){
                                                                $ftpreview=$thumbsrc."\" class=\"fileWebm";
                                                                break;
                                                        }
                                                        $ftpreview="data:image/gif;base64,R0lGODlhEAAQALMAAAQCBISChPzWjPz+/PwCBMTCxBIAAAAAAGgB398A2hYA0QAAd3CsAN/rABYSAAAAACH5BAEAAAIALAAAAAAQABAAAwRPUEgRqrUzh8H7KEEmbR5XDKFWckALqKsJpFQsV+BVcfjcFkAAMDAjFloCl3AI+AhfFKAU1DJCA4TgkTj4QZXgzhEWG09IZehoyjZLwHBABAA7";
                                                        break;
                                                case "audio":
                                                        $ftpreview="data:image/gif;base64,R0lGODlhEAAQALMAAAQCBLy+BISChMTCxIQChPz+/Pz+BAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAQALAAAAAAQABAAAwRZkEgiqrUzi8J7GUImbR43FKEWCIAHvEAWFABYDjERzCzKkTjdzFDzkT6A2cBA2xxNL4OA+TJWkIRXgHh6YilJorETnCQDABIwN4KVvqM3mU0Z2O94OmzPjwAAOw==";
                                                        break;
                                                case "text":
                                                        $gtpreview="data:image/gif;base64,R0lGODlhEAAQALMAAAQCBAT+BISChMTCxERCRPz+/AAAsQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEAAAEALAAAAAAQABAAAwRFMARQqK0yzyK474LWXSQmdUWqqgOQoevavgVh33Yxn3G8Bx0cTufi9VhFYE2Y+8GOziWTSDumolbqacDten8TgHhM1kQAADs=";
                                                        break;
                                        }
                                        if(!"file.png"){
                                                switch(strtolower(end($tmp))){
                                                        //Archive
                                                        case "x-freearc":
                                                        case "x-bzip":
                                                        case "x-bzip2":
                                                        case "gzip":
                                                        case "java-archive":
                                                        case "x-rar-compressed":
                                                        case "x-tar":
                                                        case "zip":
                                                        case "x-7z-compressed":
                                                                $ftpreview="data:image/gif;base64,R0lGODlhEAAQALMAAPQaBNSoD1+o7fvykYO+8rTY9/zVNgxPtPzzsK93DPveY4T+BCpuxvP24N+/MXmv4iH5BAEAAAsALAAAAAAQABAAAwRscMm5iCVPiBl6F00hFhYjBUAaMGzLaMcSOHRdJ8vzxMGA/IiBUIF7EHiDRiOoUBgCxaMsyXQ+o8jloGm45qS939YK/SI96LIulmi3XayMIEZZgAiCi8BUl+wZB4F0fXUahBR5eBpzhwQiFjARADs=";
                                                                break;
                                                        case "xml":
                                                        case "vnd.mozilla.xul+xml":
                                                                $ftpreview="data:image/gif;base64,R0lGODlhEAAQALMAAAQCBDSaBASChDTO%2FAQChMTCxDRmnPn5%2BTSa%2FAT%2BBAQC%2FDRm%2FDT%2B%2FISGhKTK9AQCnCH5BAEAAAkALAAAAAAQABAAAwRUsMlJZbqpnc17aZjWjcUBZuMIrAB6OJsgLBt8tJrzHMLBIITDA4bTxRiDQFDowL12C0RAERzeXDaCtrZxplJeg3g87rq%2BnTCZbBahOd63mUWvsxIRADs%3D";
                                                                break;
                                                }
                                        }
                                        if(!$ftpreview)$ftpreview="file.png";
                                        $file.="<a href=\"".$src."\" target=\"_blank\">".
                                        "<img src=\"".$ftpreview."\" ".$imgatts."/></a>";
                                        break;
                        }
                        $file.="</".($post["num_files"]>1?"td":"div").">";
                        if($i2==5){
                                $file.="</tr><tr>";
                                $i2=0;
                        }
                        $i2++;
                } else $file.="<img src=\"".($post["resto"]?"filedeleted-res.gif":"filedeleted.gif")."\" ".$imgatts."/>";
        }
        if($post["num_files"]>1)$file.="</tr></tbody></table>";
	$postinfo="<span class=\"postInfo\">";
	$postinfo.="<label><input class=\"del\" id=\"delcheck".$post["no"]."\" type=\"checkbox\" name=\"".$post["no"]."\" value=\"delete\"/>";
	if($post["sub"])$postinfo.="<font size=\"+1\"><b class=\"subject\">".$post["sub"]."</b></font> ";
        if($post["steam"]){
                $profid=array_filter(explode("/",$post["steam"]));
                $tip=false;
                if($badge=base64_encode(file_get_contents("https://badges.steamprofile.com/profile/default/steam/".end($profid).".png")))
                        $tip="onmouseover=\"Tip('<img src=\\'data:image/jpg;base64,".$badge."\\' alt=\\'\\'/>');\" onmouseout=\"UnTip();\" ";
                $postinfo.="<a href=\"".$post["steam"]."\" target=\"_blank\"><img ".$tip."src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAACXBIWXMAAA7DAAAOwwHHb6hkAAABHElEQVQ4jd2SMYrCUBCGvxcWooVC2ngBLQRPoJWIYJc+hxDEwkoEi7Se4HVibyPiGTyBjU16IRCI799icdmwFq5a7cBUM98/8w9jAAcYngsZQE/CAHivwG8R+Hi0sVarEccxxhistVwuF+DreA/dYLFY4HkekjDGMJvNyhuMRiM6nQ7n8xlrbQmuVCpUq1XG4zEASZL8ttDtdhkOh4RhSBAErNdr0jQFYDAY0O/3WS6XSMI5VxogQKvVSsfjUbcoikKHw0GbzUan00nOOV2vV83nc/m+rxv3LVCv1xVFkZrNpuI41na7VZ7nkqQsy7Tf7zWdTn+CZYF72Wg0ZK1VkiTa7XZqtVp/EwDUbrc1mUzU6/Xu1v/BK3u8aOETWfygttVpCfcAAAAASUVORK5CYII=\" alt=\"Steam\"/></a> ";
        }
        if($post["email"])$post["name"]="<a href=\"email:".$post["email"]."\">".$post["name"]."</a>";
        if($post["trip"]||$post["name"])$postinfo.="<span class=\"nameBlock\">";
	if($post["name"])$postinfo.="<b class=\"name\">".$post["name"]."</b>";
        if($post["trip"])$postinfo.="<span class=\"postertrip\">".$post["trip"]."</span>";
        if($post["capcode"])$postinfo.=" ".$post["capcode"];
        if($post["trip"]||$post["name"])$postinfo.="</span> ";
        if(DISP_ID){
                
                $r=(implode(unpack("CC",substr($post["id"],0,2)))*2);
                $g=(implode(unpack("CC",substr($post["id"],2,4)))*2);
                $b=(implode(unpack("CC",substr($post["id"],4,6)))*2);
                
                $postinfo.="<span class=\"posteruid\">(".lang("ID").":<font style=\"background-color:rgb(".$r.",".$g.",".$b.");\" ".
                        "color=\"".(($r+$b+$g>382?"black":"white"))."\">".
                        "<b>".$post["id"]."</b></font>)</span> ";
        }
        if(COUNTRY_FLAGS&&$post["country"]){
                $postinfo.="<img alt=\"".$post["country"]."\" title=\"".$post["country_name"]."\" src=\"".FLAGS_DIR.strtolower($post["country"]).".png\" class=\"flag\"/> ";
        }
        $postinfo.="<span class=\"dateTime\">".$post["now"]."</span>";
	$postinfo.="</label> ";
	$postinfo.="<span class=\"postNum\"><a href=\"".$selfref."\">".lang("No.")."</a>";
	$postinfo.="<a href=\"".($res?"":PHP_SELF."?res=".($post["resto"]?$post["resto"]:$post["no"])."&amp;q=".$post["no"])."#postform\"".
                " onclick=\"insert('".$post["no"]."');\" class=\"qu\" title=\"".lang("Quote")."\">".$post["no"]."</a></span>";
        if($post["sticky"])$postinfo.=" <img src=\"sticky.gif\" alt=\"".lang("Sticky")."\" title=\"".lang("Sticky")."\" class=\"retina\"/>";
        if($post["closed"])$postinfo.=" <img src=\"closed.gif\" alt=\"".lang("Closed")."\" title=\"".lang("Closed")."\" class=\"retina\"/>";
	if(!($post["resto"]||$res))$postinfo.="&nbsp;[<a href=\"".RES_DIR.$post["no"].PHP_EXT."\">".lang("Reply")."</a>]";
        $postinfo.="&nbsp;<small class=\"backlink\">";
        $results=mysqli_call("SELECT no,resto FROM ".POSTTABLE." WHERE com LIKE '%&gt;&gt;".$post["no"]."</a>%'");
        while($link=mysqli_fetch_assoc($results)){
                $postinfo.="<a class=\"quotelink\" href=\"".PHP_SELF."?res=".$link["resto"]."#p".$link["no"]."\">&gt;&gt;".$link["no"]."</a> ";
        }
        $postinfo.="</small>";
	$postinfo.="</span>";
        $htm.=($post["resto"]?$postinfo.$file:$file.$postinfo);
	$htm.="<blockquote>".$post["com"]."</blockquote>";
	if($post["resto"]) $htm.="</td></tr>";
	$htm.="</".($post["resto"]?"table":"div").">";
        
        if(!($post["resto"]||$res))
                file_put_contents(CACHE_DIR.$post["no"].".inc.html",$htm);
	return $htm;
}

function gentime(){
        global $tim;
        $gen_in=((time().substr(microtime(),2,3)-$tim)/1000);
        $tim=time().substr(microtime(),2,3);
        return "<span title=\"".lang("wow")."\" class=\"gentime\">".lang("Generated in ").
                $gen_in.lang(" seconds")."</span>";
}

function ctrlnav($mode,$top=false){
        $ctrl="<form action=\"".PHP_SELF."\" method=\"get\" class=\"ctrl\">";
        $ctrl.="<input type=\"hidden\" name=\"mode\" value=\"find\"/>";
        $ctrl.="<input type=\"text\" name=\"q\" value=\"\" size=\"8\"/><input type=\"submit\" value=\"".lang("Search")."\"/> ";
        if($mode!="page")$ctrl.="[<a href=\"".PHP_SELF2."\">".lang("Return")."</a>] ";
        if($mode!="catalog")$ctrl.="[<a href=\"".PHP_CAT."\">".lang("Catalog")."</a>] ";
        if($mode=="thread"){
                if($top)$ctrl.="[<a href=\"#bottom\">".lang("Bottom")."</a>] ";
                else $ctrl.="[<a href=\"#top\">".lang("Top")."</a>] ";
        }
        if($mode=="thread"&&$top)$ctrl.="<span id=\"repod_thread_stats_container\"></span>";
        $ctrl.="</form><hr/>";
        return $ctrl;
}

function updatelog($resno=0){
        global $mode,$tim;
        $threads=mysqli_call("SELECT * FROM ".POSTTABLE." WHERE ".($resno?"`no`=".$resno:"`resto`=0").
                " ORDER BY `sticky` DESC,`root` DESC");
        if(!$threads)error("Critical SQL problem!");
        
        $delsubmit=lang("Delete Post ")."[<label><input type=\"checkbox\" name=\"onlyimgdel\" value=\"on\"/>".lang("File Only")."</label>] ".
                "<label>".lang("Password: ")."<input type=\"password\" name=\"pwd\" size=\"8\"/></label> ".
                "<button type=\"submit\" name=\"mode\" value=\"usrdel\">".lang("Delete")."</button>";
        $pages=ceil(mysqli_num_rows($threads)/PAGE_DEF);
        if(!$pages)$pages++;
        for($page=$pages;$page;$page--){
                $dat='';
                head($dat,"<meta name=\"robots\" content=\"".($resno?"no":'')."index, follow\"/>");
//                if(!$resno)$dat.="<table width=\"100%\"><tbody><tr><td valign=\"top\">";
                form($dat,$resno);
/*                if(!$resno){
                        $stlist=lang("Thread list");
                        $tlist="";
                        $threads2=mysqli_call("SELECT * FROM ".POSTTABLE." WHERE ".($resno?"`no`=".$resno:"`resto`=0").
                                " ORDER BY `sticky` DESC,`root` DESC");
                        while($thread=mysqli_fetch_assoc($threads2)){
                                $tlist.="<tr>";
                                $tlist.="<td>".$thread["no"]."</td>";
                                $tlist.="<td>".$thread["name"]."</td>";
                                $tlist.="<td>".$thread["sub"]."</td>";
                                $tlist.="<td>".$thread["filename"].$thread["ext"]."</td>";
                                $tlist.="<td>".$thread["now"]."</td>";
                                $tlist.="</tr>";
                        }
                        $dat.= <<<EOF
                        </td>
                        <td valign="top">
                                <table class="postlists">
                                        <thead>
                                                <tr><th colspan="5"><center class="replymode"><b>{$stlist}</b></center></th></tr>
                                                <tr>
                                                        <th>No.</th>
                                                        <th>Name</th>
                                                        <th>Subject</th>
                                                        <th>File</th>
                                                        <th>Date</th>
                                                </tr>
                                        </thead>
                                        <tbody>
                                                {$tlist}
                                        </tbody>
                                </table>
                        </td>
                </tr>
        </tbody>
</table>
EOF;
                }*/
                if(stristr($mode,"rand"))$dat.="<center><h2>".lang("Random thread selected")."</h2></center><hr/>";
                $dat.=ctrlnav(($resno?"thread":"page"),true);
                $dat.="<form action=\"".PHP_SELF."\" method=\"post\" id=\"delform\">";
                $dat.="<div id=\"board\">";
                $t=PAGE_DEF;
                while(($t--)&&($thread=mysqli_fetch_assoc($threads))){
                        $dat.="<div class=\"thread\" id=\"t".$thread["no"]."\">";
                        $dat.=buildPost($thread,$resno);
                        $replies=mysqli_call("SELECT * FROM ".POSTTABLE." WHERE `resto`=".$thread["no"]." ORDER BY `no` ASC");
                        $omit=0;
                        $num_replies=mysqli_num_rows($replies);
                        if(!$resno&&($num_replies>COLLAPSENUM)){
                                $omit=$num_replies-COLLAPSENUM;
                                $dat.="<small class=\"omittedposts\">".$omit.lang(" post(s) omitted. ")."<a href=\"".RES_DIR.$thread["no"].PHP_EXT."\">".lang("Click here")."</a>".lang(" to view.")."</small>";
                        }
                        while($reply=mysqli_fetch_assoc($replies)){
                                if(!$omit)$dat.=buildPost($reply,$resno);
                                else $omit--;
                        }
                        $dat.="<br clear=\"all\"/></div><hr/>";
                }
                $sreason=lang("Reason: ");
                $sreport=lang("Report");
                $dat.= <<<EOF
        </div>
        <div align="right">
                <table align="right" id="delSub">
                        <tbody>
                                <tr><td align="right">{$delsubmit}</td></tr>
                                <tr><td align="right">
                                        <label>{$sreason}
                                        <input type="text" name="reason"/></label>
                                        <button type="submit" name="mode" value="report">{$sreport}</button>
                                </td></tr>
                        </tbody>
                </table>
        </div>
</form>
EOF;
                if($resno){
                        $dat.=ctrlnav("thread");
                        foot($dat);
                        return $dat;
                }
                $realpage=$pages-$page+1;
                $dat.="<table border=\"1\" id=\"pager\"><tbody><tr>";
                $pager=$pages;
                $pagel=[];
                while($pager--){
                        $pagel[]="[<".($pager==($realpage-1)?"b":"a href=\"".($pager+1).PHP_EXT."\"").">".
                                ($pager+1)."</".($pager==($realpage-1)?"b":"a").">]";
                }
                if($realpage-1){
                        $sprev=lang("Previous");
                        $prevl=($realpage-1).PHP_EXT;
                        $dat.= <<<EOF
<td>
        <form action="{$prevl}" onsubmit="window.location=this.action;return false;" method="get">
                <input type="submit" value="{$sprev}"/>
        </form>
</td>
EOF;
                }
                $dat.="<td id=\"pages\">";
                $dat.=implode("&nbsp;",array_reverse($pagel));
                if($realpage!=$pages){
                        $snext=lang("Next");
                        $nextl=($realpage+1).PHP_EXT;
                        $dat.= <<<EOF
</td>
<td>
        <form action="{$nextl}" onsubmit="window.location=this.action;return false;" method="get">
                <input type="submit" value="{$snext}"/>
        </form>
EOF;
                }
                $dat.="</td></tr></tbody></table>";
                foot($dat);
                file_put_contents($realpage.PHP_EXT,$dat);
                copy("1".PHP_EXT, PHP_SELF2);
        }
}

function head(&$dat,$extra='') {
        global $mode;
        if(is_file(CACHE_DIR."head.inc.html")&&$mode!="admin"&&!$extra){
                $dat.=file_get_contents(CACHE_DIR."head.inc.html");
                return;
        }
	$lang=LANGUAGE;
	$keywords=KEYWORDS;
	$description=DESCRIPTION;
        $head='';
	$head.= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="{$lang}" xmlns="http://www.w3.org/1999/xhtml">
	<head>
                <meta charset="UTF-8"/>
                <meta http-equiv="Content-Script-Type" content="text/javascript"/>
                <meta http-equiv="Content-Style-Type" content="text/css"/>
                <meta http-equiv="content-type"  content="text/html;charset=utf-8"/>
                <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
                <meta name="DC.Format" content="text/html"/>
                <meta name="DC.Type" content="imageboard">
                <meta name="DC.Coverage" content="Worldwide">
                <meta name="application-name" content="{$title}"/>
                <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate"/>
                <meta http-equiv="pragma" content="no-cache"/>
                <meta http-equiv="expires" content="0"/>
                <meta name="robots" content="noarchive"/>
                <meta http-equiv="content-language" content="{$lang}"/>
                <meta name="language" content="{$lang}"/>
                <meta property="og:locale" content="{$lang}"/>
                <meta name="DC.Language" content="en"/>
                <meta name="keywords" content="{$keywords}"/>
                <meta name="description" content="{$description}"/>
                <meta name="DC.Description" content="Description"/>
                <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes"/>
                <meta name="revisit-after" content="15 days"/>
                <meta name="referrer" content="origin"/>
                <meta name="theme-color" content="#FFD7B0"/>
                <meta name="msapplication-TileColor" content="#FFD7B0"/>
                <meta name="msapplication-navbutton-color" content="#FFD7B0"/>
                <meta name="generator" content="sakomoto"/>
                <meta property="og:type" content="website"/>
                <meta name="msapplication-window" content="width=1024;height=768"/>
                <meta name="pinterest" content="nopin"/>
                <style type="text/css">
blink,.blink{
        -webkit-animation:blink 1.5s step-end infinite;
        -moz-animation:blink 1.5s step-end infinite;
        -o-animation:blink 1.5s step-end infinite;
        animation:blink 1.5s step-end infinite
}
@keyframes blink{50%{opacity:0}}
@-webkit-keyframes blink{50%{opacity:0}}

@keyframes rainbow{
        0%{color:rgb(255,0,0);}
        16%{color:rgb(127,127,0);}
        33%{color:rgb(0,255,0);}
        49%{color:rgb(0,127,127);}
        66%{color:rgb(0,0,255);}
        82%{color:rgb(127,0,127);}
        100%{color:rgb(255,0,0);}
}
#top{position:absolute;top:0;}
                </style>
                <script type="text/javascript">var 
EOF;
        foreach(JSVARS as $var => $val){$head.=$var."='".$val."',";}
        $head.="bbcodes=".json_encode(BBCODES,JSON_HEX_QUOT).",";
        $head.="emotes=".str_replace("\\/","/",json_encode(EMOTES,JSON_HEX_QUOT)).",";
        $head.="emotes_dir='".EMOTES_DIR."',";
        $head.="phpself='".PHP_SELF."',";
        $head.="phpapi='".PHP_API."',";
        $head.="phpext='".PHP_EXT."',";
        $head.="phpplayer='".PHP_PLAYER."',";
        $head.="js_dir='".JS_DIR."',";
        $head.="cssdef='".CSSDEFAULT."';";
        $head.="</script>";
        $head.="<script charset=\"UTF-8\" src=\"".JS_DIR."jquery/jquery.min.js\" type=\"text/javascript\"></script>";
        $head.="<script charset=\"UTF-8\" src=\"".JS_DIR."futaba.js\" type=\"text/javascript\"></script>";
        $head.="<script charset=\"UTF-8\" src=\"".JS_DIR."sakomoto.js\" type=\"text/javascript\"></script>";
        foreach(JSPLUGINS as $js){
                $head.="<script charset=\"UTF-8\" src=\"".JS_DIR.$js."\" type=\"text/javascript\"></script>";
        }
	foreach(STYLES as $stylename => $stylefile) {
		$head.="<link charset=\"UTF-8\" rel=\"".($stylename==CSSDEFAULT?'':"alternate ")."stylesheet\" type=\"text/css\" ".
                        "href=\"".CSS_DIR."styles/".$stylefile."\" title=\"".$stylename."\"/>";
	}
        $head.="<link charset=\"UTF-8\" rel=\"stylesheet\" type=\"text/css\" href=\"".CSS_DIR."mobile.css\"/>";
        if(USE_RSS)$head.="<link charset=\"UTF-8\" rel=\"alternate\" type=\"application/rss+xml\" href=\"".RSS."\"/>";
        $head.="<meta name=\"distribution\" content=\"".($mode=="admin"?"iu":"global")."\"/>";
        $head.="<link rel=\"preload\" as=\"image\" href=\"".TITLEIMG."\"/>";
        $head.="<link rel=\"preload\" as=\"image\" href=\"".CAPTCHA_IMG."\"/>";
        $head.="<title>".TITLE."</title>";
        $head.="<meta property=\"DC.title\" content=\"".TITLE."\"/>";
        $head.="<meta property=\"og:title\" content=\"".TITLE."\"/>";
        $head.="<meta name=\"twitter:title\" content=\"".TITLE."\"/>";
        $head.="<meta property=\"og:description\" content=\"".substr(DESCRIPTION,0,255)."\"/>";
        $head.="<meta name=\"twitter:description\" content=\"".substr(DESCRIPTION,0,200)."\"/>";
        if(ICON){
                $head.="<link rel=\"apple-touch-icon\" href=\"".ICON."\"/>";
                $head.="<link rel=\"shortcut icon\" href=\"".ICON."\"/>";
                $head.="<link rel=\"icon\" href=\"".ICON."\"/>";
                $head.="<meta itemprop=\"image\" content=\"".ICON."\">";
        }
        $head.="<meta property=\"og:url\" content=\"".HERE."\"/>";
        if(HEAD_EXTRA)$head.=HEAD_EXTRA;
        $head.=$extra."</head><body><div id=\"top\"></div>";
        $head.="<script type=\"text/javascript\" src=\"".JS_DIR."wz_tooltip/wz_tooltip.js\"></script>";
        $head.="<div class=\"boardNav\">";
        if(BOARDLINKS)$head.="<span class=\"boardlist\">".BOARDLINKS."</span>";
        $head.="<div align=\"right\" class=\"navtopright\">";
        if(HOME)$head.="[<a href=\"".HOME."\" target=\"_top\">".lang("Home")."</a>] ";
        $head.="[<a href=\"".PHP_SELF."?mode=admin\">".lang("Manage")."</a>]";
        $head.="</div></div>";
        if(SHOWTITLEIMG||SHOWTITLETXT){
                $head.="<center class=\"logo\">";
                if(SHOWTITLEIMG)$head.="<img src=\"".TITLEIMG."\" onload=\"this.style.opacity=1;\" onclick=\"this.style.opacity=0.5;this.src='".TITLEIMG."?'+(new Date().getTime());\" border=\"1\" alt=\"".TITLE."\"/>";
                if(SHOWTITLETXT)$head.="<h1>".TITLE.(USE_RSS?" <a href=\"".RSS."\"><img border=\"0\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA4AAAAOCAYAAAAfSC3RAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAJDSURBVHjajJJNSBRhGMd/887MzrQxRSLbFuYhoUhEKsMo8paHUKFLdBDrUIdunvq4RdClOq8Hb0FBSAVCUhFR1CGD/MrIJYqs1kLUXd382N356plZFOrUO/MMz/vO83+e93n+f+1zF+kQBoOQNLBJg0CTj7z/rvWjGbEOIwKp9O7WkhtQc/wMWrlIkP8Kc1lMS8eyFHpkpo5SgWCCVO7Z5JARhuz1Qg29fh87u6/9VWL1/SPc4Qy6n8c0FehiXin6dcCQaylDMhqGz8ydS2hKkmxNkWxowWnuBLHK6G2C8X6UJkBlxUmNqLYyNbzF74QLDrgFgh9LLE0NsPKxjW1Hz2EdPIubsOFdH2HgbwAlC4S19dT13o+3pS+vcSfvUcq9YnbwA6muW9hNpym/FWBxfh0CZkKGkPBZeJFhcWQAu6EN52QGZ/8prEKW+cdXq0039UiLXhUYzdjebOJQQI30UXp6mZn+Dtam32Afu0iyrgUvN0r+ZQbr8HncSpUVJfwRhBWC0hyGV8CxXBL5SWYf9sYBidYLIG2V87/ifVjTWAX6AlxeK2C0X8e58hOr/Qa2XJ3iLMWxB1h72tHs7bgryzHAN2o2gJorTrLxRHVazd0o4TXiyV2Yjs90uzauGvvppmqcLjwmbZ3V7BO2HOrBnbgrQRqWUgTZ5+Snx4WeKfzCCrmb3axODKNH+vvUyWjqyK4DiKQ0eXSpFsgVvLJQWpH+xSpr4otg/HI0TR/t97cxTUS+QxIMRTLi/9ZYJPI/AgwAoc3W7ZrqR2IAAAAASUVORK5CYII=\" alt=\"RSS\"/></a>":"").
                        "</h1>";
                $head.="</center><hr class=\"logohr\"/>";
        }
        if($mode!="admin"&&!$extra)file_put_contents(CACHE_DIR."head.inc.html",$head);
        $dat.=$head;
}
/* Contribution form */
function form(&$dat,$resno=0,$admin="",$manapost=false,$paintcom=false) {
	global $q;
        if(is_file(CACHE_DIR."form.inc.html")&&!($resno||$admin||$manapost||$paintcom||$q)){
                $dat.=file_get_contents(CACHE_DIR."form.inc.html");
                return;
        }
        
        $form='';
        if($resno&&mysqli_fetch_assoc(mysqli_call("SELECT closed FROM ".POSTTABLE." WHERE `no`=".$resno))["closed"]){
                $form.="<center><h2 id=\"errormsg\">".lang("Thread closed.")."<br>".lang("You may not reply at this time.")."</h2></center><hr>";
                return;
        }

	$maxbyte = MAX_KB * 1024;
        
        $form.="<center class=\"postarea\">";
        if($admin)$form.="<i>".lang("HTML tags are allowed.")."</i>";
        if($resno&&!$manapost)$form.="<div class=\"replymode\"><big>".lang("Posting mode: Reply")."</big></div>";
        
        $inputs='';
        //Name
        if(!FORCED_ANON||$admin){
                $inputs.="<tr class=\"unimportant\"><td class=\"postblock\"><label for=\"name\"><b>".lang("Name")."</b></label></td>";
                $inputs.= "<td><input type=\"text\" name=\"name\" id=\"name\"";
                if($manapost)$inputs.=" value=\"".($_SESSION["name"]?$_SESSION["name"]:DEFAULT_NAME)."\"";
                else $inputs.=" value=\"".DEFAULT_NAME."\"";
                $inputs.=" size=\"28\" tabindex=\"1\"/></td></tr>";
        }
        if($admin){
                //Capcode
                if($_SESSION["cancap"]){
                        $inputs.="<tr><td class=\"postblock\"><label for=\"capcode\"><b>".lang("Capcode")."</b></label></td>";
                        $inputs.="<td><label><input type=\"checkbox\" name=\"capcode\" id=\"capcode\" value=\"on\" checked tabindex=\"2\"/>".
                        " (".$_SESSION["capcode"].")</label></td></tr>";
                }
                //Admin resto
                $inputs.="<tr><td class=\"postblock\"><label for=\"resto\"><b>".lang("Reply to")."</b></label></td>";
                $inputs.="<td><input type=\"number\" name=\"resto\" id=\"resto\" value=\"0\" tabindex=\"3\"/></td></tr>";
        }
        //Steam
        if(STEAM){
                $inputs.="<tr><td class=\"postblock\"><label for=\"steam\"><b>".lang("Steam")."</b></label></td>";
                $inputs.="<td><input type=\"text\" name=\"steam\" id=\"steam\" value=\"\" size=\"48\" tabindex=\"4\"/></td></tr>";
        }
        //E-mail
        $inputs.="<tr class=\"unimportant\"><td class=\"postblock\"><label for=\"email\"><b>".lang("E-mail")."</b></label></td>";
        $inputs.="<td><input type=\"text\" name=\"email\" id=\"email\" value=\"\" size=\"28\" tabindex=\"5\"/></td></tr>";
        //Subject
        $inputs.="<tr><td class=\"postblock\"><label for=\"sub\"><b>".lang("Subject")."</b></label></td>";
        $inputs.="<td><input type=\"text\" name=\"sub\" id=\"sub\" size=\"28\" tabindex=\"6\"/>".
                "<button type=\"submit\" name=\"post\" value=\"post\" tabindex=\"12\" id=\"postsubmit\">".lang(($resno?"New Reply":"New Topic"))."</button></td></tr>";
        //Comment
        $inputs.="<tr><td class=\"postblock\"><label for=\"com\"><b>".lang("Comment")."</b></label></td>";
        $inputs.="<td><textarea name=\"com\" id=\"com\" cols=\"48\" style=\"width:300px\" rows=\"6\" tabindex=\"7\"".
                ($resno&&$q?" autofocus>&gt;&gt".$q."\n":">")."</textarea></td></tr>";
        //Verification
        switch(CAPTCHA_DRIVER){
                case "saguaro":
                        $inputs.="<tr><td class=\"postblock\"><label for=\"verif\"><b>".lang("Verification")."</b></label></td>";
                        $inputs.="<td><div><img id=\"verifimg\" src=\"".CAPTCHA_IMG."\" alt=\"Captcha\" onclick=\"this.src=this.src+'?'+Date.now();this.style.opacity=0.5;\" onload=\"this.style.opacity=1;\"/>";
                        $inputs.="<script type=\"text/javascript\"  async=\"async\">/*<!--*/document.write(\"&nbsp;".lang("(Click for new captcha)")."\");/*-->*/</script></div>";
                        $inputs.="<input type=\"text\" id=\"verif\" tabindex=\"8\" name=\"verif\" value=\"\"/></td></tr>";
                        break;
                case "":
                default:
                        break;
        }
        //File(s) and oekaki
        if(MAX_FILES&&!$paintcom){
                $inputs.="<tr id=\"filerow\"><td class=\"postblock\"><label".(MAX_FILES>1?'':" for=\"upfile\"")."><b>".lang("File")."</b></label></td><td>";
                $files=MAX_FILES;
                while($files--){
                        $inputs.="<div id=\"upload\" ".($files?"class=\"unimportant\"":"style=\"display:table-row;\"")."><input type=\"file\" name=\"upfile".$files."\" ".(MAX_FILES>1?"class":"id")."=\"upfile\" tabindex=\"9\"/>";
                        $inputs.= <<<EOF
        <script type="text/javascript" async="async">
/*<!--*/
document.write('<button type="button" onclick="document.querySelector(\'input[name=upfile{$files}]\').value=\'\';">X</button>');
/*-->*/
        </script>
</div>
EOF;
                }
                //Oekaki
                if(OEKAKI_DRIVER){
                        $needjsdraw="<noscript>".lang("You need JavaScript to use the painter.")."</noscript>";
                        switch(OEKAKI_DRIVER){
                                case "neo":
                                        $self=PHP_SELF;
                                        $sdw=lang("Width: ");
                                        $sdh=lang("Height: ");
                                        $sdraw=lang("Draw");
                                        $dat.= <<<EOF
<form action="{$self}">
        <input type="hidden" name="mode" value="paint"/>
        <label>{$sdw}<input type="text" name="paintsizew" maxlength="4" size="6" class="dim" value="400"/></label>
        &times;
        <label>{$sdh}<input type="text" name="paintsizeh" maxlength="4" size="6" class="dim" value="400"/></label>
        <input type="submit" value="{$sdraw}"/>
</form>
EOF;
                                        break;
                                case "tegaki";
                                        $ssize=lang("Size");$sdraw=lang("Draw");
                                        $sclear=lang("Clear");
                                        $inputs.="<tr id=\"painter\"><td class=\"postblock\"><label for=\"pwd\"><b>".$sdraw."</b></label></td><td>";
                                        $inputs.=$needjsdraw. <<<EOF
<link rel="stylesheet" href="js/tegaki/tegaki.css"/>
<script type="text/javascript" src="js/tegaki/tegaki.js"></script>
<script type="text/javascript"  async="async">
/*<!--*/
document.write(`{$ssize}
 <input type="number" class="dim" value="400" size="6" maxlength="4" id="tWidth"> &times;
 <input type="number" class="dim" value="400" size="6" maxlength="4" id="tHeight">
 <button type="button" onclick="tegakiOpen();" id="tDraw">{$sdraw}</button>
 <button type="button" onclick="tegakiClear();" disabled id="tClear">{$sclear}</button>
<style>
.dim{
        text-align:center;
        width:50px;
}

@font-face{font-family:tegaki;src:url(data:application/octet-stream;base64,d09GRgABAAAAAAyIAA4AAAAAFVAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAABPUy8yAAABRAAAAEQAAABWPeFIsGNtYXAAAAGIAAAAOgAAAUrQFxm3Y3Z0IAAAAcQAAAAKAAAACgAAAABmcGdtAAAB0AAABZQAAAtwiJCQWWdhc3AAAAdkAAAACAAAAAgAAAAQZ2x5ZgAAB2wAAAI+AAAC7u/G5z9oZWFkAAAJrAAAADYAAAA2BIBHAWhoZWEAAAnkAAAAHgAAACQHlwNRaG10eAAACgQAAAAWAAAAIBsOAABsb2NhAAAKHAAAABIAAAASA2cCrm1heHAAAAowAAAAIAAAACAAmwu2bmFtZQAAClAAAAF+AAACte3MYkJwb3N0AAAL0AAAAFAAAABnZ1gGo3ByZXAAAAwgAAAAZQAAAHvdawOFeJxjYGROYpzAwMrAwVTFtIeBgaEHQjM+YDBkZGJgYGJgZWbACgLSXFMYHF4wvGBjDvqfxRDFzM3gDxRmBMkBANw6Cw94nGNgYGBmgGAZBkYGEHAB8hjBfBYGDSDNBqQZGZgYGF6w/f8PUvCCAURLMELVAwEjG8OIBwBqdQa0AAAAAAAAAAAAAAAAAAB4nK1WaXMTRxCd1WHLNj6CDxI2gVnGcox2VpjLCBDG7EoW4BzylexCjl1Ldu6LT/wG/ZpekVSRb/y0vB4d2GAnVVQoSv2m9+1M9+ueXpPQksReWI+k3HwpprY2aWTnSUg3bFqO4kPZ2QspU0z+LoiCaLXUvu04JCISgap1hSWC2PfI0iTjQ48yWrYlvWpSbulJd9kaD+qt+vbT0FGO3QklNZuhQ+uRLanCqBJFMu2RkjYtw9VfSVrh5yvMfNUMJYLoJJLGm2EMj+Rn44xWGa3GdhxFkU2WG0WKRDM8iCKPslpin1wxQUD5oBlSXvk0onyEH5EVe5TTCnHJdprf9yU/6R3OvyTieouyJQf+QHZkB3unK/ki0toK46adbEehivB0fSfEI5uT6p/sUV7TaOB2RaYnzQiWyleQWPkJZfYPyWrhfMqXPBrVkoOcCFovc2Jf8g60HkdMiWsmyILujk6IoO6XnKHYY/q4+OO9XSwXIQTIOJb1jkq4EEYpYbOaJG0EOYiSskWV1HpHTJzyOi3iLWG/Tu3oS2e0Sag7MZ6th46tnKjkeDSp00ymTu2k5tGUBlFKOhM85tcBlB/RJK+2sZrEyqNpbDNjJJFQoIVzaSqIZSeWNAXRPJrRm7thmmvXokWaPFDPPXpPb26Fmzs9p+3AP2v8Z3UqpoO9MJ2eDshKfJp2uUnRun56hn8m8UPWAiqRLTbDlMVDtn4H5eVjS47CawNs957zK+h99kTIpIH4G/AeL9UpBUyFmFVQC9201rUsy9RqVotUZOq7IU0rX9ZpAk05Dn1jX8Y4/q+ZGUtMCd/vxOnZEZeeufYlyDSH3GZdj+Z1arFdgM5sz+k0y/Z9nebYfqDTPNvzOh1ha+t0lO2HOi2w/UinY2wvaEGT7jsEchGBXMAGEoGwdRAI20sIhK1CIGwXEQjbIgJhu4RA2H6MQNguIxC2l7Wsmn4qaRw7E8sARYgDoznuyGVuKldTyaUSrotGpzbkKXKrpKJ4Vv0rA/3ikTesgbVAukTW/IpJrnxUleOPrmh508S5Ao5Vf3tzXJ8TD2W/WPhT8L/amqqkV6x5ZHIVeSPQk+NE1yYVj67p8rmqR9f/i4oOa4F+A6UQC0VZlg2+mZDwUafTUA1c5RAzGzMP1/W6Zc3P4fybGCEL6H78NxQaC9yDTllJWe1gr9XXj2W5twflsCdYkmK+zOtb4YuMzEr7RWYpez7yecAVMCqVYasNXK3gzXsS85DpTfJMELcVZYOkjceZILGBYx4wb76TICRMXbWB2imcsIG8YMwp2O+EQ1RvlOVwe6F9Ho2Uf2tX7MgZFU0Q+G32Rtjrs1DyW6yBhCe/1NdAVSFNxbipgEsj5YZq8GFcrdtGMk6gr6jYDcuyig8fR9x3So5lIPlIEatHRz+tvUKd1Ln9yihu3zv9CIJBaWL+9r6Z4qCUd7WSZVZtA1O3GpVT15rDxasO3c2j7nvH2Sdy1jTddE/c9L6mVbeDg7lZEO3bHJSlTC6o68MOG6jLzaXQ6mVckt52DzAsMKDfoRUb/1f3cfg8V6oKo+NIvZ2oH6PPYgzyDzh/R/UF6OcxTLmGlOd7lxOfbtzD2TJdxV2sn+LfwKy15mbpGnBD0w2Yh6xaHbrKDXynBjo90tyO9BDwse4K8QBgE8Bi8InuWsbzKYDxfMYcH+Bz5jBoMofBFnMYbDNnDWCHOQx2mcNgjzkMvmDOOsCXzGEQModBxBwGT5gTADxlDoOvmMPga+Yw+IY59wG+ZQ6DmDkMEuYw2Nd0ayhzixd0F6htUBXowPQTFvewONRUGbK/44Vhf28Qs38wiKk/aro9pP7EC0P92SCm/mIQU3/VdGdI/Y0Xhvq7QUz9wyCmPtMvxnKZwV9GvkuFA8ouNp/z98T7B8IaQLYAAQAB//8AD3icZZI/bNNAFMbvnYOd3KW1kzhnqUQmdVo7FQWi/LGlMKDSUglRZesAylSKVCkMiB2UShUqE1LGSERCSlmYIFIr5q4MDFUpTN1IB8Rahjq8c9oy4OHzu3dPv+/u3iNAyOg3PaCvyAxRByIGN67Pmjqozi3QpLjVO+BJ8cvXIJAicNsS9EBfMeaNfh9lxZB/499a1/t9/ZmQwc6O/n+hflMWEOn9R0krnBTQeyqB3pA1Va+AohUcN6iheLWqH1RQbkNZWNlKWSjpvBjmRUvkYWjZgAvbamEwxMSezJ4IzGZPLrOynOAHUpQ0/CI6+iWVC7/pc5fpMfvsUUSl7y94Y1CeKNF5h/QFSRGHVAjbK3lXTZ0qyHE9gSjHrVUDVcNiH6qu5qhZ0wYf2ZWyf8XU1Fh+Bh8z8OchZgnl3Wrb6XztOO3VB8cQOw4/G3x53RDGUokb8J03wtPwR3ja4LwBcXAh3uBQ31qoL250OhuL9YWt59vbcB9L1+8lJ2malZaML5nMZre7mXHNdpf2XprRnUc/lV06R0y8M6N45wR214NxT60EjHuqfAjXmM3CNc6b3GZQhCLPJZsc3oSPOYe3mGtyHh5hGgty52+5S5cjri65szgwXgLGUxNIeMSVuPAoPIpwHHo8J6XVZAzmwm+MRXY9Jq1zeN7R2egjvUv3yRRyOUFuBvtipbDx47F0AxyFVEFGfhpeawxaOJKfuMMGkwlmtQZx9aHG6D6Lh3YxczgxcZgSJjxRn2riL3t/mWkAAAABAAAAAQAAO8vwqV8PPPUACwPoAAAAANC+FsgAAAAA0L3smP/9/7ED6AMLAAAACAACAAAAAAAAeJxjYGRgYA76n8UQxfyCgeH/NyAJFEEBHACQkgXuAAB4nGN+wcDALIiEXyAwkzUDAwBBEgQmAAAAAAAAAD4AdgCWAPABHAFIAXcAAAABAAAACAA0AAMAAAAAAAIAAAAQAHMAAAAcC3AAAAAAeJx1kM1Kw0AUhc/U/mArLiy4HjeiiOkPurBuxELrSsFFQVzI2E6T1DRTJlOhr+A7+BC+kM/iSTJIEcwwk++ee+7NnQA4wDcEyueSu2SBOqOSK2jg2vMO9VvPVfKd5xpauPdc53ry3MQZXjy30MYHO4jqLqMFPj0L7ImG5wr2RdvzDvUjz1XyuecaDsWV5zr1B89NTMSz5xaOxdfQrDY2DiMnT4anst/tXcjXjTSU4lQlUq1dZGwmb+TcpE4niQmmZul0qN7iRx2uE2XLoDwn2maxSWUv6JbCWKfaKqdnedfsPew7N5dza5Zy5PvJlTULPXVB5Nxq0OlsfwdDGKywgUWMEBEcJE6onvLdRxc9XJBe6ZB0lq4YKRQSKgprVkRFJmN8wz1nlFLVdCTkAFOey0IJWfHG+seC18wrVm5ntnlCzvvGRUfJWQJOtO0Yk9PCpQp99jtrhne6+lQdJ8qnssUUEqM/80neP88tqEypB8VfcFQH6HD9c58fnU58DwAAeJxjYGKAAC4G7ICDgYGRiZGZkYWRlZGNkZ2Rgy05MS85NYelIKe0mDU3M6+0mDm1MpUzJb88Tze/IDWPvbQATHPlpJal5uiCxBkYAP+wElx4nGPw3sFwIihiIyNjX+QGxp0cDBwMyQUbGVidNjIwaEFoDhR6JwMDAycyi5nBZaMKY0dgxAaHjoiNzCkuG9VAvF0cDQyMLA4dySERICWRQLCRgUdrB+P/1g0svRuZGFwAB9MiuAAAAA==) format('woff');font-weight:400;font-style:normal}.tegaki-icon:before{font-size:10px;width:10px;font-family:tegaki;font-style:normal;font-weight:400;speak:none;display:inline-block;text-align:center;font-variant:normal;text-transform:none;line-height:1em}.tegaki-cancel:before{content:'\e800'}.tegaki-plus:before{content:'\e801'}.tegaki-minus:before{content:'\e802'}.tegaki-eye:before{content:'\e803'}.tegaki-down-open:before{content:'\e804'}.tegaki-up-open:before{content:'\e805'}.tegaki-level-down:before{content:'\e806'}#tegaki{position:fixed;width:100%;height:100%;top:0;left:0;background-color:#a3b1bf;color:#000;font-family:arial,sans-serif;-moz-user-select:none;-webkit-user-select:none;-ms-user-select:none;overflow:auto;z-index:9999}#tegaki canvas{image-rendering:optimizeSpeed;image-rendering:-moz-crisp-edges;image-rendering:-webkit-optimize-contrast;image-rendering:pixelated;-ms-interpolation-mode:nearest-neighbor}#tegaki-debug{position:absolute;left:0;top:0}#tegaki-debug canvas{width:75px;height:75px;display:block;border:1px solid #000}.tegaki-backdrop{overflow:hidden}.tegaki-hidden{display:none!important}.tegaki-strike{text-decoration:line-through}#tegaki-cnt{left:50%;top:50%;position:absolute}#tegaki-cnt.tegaki-overflow-x{left:10px;margin-left:0!important}#tegaki-cnt.tegaki-overflow-y{top:10px;margin-top:0!important}.tegaki-tb-btn{margin-left:10px;cursor:pointer;text-decoration:none}.tegaki-tb-btn:hover{color:#007fff}.tegaki-tb-btn:focus{color:#007fff;outline:none}#tegaki-menu-bar{font-size:12px;white-space:nowrap;position:absolute;right:0}#tegaki-canvas{-moz-box-shadow:0 0 0 1px rgba(0,0,0,.2);box-shadow:0 0 0 1px rgba(0,0,0,.2);background:snow}#tegaki-layers{display:inline-block;position:relative;overflow:auto;font-size:0}#tegaki-layers canvas{position:absolute;left:0;top:0}#tegaki-finish-btn{font-weight:700}.tegaki-ctrlgrp{margin-bottom:5px}.tegaki-label{font-size:10px}.tegaki-label:after{content:' ' attr(data-value)}#tegaki-ctrl{position:absolute;display:inline-block;width:80px;padding-left:5px;font-size:14px}#tegaki-color{padding:0;border:0;display:block;width:25px;height:25px;cursor:pointer}#tegaki-layer-grp span{font-size:12px;margin-right:3px;cursor:pointer}#tegaki-layer-grp span:hover{color:#007fff}#tegaki-color::-moz-focus-inner{border:none;padding:0}#tegaki-ctrl input[type=range]{width:90%;margin:auto}#tegaki-ctrl select{font-size:11px;width:100%}
</style>
`);
/*-->*/
</script></td></tr>
EOF;
                                        break;
                                default:
                                        break;
                        }
                }
        }
        //Toggle unimportant
        $inputs.= <<<EOF
<script type="text/javascript" async="async">
/*<!--*/
document.write('<tr><td colspan="2"><button type="button" onclick="toggleHidden();">Toggle hidden feilds</button></td></tr>');
/*-->*/
</script>
EOF;
        //Password
        $inputs.="<tr class=\"unimportant\"><td class=\"postblock\"><label for=\"pwd\"><b>".lang("Password")."</b></label></td>";
        $passtxt=lang("(Password used for file deletion)");
        $inputs.= <<<EOF
        <td>
                <input type="password" name="pwd" id="pwd" size="8" tabindex="11"/> 
                <small>{$passtxt}</small>
                <script type="text/javascript" async="async">
/*<!--*/
document.write('<br/><label><input type="checkbox" class="noqr" onchange="document.getElementById(\'pwd\').type=(this.checked?\'text\':\'password\');"/> Show password</label>');
/*-->*/
                </script>
        </td>
</tr>
EOF;
        
        $rules = RULES;
        
        $form.="<form id=\"postform\" action=\"".PHP_SELF."\" method=\"post\" enctype=\"multipart/form-data\">";
        $form.="<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"".$maxbyte."\"/>";
	$form.="<input type=\"hidden\" name=\"mode\" value=\"regist\"/>";
        if($resno&&!$manapost)$form.="<input type=\"hidden\" name=\"resto\" value=\"".$resno."\"/>";
        $form.= <<<EOF
                <table cellspacing="2">
                        <tbody>
                                {$inputs}
                                <tr><td colspan="2"><small>{$rules}</small></td></tr>
                        </tbody>
                </table>
        </form>
EOF;
        $form.=blotter_contents(4);
        $form.="</center><hr/>";
        if(!($resno||$admin||$manapost||$paintcom||$q))
                file_put_contents(CACHE_DIR."form.inc.html",$form);
        $dat.=$form;
}

function fakefoot() {
	$dat = '';
        $dat.= <<<EOF
        <script type="text/javascript" async="async">
/*<!--*/
document.write('<div align="right"><table id="delSub" align="right"><tbody></tbody></table></div>');
/*-->*/
        </script>
EOF;
	foot($dat);
	return $dat;
}

/* Footer */
function foot(&$dat){
        $sfoot=FOOT;$slegal=_("All trademarks, copyrights, comments, and images on this page are owned by and are the responsibility of their respective parties.");
        $dat.="<br clear=\"all\"/>";
        if(BOARDLINKS)$dat.="<span class=\"boardlist\">".BOARDLINKS."</span>";
        $gentime=gentime();
        if(SHOWTITLEIMG){
                $dat.="<h1><center class=\"logo\">";
                $dat.="<img src=\"".TITLEIMG."\" onload=\"this.style.opacity=1;\" onclick=\"this.style.opacity=0.5;this.src='".TITLEIMG."?'+(new Date().getTime());\" border=\"1\" alt=\"".TITLE."\"/>";
                $dat.="</center></h1>";
        }
        $dat.= <<<EOF
                <center>
                        <div>{$gentime}</div>
                        <small>{$sfoot}</small>
                        <div class="disclaimer"><small>{$slegal}</small></div>
                </center>
                <div id="bottom"></div>
        </body>
</html>
EOF;
}

function error($mes){ /* Basically a fancy die() */
	global $upfile_name,$json_response,$dat;
        if($json_response){
                header('Content-Type: application/json');
                die(json_encode(["status"=>"error","error"=>$mes]));
        }
	if(!$dat)head($dat,"<meta name=\"robots\" content=\"noindex, nofollow\">");
	echo $dat;
	die("<table width=\"100%\" height=\"200\"><tbody><tr valign=\"middle\"><td><center><h2 id=\"errormsg\">".$mes."</h2><big>[<a href=\"".PHP_SELF2."\">".lang("Return")."</a>]</big></center></td></tr></tbody></table>".fakefoot());
        die(fakefoot());
}

function auto_link($proto) {
	$proto = preg_replace("#(https?|ftp|news|irc|gopher|telnet|ssh)(://[[:alnum:]\+\$\;\?\.%,!\#~*/:@&=_-]+)#","<a href=\"\\1\\2\" rel=”noreferrer” target=\"_blank\">\\1\\2</a>",$proto);
	return $proto;
}

function closetags($html){ //https://gist.github.com/JayWood/348752b568ecd63ae5ce
        preg_match_all('#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        if (count($closedtags) == $len_opened)
                return $html;
        $openedtags = array_reverse($openedtags);
        for ($i=0; $i < $len_opened; $i++) {
                if (!in_array($openedtags[$i], $closedtags))
                        $html .= '</'.$openedtags[$i].'>';
                else
                        unset($closedtags[array_search($openedtags[$i], $closedtags)]);
        }
        return $html;
}


function catalog() {
	$dat = '';
	head($dat,"<meta name=\"robots\" content=\"index, follow\"/>");
	$dat.="<center class=\"replymode\"><big>".lang("View mode: Catalog")."</big></center>";
        form($dat);
        $dat.=ctrlnav("catalog",true);
        $dat.="<center>";
	$dat.="<table border=\"1\" id=\"catalog\"><tbody><tr>";
	$i=0;
	$result = mysqli_call("SELECT * FROM ".POSTTABLE." WHERE `resto`=0 ORDER BY `root` DESC");
	while($thread=mysqli_fetch_assoc($result)){
		$dat.="<td width=\"100\" height=\"100\" valign=\"top\">";
                $dat.="<center><a href=\"".PHP_SELF."?res=".$thread["no"]."\">";
                if($thread["md5"]){
                        $temp=explode(',',$thread["ext"]);
                        $src = IMG_DIR.$thread["tim"].end($temp);
                        if(file_exists($src)){
                                switch(explode('/',mime_content_type($src))[0]){
                                        case "image":
                                                $dat.="<img src=\"".THUMB_DIR.$thread["tim"]."c.jpg\" ".
                                                        ($thread["h"]>$thread["w"]?"height=\"100\"":"width=\"100\"")." alt=\"***\"/>";
                                                break;
                                        default:
                                                $dat.="<img src=\"file.png\" width=\"100\" height=\"100\" border=\"0\" alt=\"***\"/>";
                                                break;
                                }
                        }else $dat.="<img src=\"filedeleted-cat.gif\" width=\"100\" vspace=\"2\" border=\"0\" alt=\"***\"/>";
                }else $dat.="***";
                $dat.="</a><small><br/><span class=\"meta\" title=\"".lang("(R)eplies / (I)mage Replies")."\"><font size=\"1\">";
                $dat.="R: <b>".mysqli_num_rows(mysqli_call("SELECT * FROM ".POSTTABLE." WHERE `resto`=".$thread["no"]));
                $dat.="</b> / I: <b>".mysqli_num_rows(mysqli_call("SELECT * FROM ".POSTTABLE." WHERE `md5`!='' AND `resto`=".$thread["no"]));
                $dat.="</b></font></span><br/>";
                if($thread["sub"])$dat.="<span class=\"subject\"><b>".$thread["sub"]."</b></span><br/>";
                //$dat.=substr($thread["com"],0,120);
                $dat.="</small></center></td>";
                $i++;
                if($i==10){
                        $dat.="</tr><tr>";
                        $i=0;
                }
	}
        //if($i)$dat.=str_repeat("<td></td>",10-$i);
	mysqli_free_result($result);
	$dat.="</tr></tbody></table></center>";
	$dat.=fakefoot();
	return $dat;
}

function rss(){
        $title=TITLE;
        $here=HERE;$index=PHP_SELF2;
        $desc=DESCRIPTION;
        $result=mysqli_call("SELECT  * FROM ".POSTTABLE." ORDER BY `no` DESC LIMIT ".RSS_LIMIT);
        $items='';
        while($row=mysqli_fetch_assoc($result)){
                $link=HERE.PHP_SELF."?res=".$row["no"];
                $items.= <<<EOF
<item>
        <title>{$row["sub"]}</title>
        <link>{$link}</link>
        <guid>{$link}</guid>
        <comments>{$link}</comments>
        <author>{$row["name"]}</author>
        <description><![CDATA[ {$row["com"]} ]]></description>
</item>
EOF;
        }
        $rss= <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
        <channel>
                <title>{$title}</title>
                <link>{$here}{$index}</link>
                <description>{$desc}</description>
                {$items}
        </channel>
</rss>
EOF;
        return $rss;
}

function rebuild($output_started=false,$echo=true){
        global $q;
        $q=false;
        if($echo){
                if(!$output_started)echo "<html><body>";
                echo lang("Rebuilding all threads and pages")."<br>";
        }
        
        updatelog();
        if($echo)echo lang("Index pages created")."<br>";
        
        if(!$threads=mysqli_call("SELECT * FROM ".POSTTABLE." WHERE `resto`=0".
                " ORDER BY `sticky` DESC,`root` DESC"))error(lang("Critical SQL problem!"));
        while($thread=mysqli_fetch_assoc($threads)){
                $thread_htm=updatelog($thread["no"]);
                $fix_dirs=array_unique([HOME,RES_DIR,JS_DIR,CSS_DIR,THUMB_DIR,IMG_DIR,EMOTES_DIR,FLAGS_DIR,PHP_PLAYER,
                        CAPTCHA_IMG,PHP_SELF,PHP_SELF2,PHP_CAT,PHP_API,PHP_BANNED,PHP_BLOTTER,"sticky.gif","closed.gif","file.png",
                        "filedeleted.gif","filedeleted-res.gif","filedeleted-cat.gif",TITLEIMG,RSS]);
                foreach($fix_dirs as $dir){
                        if(strpos($dir,'/')||substr($dir,-1)!='/')$thread_htm=str_replace($dir,"../".$dir,$thread_htm);
                }
                file_put_contents(RES_DIR.$thread["no"].PHP_EXT,$thread_htm);
        }
        file_put_contents(RES_DIR."index".PHP_EXT,"<meta http-equiv=\"refresh\" content=\"0;URL=../".PHP_SELF2."\"/>");
        if($echo)echo lang("Threads created")."<br/>";
        
        file_put_contents(PHP_CAT,catalog());
        if($echo)echo lang("Catalog created")."<br/>";
        
        if(USE_RSS){
                file_put_contents(RSS,rss());
                if($echo)echo lang("RSS feed created")."<br/>";
        }
        
        if(!$echo)return;
        if(error_get_last())
                echo lang("An error has occured&hellip;")."<br/></body><head>";
        else if(!$output_started){
                echo lang("Redirecting back to board.")."<br/>";
                echo "</body><head><meta http-equiv=\"refresh\" content=\"0;URL=".PHP_SELF2."\"/>";
        }else echo "</body><head>";//The 2 headed beast
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"".
                STYLES[(isset($_COOKIE["fikabastyle"])?$_COOKIE["fikabastyle"]:CSSDEFAULT)]."\"/>";
        echo "</head></html>";
}
