<?php
# Sakomoto
#
# Based on GazouBBS, Futaba, Futallaby, Fikaba
# Much code also ported from Saguaro

if (!file_exists('config.inc.php')){
        header("content-type:text/plain");
	die("Error: Imageboard must be configured before usage.");
}
require_once("config.inc.php");
require_once(CORE_DIR."init.inc.php");

/*-----------Main-------------*/

if(!isset($res))$res=(isset($thread)?$thread:0);
switch(strtolower($mode)){
        case "nothing":
                break;
	case 'regist':
	case "post":
                require_once(CORE_DIR."regist.inc.php");
		regist($ip,$name,$capcode,$email,$sub,$com,'',$pwd,$resto,$spoiler,$steam,$sage,$nonoko,$fortune);
		break;
	case 'admin':
                require_once(CORE_DIR."admin.inc.php");
		valid($user,$pass);
		adminhead();
                switch($admin){
                        case "blotter":
                                $snewblotpost=lang("New blotter post");
                                $smsg=lang("Message");$sdel=lang("Delete?");
                                $sid=lang("ID");$stime=lang("Time");
                                echo <<<EOF
<center><table><tbody><tr><td>
        <fieldset><legend>{$snewblotpost}</legend>
                <center><form action="?" method="post">
                        <input type="hidden" name="mode" value="admin"/>
                        <input type="hidden" name="admin" value="addblotter"/>
                        <table><tbody><tr>
                                <td class="postblock"><label for="message"><b>{$smsg}</b></label></td>
                                <td><input type="text" name="msg" value="" autocomplete="off" id="message"/>
                                <input type="submit"/></td>
                        </tr></tbody></table>
                </form></center>
        </fieldset>
</td></tr></tbody></table></center>
<form action="?" method="post">
        <input type="hidden" name="mode" value="admin"/>
        <input type="hidden" name="admin" value="blotter"/>
        <table class="postlists">
                <thead>
                        <tr>
                                <th>{$sdel}</th>
                                <th>{$sid}</th>
                                <th>{$stime}</th>
                                <th>{$smsg}</th>
                        </tr>
                </thead>
                <tbody>
EOF;
                                if(!$result=mysqli_call("SELECT * FROM ".BLOTTERTABLE." ORDER BY id DESC"))error(lang("Critical SQL problem!"));
                                while($row=mysqli_fetch_assoc($result)){
                                        if(isset($_POST[$row["id"]])&&$_POST[$row["id"]]=="delete"){
                                                if(!mysqli_call("DELETE FROM ".BLOTTERTABLE." WHERE id=".$row["id"]))error(lang("Critical SQL problem!"));
                                                continue;
                                        }
                                        echo "<tr>";
                                        echo "<td><label><center><input type=\"checkbox\" name=\"".$row["id"]."\" value=\"delete\"/></center></label></td>";
                                        echo "<td><center>".$row["id"]."</center></td>";
                                        echo "<td><center>".humantime($row["time"])."</center></td>";
                                        echo "<td><center>".$row["message"]."</center></td>";
                                        echo "</tr>";
                                }
                                echo "</tbody></table><input type=\"submit\"/></form>";
                                die(fakefoot());
                                break;
                        case "addblotter":
                                $query="insert into ".BLOTTERTABLE." (time,message) values (
                                        ".$time.",".
                                        "'".$msg."')";
                                if(!$result=mysqli_call($query))error(lang("Critical SQL problem!"));
                                die("<meta http-equiv=\"refresh\" content=\"0;URL=".PHP_SELF."?mode=admin&admin=blotter\">");
                                break;
                        case "ban":
                        case "banish":
                                adminban();
                                break;
                        case "rban":
                        case "removeban":
                                if(!isset($ip)){
                                        $back=PHP_SELF."?mode=admin&admin=rban";
                                        header("location:".$back);
                                        die("<html><head><meta http-equiv=\"refresh\" content=\"0;URL=".$back."\"></head></html>");
                                }
                                removeban($ip);
                                break;
                        case "post":
                        case "regist":
                                if (!$_SESSION['cancap'])
                                        die(lang("You do not have the necessary permissions to do that."));
                                form($post, $res, 1, true);
                                echo $post;
                                die(fakefoot());
                                break;
                        case "logout":
                                stopsession();
                                echo "<meta http-equiv=\"refresh\" content=\"0;URL=".PHP_SELF2."\" />";
                                break;
                        case "acc":
                        case "accounts":
                                adminacc(
                                        (isset($accname)?$accname:false),
                                        (isset($accpassword)?$accpassword:false),
                                        (isset($acccapcode)?$acccapcode:false),
                                        (isset($accdel)?$accdel:false),
                                        (isset($accban)?$accban:false),
                                        (isset($acccap)?$acccap:false),
                                        (isset($accacc)?$accacc:false),
                                        (isset($accedit)?$accedit:false),
                                        (isset($accflag)?$accflag:false));
                                break;
                        case "pass":
                                if(!$newpass)die(lang("Password cannot be empty"));
                                if($newpass!=$confnewpass) die(lang("New password and confirmation password are different"));
                                if(!$result=mysqli_call("UPDATE ".MANATABLE." SET `password`='".sha1($newpass)."' WHERE `name`='".$_SESSION["name"]."'"))error(lang("Critical SQL problem!"));
                                die(lang("Password has been changed"));
                                break;
                        case "reports":
                        case "report":
                                $sdel=lang("Delete");$stime=lang("Time");
                                $srepid=lang("Report ID");$sreppno=lang("Reported post No.");
                                $sreason=lang("Reason");$sreportip=lang("Reporters IP");
                                echo <<<EOF
<form action="?" method="post">
        <input type="hidden" name="mode" value="admin"/>
        <input type="hidden" name="admin" value="reports"/>
        <table class="postlists">
                <thead>
                        <tr>
                                <th>{$sdel}</th>
                                <th>{$srepid}</th>
                                <th>{$stime}</th>
                                <th>{$sreppno}</th>
                                <th>{$sreason}</th>
                                <th>{$sreportip}</th>
                        </tr>
                </thead>
                <tbody>
EOF;
                                if(!$result=mysqli_call("SELECT * FROM ".REPORTTABLE." ORDER BY id DESC"))error(S_SQLFAIL);
                                while($row=mysqli_fetch_assoc($result)){
                                        if(isset($_POST[$row["id"]])&&$_POST[$row["id"]]=="delete"){
                                                if(!mysqli_call("DELETE FROM ".REPORTTABLE." WHERE id=".$row["id"]))error(S_SQLFAIL);
                                                continue;
                                        }
                                        echo "<tr>";
                                        echo "<td><a><label><center><input type=\"checkbox\" name=\"".$row["id"]."\" value=\"delete\"/></center></label></a></td>";
                                        echo "<td><center>".$row["id"]."</center></td>";
                                        echo "<td><center>".humantime($row["time"])."</center></td>";
                                        echo "<td><center><a href=\"?mode=admin&amp;admin=del#".$row["post"]."\">".$row["post"]."</a></center></td>";
                                        echo "<td><center>".$row["reason"]."</center></td>";
                                        echo "<td><center><a href=\"?mode=admin&admin=ban&sugip=".$row["ip"]."\">".$row["ip"]."</a></center></td>";
                                        echo "</tr>";
                                }
                                echo "</tbody></table><input type=\"submit\"/></form>";
                                echo fakefoot();
                                break;
                        case "del":
                        default:
                                admindel();
                                break;
                }
		break;
	case 'banned':
		checkban($ip);
		break;
	case 'catalog':
	case "cat":
		die(catalog());
		break;
        case "list":
                echo listlog();
                break;
        case "error":
        case "err"://Note: Do NOT redirect here from the script, this is for testing
                if(!isset($msg))$msg=lang("That happend");
                error($msg);
                break;
        case "find":
        case "search":
                $dat='';
                $q=htmlspecialchars(trim(rtrim($q)));
                if(!$q)error(lang("No text entered."));
                head($dat);
                $dat.="<center class=\"viewmode\"><big>".lang("View mode: Search")."</big></center><hr>";
                $findcols=["com","name","id","sub","filename","ext"];
                $dat.=ctrlnav("search");
                $colsq='';
                foreach($findcols as $col){
                        $colsq.="`".$col."` LIKE '%".$q."%' OR ";
                }
                $results=mysqli_call("SELECT * FROM ".POSTTABLE." WHERE ".$colsq." `no`=".(int)$q);
                if(!mysqli_num_rows($results))error(lang("No results."));
                while($post=mysqli_fetch_assoc($results)){
                        if($post["resto"])
                                $dat.="<h3><a href=\"".PHP_SELF."?res=".$post["resto"]."\">".lang("Thread No.").$post["resto"]."</a></h3>";
                        foreach($findcols as $col){
                                $post[$col]=str_replace($q."</font>","<font color=\"black\" style=\"background-color:yellow\">".$q."</font>",
                                        str_replace($q,$q."</font>",$post[$col]));
                        }
                        $dat.=buildPost($post);
                        $dat.="<br clear=\"all\"><hr>";
                }
                $dat.=fakefoot();
                echo $dat;
                break;
        case "report":
                usrdel($no,$pwd,true);
                echo "<meta http-equiv=\"refresh\" content=\"1;URL=".PHP_SELF2."\" />";
                break;
        case "paintpost":
                if(!OEKAKI_DRIVER)error(lang("Error: Oekaki is disabled."));
                //Get raw POST data
                ini_set("always_populate_raw_post_data", "1");
                //$buffer = $_REQUEST['HTTP_RAW_POST_DATA'];
                $buffer = file_get_contents('php://input');
                //if(!$buffer) $buffer = $HTTP_RAW_POST_DATA;
                if(!$buffer){
                        $stdin = @fopen("php://input", "rb");
                        $buffer = @fread($stdin, $_ENV['CONTENT_LENGTH']);
                        @fclose($stdin);
                }
                if(!$buffer)
                        die("Cannot read the input file data.");
                
                $headerLength = substr($buffer, 1, 8);
                $imgLength = substr($buffer, 1 + 8 + $headerLength, 8);
                $imgdata = substr($buffer, 1 + 8 + $headerLength + 8 + 2, $imgLength);
                $imgh = substr($imgdata, 1, 5);
                
                if($imgh=="PNG\r\n")
                        $_SESSION["oekaki_ext"] = 'png';	// PNG
                else
                        $_SESSION["oekaki_ext"] = 'jpg';	// JPEG
                
                $_SESSION["oekaki"]=$imgdata;
                break;
        case "paintcom":
                if(!OEKAKI_DRIVER)error(lang("Error: Oekaki is disabled."));
                if(!$_SESSION["oekaki"])error(lang("Error: No image data found!"));
                $dat='';
                head($dat);
                $dat.="<center><img border=\"1\" src=\"data:image/".$_SESSION["oekaki_ext"].
                        ";base64,".base64_encode($_SESSION["oekaki"])."\"/></center>";
                form($dat,$res,false,false,true);
                $dat.="<center><p><big>[<a href=\"".PHP_SELF2."\">".lang("Return")."</a>]</big></p></center>";
                $dat.=fakefoot();
                die($dat);
                break;
        case "paint":
                if(!OEKAKI_DRIVER)error(lang("Error: Oekaki is disabled."));
                $dat='';
                head($dat, <<<EOF
<link rel="stylesheet" href="js/neo/neo.css" type="text/css"/>
<script src="js/neo/neo.js" charset="UTF-8" type="text/javascript"></script>
EOF);
                $self=PHP_SELF;
                $self2=PHP_SELF2;
                $sjavascriptmsg=lang("You need JavaScript to use the painter.");
                $sreturn=lang("Return");
                if(!$paintsizew)$paintsizew=400;
                if(!$paintsizeh)$paintsizeh=400;
                $dat.= <<<EOF
<applet-dummy code="pbbs.PaintBBS.class" archive="js/neo/PaintBBS.jar" name="paintbbs" width="500" height="500" border="1">
        <param name="image_width" value="{$paintsizew}">
        <param name="image_height" value="{$paintsizeh}">
        <param name="image_bkcolor" value="#FFFFFF">
        <param name="image_size" value="0">
        <param name="undo" value="90">
        <param name="undo_in_mg" value="15">
        <param name="color_text" value="#EFEFFF">
        <param name="color_bk" value="#E8EFFF">
        <param name="color_bk2" value="#D5D8EF">
        <param name="color_icon" value="#A1B8D8">
        <param name="color_iconselect" value="#000000">
        <param name="url_save" value="{$self}?mode=paintpost">
        <param name="url_exit" value="{$self}?mode=paintcom&res={$res}">
        <param name="poo" value="false">
        <param name="send_advance" value="true">
        <param name="thumbnail_width" value="100%">
        <param name="thumbnail_height" value="100%">
        <param name="tool_advance" value="true">
        <param name="tool_color_button" value="#D2D8FF">
        <param name="tool_color_button2" value="#D2D8FF">
        <param name="tool_color_text" value="#5A5781">
        <param name="tool_color_bar" value="#D2D8F0">
        <param name="tool_color_frame" value="#7474AB">
</applet-dummy>
<noscript><center><h2 id="errormsg">{$sjavascriptmsg}</h2></center></noscript>
<center><p><big>[<a href="{$self2}">{$sreturn}</a>]</big></p></center>
<hr/>
EOF;
                $dat.=fakefoot();
                die($dat);
                break;
        case "rss":
                if(!USE_RSS)error(lang("Error: RSS is disabled."));
                die(rss());
                break;
        case "sam":
        case "thumblist":
                require_once(CORE_DIR."sam.inc.php");
                break;
        case "view_a":
                require_once(CORE_DIR."view.inc.php");
                break;
        case "bbcodes":
                $dat="";
                $dat.= <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta charset="UTF-8"/>
                <meta http-equiv="content-type"  content="text/html;charset=utf-8"/>
                <meta http-equiv="Content-Script-Type" content="text/javascript"/>
                <meta http-equiv="Content-Style-Type" content="text/css"/>
                <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
                <meta http-equiv="cache-control" content="no-cache,no-store,must-revalidate"/>
                <meta http-equiv="cache-control" content="max-age=0"/>
                <meta http-equiv="pragma" content="no-cache"/>
                <meta http-equiv="expires" content="0"/>
                <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT"/>
                <meta name="robots" content="noindex noarchive"/>
                <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=yes"/>
                <meta name="generator" content="sakomoto"/>
                <meta name="pinterest" content="nopin"/>
                <style>
.emote{
        max-height:50px;
        max-width:100%;
}
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
EOF;
                $dat.="<title>".TITLE."</title>";
                $dat.="<script language=\"JavaScript\" charset=\"UTF-8\" type=\"text/javascript\">var cssdef='".CSSDEFAULT."';</script>";
                $dat.="<script language=\"JavaScript\" charset=\"UTF-8\" src=\"".JS_DIR."sakomoto.js\" type=\"text/javascript\"></script>";
                foreach(STYLES as $stylename => $stylefile) {
                        $dat.="<link charset=\"UTF-8\" rel=\"".($stylename==CSSDEFAULT?'':"alternate ")."stylesheet\" type=\"text/css\" ".
                                "href=\"".CSS_DIR."styles/".$stylefile."\" title=\"".$stylename."\"/>";
                }
                $dat.="</head><body><div id=\"top\"></div>";
                $dat.="<script type=\"text/javascript\" src=\"".JS_DIR."wz_tooltip/wz_tooltip.js\"></script>";
                $semotes=lang("Emotes");
                $dat.= <<<EOF
<p>
        <table width="100%" border="1" bgcolor="white" class="set">
                <thead><tr><th align="left" class="postblock"><b>{$semotes}</b></th></tr></thead>
                <tbody><tr><td><font color="black">
EOF;
                foreach(EMOTES as $bb => $emote){
                        $dat.="<table height=\"100\" width=\"100\" align=\"left\" border=\"1\"><tbody><tr><td><center>";
                        $dat.="<img class=\"emote\" src=\"".EMOTES_DIR.$emote."\"/>";
                        $dat.="<br/><br/><div>:".$bb.":</div>";
                        $dat.="</center></td></tr></tbody></table>";
                }
                $dat.= <<<EOF
                </font></td></tr></tbody>
        </table>
</p>
EOF;
                $sbbcodes=lang("BBcodes");
                $dat.= <<<EOF
<p>
        <table width="100%" border="1" bgcolor="white" class="set">
                <thead><tr><th align="left" class="postblock"><b>{$sbbcodes}</b></th></tr></thead>
                <tbody><tr><td><font color="black">
EOF;
                foreach(BBCODES as $bb => $code){
                        $dat.="<table height=\"100\" width=\"100\" align=\"left\" border=\"1\"><tbody><tr><td><center>";
                        $dat.="<".$code.">example</".explode(" ",$code)[0].">";
                        $dat.="<br/><br/><div>[".$bb."]".lang("example")."[/".$bb."]</div>";
                        $dat.="</center></td></tr></tbody></table>";
                }
                $dat.= <<<EOF
                </font></td></tr></tbody>
        </table>
</p>
EOF;
                $dat.="<center><button onclick=\"window.close();\">Close</button></center>";
                $dat.= <<<EOF
<script type="text/javascript" async="async">
/*<!--*/
document.write('<div align="right"><table id="delSub" align="right"><tbody></tbody></table></div>');
/*-->*/
</script>
EOF;
                $dat.="<br clear=\"all\"/><center>".FOOT."</center></body></html>";
                echo $dat;
                break;
        case "ipcount":
        case "usrcount":
                if(!USE_IP_COUNT)die(lang("IP count is disabled."));
                require_once(CORE_DIR."count.inc.php");
                break;
        case "random":
        case "randomthread":
        case "randomres":
        case "rand":
                $res=mysqli_fetch_assoc(mysqli_call("SELECT no FROM ".POSTTABLE." WHERE `resto`=0 ORDER BY RAND() LIMIT 1"))["no"];
	case "usrdel":
	case "del":
	case "delete":
                if(!$pwd)$pwd=$pwdc;
		usrdel($no,$pwd);
        case "res":
        case "thread":
	case "rebuild":
	case "rebuildall":
	default:
		if($res)die(updatelog((int)$res));
		rebuild();
                break;
}
