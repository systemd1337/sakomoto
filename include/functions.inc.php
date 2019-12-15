<?php
if(basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
        die("This file is not to be called directly.");

function lang($str){
        global $lang;
        if(isset($lang[$str]))return $lang[$str];
        return $str;
}

function humantime($time) {
	$youbi = array(lang("Sun"),lang("Mon"),lang("Tue"),lang("Wed"),lang("Thu"),lang("Fri"),lang("Sat"));
	$yd = $youbi[gmdate("w", $time+9*60*60)];
	return gmdate("y/m/d",$time+9*60*60)."(".(string)$yd.")".gmdate("H:i",$time+9*60*60);
}

function ipv4to6($ip) { //Credit to tinyboard/vichan
        if (strpos($ip, ':') !== false) {
                if (strpos($ip, '.') > 0)
                        $ip = substr($ip, strrpos($ip, ':')+1);
                else return $ip;  //native ipv6
        }
        $iparr = array_pad(explode('.', $ip), 4, 0);
        $part7 = base_convert(($iparr[0] * 256) + $iparr[1], 10, 16);
        $part8 = base_convert(($iparr[2] * 256) + $iparr[3], 10, 16);
        return '::ffff:'.$part7.':'.$part8;
}

/* 
	joaoptm78@gmail.com
	http://www.php.net/manual/en/function.filesize.php#100097
*/
function humanSize($size) {
	$units = [' B', ' KB', ' MB', ' GB', ' TB'];
	for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
	return round($size, 2).$units[$i];
}

function stopsession() {
	session_unset();
	session_destroy();
}

function mysqli_call($query) {
	global $con;
	$ret=mysqli_query($con, $query) or die(mysqli_error($con));
	if (!$ret) {
		echo $query."<br />";
	}
	return $ret;
}

function proxy_connect($port) {
	$fp = @fsockopen ($_SERVER["REMOTE_ADDR"], $port,$a,$b,2);
	if (!$fp) {return false;}
	else{return true;}
}

//check version of gd
function get_gd_ver() {
	if (function_exists("gd_info")) {
		$gdver=gd_info();
		$phpinfo=$gdver["GD Version"];
	} else { //earlier than php4.3.0
		ob_start();
		phpinfo(8);
		$phpinfo=ob_get_contents();
		ob_end_clean();
		$phpinfo=strip_tags($phpinfo);
		$phpinfo=stristr($phpinfo,"gd version");
		$phpinfo=stristr($phpinfo,"version");
	}
	$end=strpos($phpinfo,".");
	$phpinfo=substr($phpinfo,0,$end);
	$length = strlen($phpinfo)-1;
	$phpinfo=substr($phpinfo,$length);
	return $phpinfo;
}

//md5 calculation for earlier than php4.2.0
function md5_of_file($inFile) {
	if (file_exists($inFile)) {
		if (function_exists('md5_file')) {
			return md5_file($inFile);
		} else {
			$fd = fopen($inFile, 'r');
			$fileContents = fread($fd, filesize($inFile));
			fclose ($fd);
			return md5($fileContents);
		}
 	} else {
		return false;
	}
}

/* text plastic surgery */
function CleanStr($str) {
	$str = trim($str);//blankspace removal
	if (get_magic_quotes_gpc()) {//magic quotes is deleted (?)
		$str = stripslashes($str);
	}
	if (!(isset($_SESSION['cancap']) && ((int)$_SESSION['cancap'])!=0)) {
		$str = htmlspecialchars($str);//remove html special chars
		$str = str_replace("&amp;", "&", $str);//remove ampersands
	}
	return str_replace(",", "&#44;", $str);//remove commas
}

//check for table existance
function table_exist($table) {
	$result = mysqli_call("show tables like '$table'");
	if (!$result) {return 0;}
	$a = mysqli_fetch_row($result);
	mysqli_free_result($result);
	return $a;
}

/* user image deletion */
function usrdel($no,$pwd,$report=false) {
        global $onlyimgdel,$pwdc,$reason,$time;
        if($no)$_POST[$no]="delete";
        $host=gethostbyaddr($_SERVER["REMOTE_ADDR"]);
        $pwd=substr(md5($pwd),2,8);
        $pwdc=substr(md5($pwdc),2,8);
        if(!$reason)$reason="<i>".lang("No reason given.")."</i>";
        
        foreach($_POST as $no => $action){
                if($action=="delete"&&$no){
                        if(!$result=mysqli_call("SELECT tim,ext,pwd,host FROM ".POSTTABLE." WHERE no=".$no))
                                error(lang("Critical SQL problem!"));
                        if(!$post=mysqli_fetch_assoc($result))
                                error(lang("Error: That post does not exist"));
                                
                        if($report){
                                if(!mysqli_call("INSERT INTO ".REPORTTABLE." (id,time,ip,post,reason) VALUE ".
                                "(0,".$time.",'".$host."',".$no.",'".$reason."')"))
                                        error(lang("Critical SQL problem!"));
                                echo lang("Report submitted")."<br>";
                        }else{
                                if($post["pwd"]!=$pwd&&$post["pwd"]!=$pwdc&&$post["host"]!=$host)
                                        error(lang("You cannot delete this post."));
                                if(file_exists(IMG_DIR.$post["tim"].$post["ext"]))
                                        unlink(IMG_DIR.$post["tim"].$post["ext"]);
                                if(file_exists(THUMB_DIR.$post["tim"]."s".$post["ext"]))
                                        unlink(THUMB_DIR.$post["tim"]."s".$post["ext"]);
                                if(file_exists(THUMB_DIR.$post["tim"]."c".$post["ext"]))
                                        unlink(THUMB_DIR.$post["tim"]."c".$post["ext"]);
                                if($onlyimgdel)mysqli_call("UPDATE ".POSTTABLE." SET filedeleted=1 WHERE no=".$no);
                                else mysqli_call("DELETE FROM ".POSTTABLE." WHERE no=".$no);
                                echo lang("Post deleted")."<br>";
                        }
                        
                        mysqli_free_result($result);
                }
        }
}

function insertban($target,$days,$pubmsg,$privmsg,$bantype,$rmp,$rmallp,$unban) {
	$time = time();
	$daylength = 60*60*24;
	$expires = $time + ($daylength * $days);
	if ($bantype==0) {
		$result = mysqli_call("select no, ip from ".POSTTABLE);
		while ($row=mysqli_fetch_row($result)) {
			list($no, $ip)=$row;
			if ($target==(int)$no) {
				$banip=$ip;
				break;
			}
		}
		if (!isset($banip)) {die(lang("The post you\'re trying to ban for does not exist."));}
	} else {
		$banip=$target;
	}
	mysqli_free_result($result);

	if ($pubmsg && !$unban) {
		$pubmsg = strtoupper($pubmsg);
		$pubmsg = "<br /><br /><span style=\"color: red; font-weight: bold;\">($pubmsg)</span>";
		$query="update ".POSTTABLE."
			set com=concat(com,'$pubmsg')
			where no='$no'";
		if (!$result=mysqli_call($query))error(lang("Critical SQL problem!"));
		mysqli_free_result($result);
	}

	if (!$unban) {
		$query="insert into ".BANTABLE." (ip,start,expires,reason) values (
			'$banip',
			'$time',
			'$expires',
			'$privmsg')";
		if (!$result=mysqli_call($query))error(lang("Critical SQL problem!"));
		mysqli_free_result($result);
	} else {
		$query="delete from ".BANTABLE." where `ip`='$banip'";
		if (!$result=mysqli_call($query))error(lang("Critical SQL problem!"));
		mysqli_free_result($result);
	}

	if ($rmp && $bantype==0) {
		$query="delete from ".POSTTABLE." where `no`='$target'";
		if (!$result=mysqli_call($query))error(lang("Critical SQL problem!"));
		mysqli_free_result($result);
	}
	if ($rmallp) {
		$query="delete from ".POSTTABLE." where `ip`='$banip'";
		if (!$result=mysqli_call($query))error(lang("Critical SQL problem!"));
		mysqli_free_result($result);
	}
}

