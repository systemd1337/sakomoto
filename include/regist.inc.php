<?php
/*
 * Function for registering new posts
 */

if(basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
        die("This file is not to be called directly.");

function regist($ip,$name,$capcode,$email,$sub,$com,$url,$pwd,$resto,$spoiler) {
	global $con,$path,$pwdc,$textonly,$admin,$time,$tim,$upfiles,$upfiles_names,$upfiles_errors,$upfiles_count,$verif,$json_response;
        $resto=(int)$resto;
        
	if (isbanned($ip)) error(lang("Error: you are banned. Post discarded. ".
                "Check on the status of your ban ")."<a href=\"".PHP_BANNED."\">".lang("here")."</a>".lang("."));
	if ($_SERVER["REQUEST_METHOD"] != "POST") error(lang("Error: Unjust POST."));
        if(CAPTCHA_DRIVER&&$verif!=$_SESSION['captcha_key'])error(lang("Invalid captcha."));
        if($resto){
                $thread=mysqli_fetch_assoc(mysqli_call("SELECT closed,resto FROM ".POSTTABLE." WHERE `no`=".$resto));
                if(!$thread)error(lang("Error: That thread no longer exists."));
                if($thread["closed"])error(lang("Error: This thread is closed."));
                if($thread["resto"])error(lang("Error: That post is not a thread."));
        }else{
                $thread=false;
                $result=mysqli_call("SELECT no,time FROM ".POSTTABLE." WHERE `host`='".$ip."' AND `resto`=0 AND `sticky`=0 ORDER BY `no` DESC");
                if(mysqli_num_rows($result)>RENZOKU4)error(lang("You can only have ").RENZOKU4.lang(" active threads at a time."));
                if(mysqli_fetch_assoc($result)["time"]>($time-RENZOKU3))error(lang("You must wait longer before making another thread."));
        }
	foreach(BADSTRING as $value){
                if(strpos(' '.$value,$com) ||strpos(' '.$value,$sub)||
                   strpos(' '.$value,$name)||strpos(' '.$value,$email))
                        error(lang("Error: String refused."));
        }
        
	// upload processing
        $file=[];
        $files=$upfiles_count;
        $file["filename"]=$file["ext"]=$file["md5"]=$file["fsize"]=$file["spoiler"]=
        $file["w"]=$file["h"]=$file["tn_w"]=$file["tn_h"]=[];
        $mes='';
        if($files){
                require_once(CORE_DIR."thumbnail.inc.php");
                while($files--){
//                      $filename=$mes=$ext=$md5='';
//                      $w=$h=$tn_w=$tn_h=$fsize=0;
                        if($upfiles[$files]){
                                switch ($upfiles_errors[$files]) {
                                        case UPLOAD_ERR_OK:
                                                break;
                                        case UPLOAD_ERR_FORM_SIZE:
                                                error(lang("This image is too large! Upload something smaller!"));
                                                break;
                                        case UPLOAD_ERR_INI_SIZE:
                                                error(lang("This image is too large! Upload something smaller!"));
                                                break;
                                        case UPLOAD_ERR_PARTIAL:
                                                error(lang("The uploaded file was only partially uploaded."));
                                                break;
                                        case UPLOAD_ERR_NO_TMP_DIR:
                                                error(lang("Missing a temporary folder."));
                                                break;
                                        case UPLOAD_ERR_CANT_WRITE:
                                                error(lang("Failed to write file to disk"));
                                                break;
                                        default:
                                                error(lang("Unable to save the uploaded file."));
                                }
                        }
                        if (file_exists($upfiles[$files])) {
                                $md5=$file["md5"][] = md5_of_file($upfiles[$files]);
                                foreach(BADFILE as $value) {
                                        if (preg_match("/^".$value."/",$md5))
                                                error(lang("Error: Duplicate md5 checksum detected.")); //Refuse this image
                                }
                                //Duplicate image check
                                if(DUPECHECK){
                                        $result = mysqli_call("select tim,ext,md5 from ".POSTTABLE." where md5='".$md5."'");
                                        if ($result) {
                                                list($timp,$extp,$md5p) = mysqli_fetch_row($result);
                                                mysqli_free_result($result);
                                                if ($timp) error(lang("Error: Duplicate file entry detected."));
                                        }
                                }
                                $fsize=$file["fsize"][] = filesize($upfiles[$files]);
                                if ($fsize>MAX_KB * 1024) error(lang("This image is too large! Upload something smaller!"));
                                
                                $upfile_name = CleanStr($upfiles_names[$files]);
                                $ext='.'.strtolower(pathinfo(basename($upfiles_names[$files]),PATHINFO_EXTENSION));
                                $filename=$file["filename"][]=strtolower(pathinfo(basename($upfiles_names[$files]),PATHINFO_FILENAME));
                                if(!$ext)$ext='.'.end(explode('.',$filename));
                                $file["ext"][]=$ext;
                                
                                $size = getimagesize($upfiles[$files]);
                                if($size&&$ext!=".webm"){
                                        $w=$file["w"][] = $size[0];
                                        $h=$file["h"][] = $size[1];
                                }else $w=$file["w"][]=$h=$file["h"][]=0;
                                
                                $fileno=($files?'_'.($files+1):'');
                                //Note: move_uploaded_file breaks oekaki
                                copy($upfiles[$files],IMG_DIR.$tim.$fileno.$ext);
                                thumb(IMG_DIR,$tim,$ext,$fileno);
                                
                                // Picture reduction
                                if ($size && ($w > MAX_W || $h > MAX_H)) {
                                        $w2 = MAX_W / $w;
                                        $h2 = MAX_H / $h;
                                        $key=($w2 < $h2) ? $w2 : $h2;
                                        $tn_w=$file["tn_w"][] = floor($w * $key);
                                        $tn_h=$file["tn_h"][] = floor($h * $key);
                                }
                                $mes.=$upfiles_names[$files]." uploaded<br/><br/>";
                                $file["spoiler"][]=$spoiler=($spoiler=="on");
                        }else if(!$resto&&FORCEIMAGE) error(lang("Error: No file selected."));
                        else if(!$com) error(lang("Error: No text entered."));
                        else $spoiler=false;
                }
        }
        
	// Form content check
	if (!$name||preg_match("/^[ |@|]*$/",$name)) $name = '';
	if (!$com||preg_match("/^[ |@|\t]*$/",$com)) $com = '';
	if (!$sub||preg_match("/^[ |@|]*$/",$sub)) $sub = '';

	if (strlen($com) > 10000) error(lang("Error: Field too long."));
	if (strlen($name) > 100) error(lang("Error: Field too long."));
	if (strlen($email) > 100) error(lang("Error: Field too long."));
	if (strlen($sub) > 100) error(lang("Error: Field too long."));
	if (strlen($resto) > 10) error(lang("Error: Abnormal reply."));
	if (strlen($url) > 10) error(lang("Error: Abnormal reply."));

        if($resto){
                // Number of log lines
                $result=mysqli_call("select * from ".POSTTABLE." where resto=0");
                $threadcount=mysqli_num_rows($result)+1;
                mysqli_free_result($result);
                
                /* Purge old threads */
                $result=mysqli_call("select no,ext,tim from ".POSTTABLE." where resto=0 order by root asc");
                while ($threadcount>THREADLIMIT) {
                        list($dno,$dext,$dtim)=mysqli_fetch_row($result);
                        if (!mysqli_call("delete from ".POSTTABLE." where no=".$dno))error("Critical SQL problem!");
                        if (!mysqli_call("delete from ".POSTTABLE." where resto=".$dno))error("Critical SQL problem!");
                        if ($dext) {
                                if (is_file($path.$dtim.$dext)) unlink($path.$dtim.$dext);
                                if (is_file(THUMB_DIR.$dtim.'s.jpg')) unlink(THUMB_DIR.$dtim.'s.jpg');
                        }
                        $threadcount--;
                }
                mysqli_free_result($result);
        }

	//host check
	$host = gethostbyaddr($_SERVER["REMOTE_ADDR"]);

        $pxck=false;
	if (preg_match("/^mail/",$host)
	|| preg_match("/^ns/",$host)
	|| preg_match("/^dns/",$host)
	|| preg_match("/^ftp/",$host)
	|| preg_match("/^prox/",$host)
	|| preg_match("/^pc/",$host)
	|| preg_match("/^[^\.]\.[^\.]$/",$host)) {
		$pxck = true;
	}

	if ($pxck && PROXY_CHECK && !isset($_SESSION['name'])) {
		if (proxy_connect('80') == true) {
			error(lang("Error: Proxy detected on :80."));
		} elseif (proxy_connect('8080') == true) {
			error(lang("Error: Proxy detected on :8080."));
		}
	}

	// No, path, time, and url format
	srand((double)microtime()*1000000);
	if ($pwd) {
		$pwd=substr(md5($pwd),2,8);
	}else{
		if ($pwdc) $pwd=$pwdc;
		else $pwd=substr(rand(),0,8);
                setcookie ("pwdc", $pwd,$time+7*24*3600);
        }
        $c_pass=$pwdc;

        $now=humantime($time);
	$posterid = substr(crypt(md5($_SERVER["REMOTE_ADDR"].'id'.gmdate("Ymd", $time+9*60*60)),'id'),-8);
	// Text plastic surgery (rorororor)
	$email = CleanStr($email); $email = preg_replace("/[\r\n]/", "", $email);
	$sub = CleanStr($sub); $sub = preg_replace("/[\r\n]/", "", $sub);
	$url = CleanStr($url); $url = preg_replace("/[\r\n]/", "", $url);
	$name=preg_replace("/[\r\n]/", "", $name);
	$com = CleanStr($com);
	// Standardize new character lines
	$com = str_replace( "\r\n", "\n", $com);
	$com = str_replace( "\r", "\n", $com);
	// Continuous lines
	$com = preg_replace("/\n((!@| )*\n) {3,}/","\n",$com);
	$com = str_replace("\n", "<br/>", $com);	// \n is erased (is this necessary? [yes])
	$com=preg_replace("/&gt;/i", ">", $com);
	$com=preg_replace("/(^|>|&gt;)(\>[^<]*)/i", "\\1<span class=\"unkfunc\">\\2</span>", $com);
	$com=preg_replace("/(^|<|&lt;)(\<[^<]*)/i", "\\1<span class=\"pinktext\">\\2</span>", $com);
	$com=auto_link($com);
	$com=preg_replace_callback("/\>\>([0-9]+)/i", "postLink", $com);
        //bbcode
        $bbopen=[];
        foreach(BBCODES as $bb => $code){
                $com=str_replace('['.$bb.']','<'.$code.'>',$com);
                $com=str_replace("[/".$bb.']',"</".explode(' ',$code)[0].'>',$com);//Close without the attributes
        }
        //Emotes
        foreach(EMOTES as $emote => $emotefile){
                $com=str_replace(":".$emote.":","<img alt=\"".$emote."\" src=\"".EMOTES_DIR.$emotefile."\" border=\"0\"/>",$com);
        }
        //Fortune
        if(FORTUNE&&stristr($email,"fortune")){
                $fortunes = ["Bad Luck","Average Luck","Good Luck",
                        "Excellent Luck","Reply hazy, try again","Godly Luck",
                        "Very Bad Luck","Outlook good","Better not tell you now",
                        "You will meet a dark handsome stranger",
                        "&#65399;&#65408;&#9473;&#9473;&#9473;&#9473;&#9473;&#9473;(&#65439;&#8704;&#65439;)&#9473;&#9473;&#9473;&#9473;&#9473;&#9473; !!!!",
                        "&#65288;&#12288;´_&#12445;`&#65289;&#65420;&#65392;&#65437; ",
                        "Good news will come to you by mail"];
                $fortunenum = rand(0,sizeof($fortunes)-1);
                $fortcol = "#" . sprintf("%02x%02x%02x",
                        127+127*sin(2*M_PI * $fortunenum / sizeof($fortunes)),
                        127+127*sin(2*M_PI * $fortunenum / sizeof($fortunes)+ 2/3 * M_PI),
                        127+127*sin(2*M_PI * $fortunenum / sizeof($fortunes) + 4/3 * M_PI));
                $com.="<font color=\"".$fortcol."\"><b>Your fortune: ".$fortunes[$fortunenum]."</b></font>";
        }

        //Filters
	foreach (FILTERS as $filterin => $filterout) {
		$com = str_replace($filterin, $filterout, $com);
	}

	// Add capcode
	if ($capcode && isset($_SESSION['capcode']) && $_SESSION['cancap'])
                $capcode=$_SESSION['capcode'];
        else $capcode='';

        //Tripcodes
        $tripcode="";
        $names="";
        if(strstr($name,"#")){
                $names=explode("#",$name,3);
                if(isset($names[2]))$sectripcode=$names[2];
                $tripcode=$names[1];
                $name=$names[0];
        }
        if(isset($sectripcode)&&$sectripcode){
                $result=mysqli_call("select password,capcode,cancap from ".MANATABLE);
                while($row=mysqli_fetch_assoc($result)){
                        if(sha1($sectripcode)==$row["password"]&&$row["cancap"])
                                $capcode=$row["capcode"];
                }
                mysqli_free_result($result);
                $tripcode="!!".str_rot13(base64_encode(pack("H*",sha1($sectripcode.SEED))));
        }else if($tripcode){
                $salt=strtr(preg_replace("/[^\.-z]/",".",substr($tripcode."H.",1,2)),":;<=>?@[\\]^_`","ABCDEFGabcdef");
                $tripcode="!".substr(crypt($tripcode,$salt),-10);
        }

        if($tripcode)$tripcode=substr($tripcode,0,11);
        
	$name = trim(rtrim($name));//blankspace removal
	if (get_magic_quotes_gpc())//magic quotes is deleted (?)
		$name = stripslashes($name);
	$name = htmlspecialchars($name);//remove html special chars

	if (!$name||(FORCED_ANON&&!$_SESSION['name'])) $name=DEFAULT_NAME; //Moderators can post with name when forced anon is on
	if (!$com) $com = DEFAULT_COMMENT;
	if (!$sub) $sub = DEFAULT_SUBJECT;
        
        $com=closetags($com);
        
	// Read the log
        $lastno=mysqli_num_rows(mysqli_call("SELECT * FROM ".POSTTABLE));
	$query="select time from ".POSTTABLE." where com='".mysqli_escape_string($con, $com)."' ".
		"and host='".mysqli_escape_string($con, $host)."' ".
		"and no>".($lastno-20); // The same
	if (!$result=mysqli_call($query))error("Critical SQL problem!");
	if (mysqli_fetch_array($result)&&!$upfiles_count)error(lang("Error: Flood detected."));
	mysqli_free_result($result);

	$restoqu=(int)$resto;
	if ($resto) { //res,root processing
		if (!$resline=mysqli_call("select * from ".POSTTABLE." where resto=".$resto))error("Critical SQL problem!");
		$countres=mysqli_num_rows($resline);
		mysqli_free_result($resline);
		if (!stristr($email,'sage') && $countres < BUMPLIMIT) {
			$query="update ".POSTTABLE." set root=now() where no=".$resto; //age
			if (!$result=mysqli_call($query))error("Critical SQL problem!");
		}
	}
        
        $country=$country_name='';
        if(COUNTRY_FLAGS){
                /*$geo=json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$_SERVER["REMOTE_ADDR"]));
                $country=$geo->geoplugin_countryCode;
                $country_name=$geo->geoplugin_countryName;*/
                
                require_once(CORE_DIR."lib/geoip/geoip.inc.php");
                $gi=geoip\geoip_open(CORE_DIR."lib/geoip/GeoIPv6.dat", GEOIP_STANDARD);
                $country=geoip\geoip_country_code_by_addr_v6($gi, ipv4to6($_SERVER['REMOTE_ADDR']));
		$country_name=geoip\geoip_country_name_by_addr_v6($gi, ipv4to6($_SERVER['REMOTE_ADDR']));
                if(!$country)$country="XX";
        }
        
	if(!$result=mysqli_call("INSERT INTO ".POSTTABLE.
                " (no,now,name,trip,capcode,email,sub,com,host,pwd,country,country_name,".
                "filedeleted,filename,ext,w,h,tn_w,tn_h,spoiler,time,tim,md5,fsize,num_files,".
                "root,resto,ip,id,sticky,closed) VALUES (".
                        "0,".
                        "'".$now."',".
                        "'".mysqli_escape_string($con, $name)."',".
                        "'".mysqli_escape_string($con, $tripcode)."',".
                        "'".mysqli_escape_string($con, $capcode)."',".
                        "'".mysqli_escape_string($con, $email)."',".
                        "'".mysqli_escape_string($con, $sub)."',".
                        "'".mysqli_escape_string($con, $com)."',".
                        "'".mysqli_escape_string($con, $host)."',".
                        "'".mysqli_escape_string($con, $pwd)."',".
                        "'".$country."',".
                        "'".$country_name."',".
                        "0,".
                        "'".mysqli_escape_string($con, implode(',',$file["filename"]))."',".
                        "'".mysqli_escape_string($con, implode(',',$file["ext"]))."',".
                        "'".(implode(',',$file["w"]))."','".(implode(',',$file["h"]))."',".
                        "'".(implode(',',$file["tn_w"]))."','".(implode(',',$file["tn_h"]))."',".
                        "'".(implode(',',$file["spoiler"]))."',".
                        $time.",".$tim.",".
                        "'".implode(',',$file["md5"])."',".
                        "'".(implode(',',$file["fsize"]))."',".
                        "".($upfiles_count).",".
                        "now(),".
                        ((int)$resto).",".
                        "'".$_SERVER["REMOTE_ADDR"]."',".
                        "'".$posterid."',".
                        "0,0".
                        ")"))error(lang("Critical SQL problem!"));// post registration

	//Cookies
	setcookie("pwdc",$c_pass,time()+7*24*3600); /* 1 week cookie expiration */
	if (function_exists("mb_internal_encoding") && function_exists("mb_convert_encoding") && function_exists("mb_substr")) {
		if (preg_match("/MSIE|Opera/", $_SERVER["HTTP_USER_AGENT"])) {
			$i = 0; $c_name = '';
			mb_internal_encoding("SJIS");
			while ($j = mb_substr($names, $i, 1)) {
				$j = mb_convert_encoding($j, "UTF-16", "SJIS");
				$c_name .= "%u".bin2hex($j);
				$i++;
			}
			header("Set-Cookie: namec=$c_name; expires=".gmdate("D, d-M-Y H:i:s",time()+7*24*3600)." GMT",false);
		} else {
			if(is_array($names))$c_name=implode('#',$names);
                        else $c_name=$names;
			setcookie ("namec",$c_name,time()+7*24*3600); /* 1 week cookie expiration */
		}
	}

        if(!error_get_last()&&!$json_response)
                echo "<html><head><meta http-equiv=\"refresh\" content=\"1;URL=".
                        (stristr($email,"nonoko")||!$resto?PHP_SELF2:PHP_SELF."?res=".$resto)."\"/></head><body>";

        if($json_response){
                rebuild(false,false);
                header('Content-Type: application/json');
                die(json_encode(["status"=>"success"]));
        }
        
        echo $mes;
        if(!$mes)echo lang("Post submitted")."<br/>";
	rebuild(true);
}