function removeban($ip) {
	$result = mysqli_call("select ip from ".BANTABLE);
	while ($row = mysqli_fetch_row($result)) {
		list($bip) = $row;
		if ($ip == $bip) {
			$result = mysqli_call("delete from ".BANTABLE." where `ip` = '".$ip."'");
			break;
		}
	}
	mysqli_free_result($result);
}

function isbanned($ip) { // check ban, returning true or false
	$result = mysqli_call("select ip, expires from ".BANTABLE);
	$banned = false;
	while ($row = mysqli_fetch_row($result)) {
		list($bip,$expires) = $row;
		if ($ip == $bip) {
			if ((int)$expires<time()) {
				removeban($ip);
			} else {
				return true;
			}
		}
	}
	mysqli_free_result($result);
	return false;
}

function checkban($ip) {
	$result = mysqli_call("select * from ".BANTABLE);
	$banned=false;
	while ($row=mysqli_fetch_row($result)) {
		list($bip,$time,$expires,$reason)=$row;
		if ($ip==$bip) {
			if ((int)$expires<time()) {
				removeban($ip);
				error(lang("Your ban has expired, and has been removed from the database."));
			} else {
				error(lang("You are banned!")."<br />".lang("You were banned on: ").humantime($time)."<br />".lang("Your ban expires on: ").humantime($expires));
			}
		}
	}
	if (!$banned) {
		error(lang("You are not banned. IP: ") . $ip);
	}
	mysqli_free_result($result);
}

function rmdir2($dirname) {
        $dir_handle = opendir($dirname);
        while($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                        if (is_file($dirname."/".$file))
                                unlink($dirname."/".$file);
                        else
                                rmdir2($dirname.'/'.$file);
                }
        }
        closedir($dir_handle);
        rmdir($dirname);
}
