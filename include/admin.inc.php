<?php
/*
 * Functions for administration
 */

if(basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
        die("This file is not to be called directly.");

function adminhead() {
	global $admin;
	head($dat);
	echo $dat;
        echo "<center>";
	echo("<div class=\"replymode\"><big>".lang("Manager Mode")."</big></div>");
	echo "<p><nav class=\"manabuttons\"> [<a href=\"".PHP_SELF2."\">".lang("Return")."</a>] ";
        echo "[<a href=\"".PHP_SELF."\">".lang("Rebuild")."</a>] ";
	echo("[<a class='admd$admin' href='".PHP_SELF."?mode=admin&amp;admin=del'>".lang("Post Management")."</a>] ");
	echo("[<a class='admb$admin' href='".PHP_SELF."?mode=admin&amp;admin=ban'>".lang("Ban Panel")."</a>] ");
	echo("[<a class='admp$admin' href='".PHP_SELF."?mode=admin&amp;admin=post'>".lang("Manager Post")."</a>] ");
	echo("[<a class='adma$admin' href='".PHP_SELF."?mode=admin&amp;admin=acc'>".lang("Account Management")."</a>] ");
	echo "[<a class=\"admr$admin\" href=\"".PHP_SELF."?mode=admin&amp;admin=reports\">".lang("Manage Reports")."</a>] ";
	echo "[<a class=\"adml$admin\" href=\"".PHP_SELF."?mode=admin&amp;admin=blotter\">".lang("Blotter")."</a>] ";
	echo("[<a href='".PHP_SELF."?mode=admin&amp;admin=logout'>".lang("Logout")."</a>]</nav></p>");
        echo "</center><hr>";
}

/*password validation */
function valid($pass) {
	if (isset($_SESSION['capcode'])) return;
	head($dat,"<meta name=\"robots\" content=\"noindex, nofollow\">");
	echo $dat;
	echo "<center class=\"replymode\"><big>".lang("Manager Mode")."</big></center>";
	if ($pass) {
		$result = mysqli_call("select * from ".MANATABLE);
		while ($row=mysqli_fetch_assoc($result)) {
			if (sha1($pass) == $row["password"]) {
                                unset($row["password"]);
                                foreach($row as $u => $v){$_SESSION[$u]=$v;}
				echo("<center class='passvalid'>".lang("You are now logged in.")."</center>");
				echo("<meta http-equiv=\"refresh\" content=\"2;URL=".PHP_SELF."?mode=admin\"/>");
				die(fakefoot());
			}
		}
		mysqli_free_result($result);
		die("<center>".lang("Error: Management password incorrect.")."</center>".fakefoot());
	}else{
		// Manager login form
                $ssubmit=lang("Submit");$spassword=lang("Password");
                $slogin=lang("Login");
                $self=PHP_SELF;$self2=PHP_SELF2;
                echo <<<EOF
<p>
        <center>
                <form action="{$self}" method="POST">
                        <table><tbody><tr><td><fieldset><legend>{$slogin}</legend><table><tbody><tr>
                                                <td class="postblock"><label for="password"><b>{$spassword}</b></label></td>
                                                <td>
                                                        <input type="hidden" name="mode" value="admin">
                                                        <input type="password" id="password" name="pass" size="8"><input type="submit" value="{$ssubmit}">
                                                </td>
                        </tr></tbody></table></fieldset></td></tr></tbody></table>
                </form>
        </center>
</p>
<meta http-equiv="refresh" content="60;URL={$self2}" />
EOF;
		die(fakefoot());
	}
}

function adminacc($accname,$accpassword,$acccapcode,$accdel,$accban,$acccap,$accacc,$accedit,$accflag) {
	if (!$accname) {
		$self=PHP_SELF;
                $snewacc=lang("Create a new account");$snewaccmsg=lang("Enter existing account name to modify");
                $sname=lang("Name");$smod=lang("Moderator");
                $smanasub=lang("Submit");
                $smodifiedmsg=lang("If you've modified your own account,");
                $smodifiedmsg2=lang("you may need to ");
                $smodifiedmsg3=lang(" and back in.");
                $slogoutl=lang("logout");
                $spass=lang("Password");
                $scapcode=lang("Capcode");
                $saccdel=lang("Can delete posts?");
                $saccban=lang("Can ban users?");
                $sacccap=lang("Can post with capcode?");
                $saccflag=lang("Can flag posts?");
                $saccedit=lang("Can edit posts?");
                $saccacc=lang("Can create new accounts?");
                $schangepass=lang("Change your password");$snewpass=lang("New password");
                $sconfnewpass=lang("Confirm new password");$schangesub=lang("Change password");
		echo <<<EOF
<center>
        <table>
                <tbody>
                        <tr>
                                <td>
                                        <fieldset><legend>{$snewacc}</legend>
                                                <center>
                                                        <i>{$snewaccmsg}</i>
                                                        <form action="{$self}" method="post">
                                                                <input type="hidden" name="mode" value="admin">
                                                                <input type="hidden" name="admin" value="acc">
                                                                <table>
                                                                        <tbody>
                                                                                <tr><td class="postblock"><label for="accname"><b>{$sname}</b></label></td>
                                                                                <td><input type="text" size="28" name="accname" id="accname"><input type="submit" value="{$smanasub}"></td></tr>
                                                                                <tr><td class="postblock"><label for="accpassword"><b>{$spass}</b></label></td>
                                                                                <td><input type="password" size="28" name="accpassword" id="accpassword"></td></tr>
                                                                                <tr><td class="postblock"><label for="acccapcode"><b>{$scapcode}</b></label></td>
                                                                                <td><input type="text" size="28" name="acccapcode" id="acccapcode" value='&lt;font color="purple"&gt;## {$smod}&lt;/font&gt;'></td></tr>
                                                                                <tr><td class="postblock"><label for="accdel"><b>{$saccdel}</b></label></td>
                                                                                <td><input type="checkbox" name="accdel" id="accdel" value="1"></td></tr>
                                                                                <tr><td class="postblock"><label for="accban"><b>{$saccban}</b></label></td>
                                                                                <td><input type="checkbox" name="accban" id="accban" value="1"></td></tr>
                                                                                <tr><td class="postblock"><label for="acccap"><b>{$sacccap}</b></label></td>
                                                                                <td><input type="checkbox" name="acccap" id="acccap" value="1"></td></tr>
                                                                                <tr><td class="postblock"><label for="accacc"><b>{$saccacc}</b></label></td>
                                                                                <td><input type="checkbox" name="accacc" id="accacc" value="1"></td></tr>
                                                                                <tr><td class="postblock"><label for="accedit"><b>{$saccedit}</b></label></td>
                                                                                <td><input type="checkbox" name="accedit" id="accedit" value="1"></td></tr>
                                                                                <tr><td class="postblock"><label for="accflag"><b>{$saccflag}</b></label></td>
                                                                                <td><input type="checkbox" name="accflag" id="accflag" value="1"></td></tr>
                                                                        </tbody>
                                                                </table>
                                                        </form>
                                                        <i>{$smodifiedmsg}<br>
                                                        {$smodifiedmsg2}<a href="?mode=admin&admin=logout">{$slogoutl}</a>{$smodifiedmsg3}</i>
                                                </center>
                                        </fieldset>
                        </td></tr><tr><td>
                                        <fieldset><legend>{$schangepass}</legend>
                                                <center>
                                                        <form action="{$self}?mode=admin" method="post">
                                                                <input type="hidden" name="admin" value="pass">
                                                                <table>
                                                                        <tbody>
                                                                                <tr><td class="postblock"><label for="newpass"><b>{$snewpass}</b></label></td>
                                                                                <td><input type="password" name="newpass" id="newpass" size="28"></td></tr>
                                                                                <tr><td class="postblock"><label for="confnewpass"><b>{$sconfnewpass}</b></label></td>
                                                                                <td><input type="password" name="confnewpass" id="confnewpass" size="28"></td></tr>
                                                                                <tr><td></td><td><input type="submit" value="{$schangesub}"></td></tr>
                                                                        </tbody>
                                                                </table>
                                                        </form>
                                                </center>
                                        </fieldset>
                                </td>
                        </tr>
                </tbody>
        </table>
</center>
EOF;
		die(fakefoot());
	}
	if (!$_SESSION['canacc']) die(lang("You do not have the necessary permissions to do that."));
	if (!$accdel) $accdel=0;
	if (!$accban) $accban=0;
	if (!$acccap) $acccap=0;
	if (!$accacc) $accacc=0;
	if (!$accedit) $accedit=0;
	if (!$accflag) $accflag=0;
        
        if(mysqli_fetch_assoc(mysqli_call("SELECT name FROM ".MANATABLE." WHERE `name`='".$accname."'"))){
                $query="UPDATE ".MANATABLE." SET ".
                        "password='".sha1($accpassword)."',".
                        "capcode='".$acccapcode."',".
                        "candel=".$accdel.",".
                        "canban=".$accban.",".
                        "cancap=".$acccap.",".
                        "canacc=".$accacc.",".
                        "canedit=".$accedit.",".
                        "canflag=".$accflag." ".
                        "WHERE name='".$accname."'";
                echo lang("Modifying account: ").$accname;
        }else{
                $query="insert into ".MANATABLE." (name,password,capcode,candel,canban,cancap,canacc,canedit,canflag) values (
                        '".$accname."',
                        '".sha1($accpassword)."',
                        '".$acccapcode."',
                        ".$accdel.",
                        ".$accban.",
                        ".$acccap.",
                        ".$accacc.",
                        ".$accedit.",
                        ".$accflag.")";
                echo lang("Creating account: ").$accname;
        }
        if (!$result=mysqli_call($query))error(lang("Critical SQL problem!"));
	mysqli_free_result($result);
	$query="delete from ".MANATABLE." where name='admin' and password='".sha1("password")."'";
	if (!$result=mysqli_call($query))error(lang("Critical SQL problem!"));
	mysqli_free_result($result);
	die("</body></html>");
}

function adminban() {
	global $banip,$banexp,$banpubmsg,$banprivmsg,$rmp,$rmallp,$unban,$sugip;
	if (!$_SESSION['canban']) die(lang("You do not have the necessary permissions to do that."));
	if ($banip!='') {
		if ($banexp=='') error(lang("Please give a number of days to ban this user for."));
		if (strpos($banip, '.')) {
			$banmode = 1;
		} else {
			$banexp=(int)$banexp;
			$banmode = 0;
		}
		insertban($banip,$banexp,$banpubmsg,$banprivmsg,$banmode,$rmp,$rmallp,$unban); // 0 is IP mode, 1 is post no. mode
		if ($unban) { die(lang("User unbanned")); }
		else { die(lang("User banned")); }
	}
        $self=PHP_SELF;
        $sip=lang("IP or post No.");$ssub=lang("Submit");$sxp=lang("Expires in (days)");
        $smsg=lang("Public reason (only if No. ban)");$spmsg=lang("Private reason");
        $spbmsg=lang("USER WAS BANNED FOR THIS POST");
        $srmp=lang("Remove post?");$srap=lang("Remove all posts by this IP?");$sunb=lang("Unban instead of ban");
        echo <<<EOF
<center>
        <form action="{$self}" method="POST">
                <input type="hidden" name="mode" value="admin">
                <input type="hidden" name="admin" value="ban">
                <table><tbody>
                        <tr>
                                <td class="postblock"><label for="banip"><b>{$sip}</b></label></td>
                                <td><input type="text" size="28" name="banip" id="banip" value="{$sugip}"> <input type="submit" value="{$ssub}"></td>
                        </tr>
                        <tr>
                                <td class="postblock"><label for="banexp"><b>{$sxp}</b></label></td>
                                <td><input value="7" type="number" size="5" name="banexp" id="banexp"></td>
                        </tr>
                        <tr>
                                <td class="postblock"><label for="banpubmsg"><b>{$smsg}</b></label></td>
                                <td><textarea rows="3" cols="33" name="banpubmsg" id="banpubmsg">{$spbmsg}</textarea></td>
                        </tr>
                        <tr>
                                <td class="postblock"><label for="banprivmsg"><b>{$spmsg}</b></label></td>
                                <td><textarea rows="3" cols="33" name="banprivmsg" id="banprivmsg"></textarea></td>
                        </tr>
                        <tr>
                                <td class="postblock"><label for="rmp"><b>{$srmp}</b></label></td>
                                <td><input value="7" type="checkbox" name="rmp" id="rmp" value="on"></td>
                        </tr>
                        <tr>
                                <td class="postblock"><label for="rmallp"><b>{$srap}</b></label></td>
                                <td><input value="7" type="checkbox" name="rmallp" id="rmallp" value="on"></td>
                        </tr>
                        <tr>
                                <td class="postblock"><label for="unban"><b>{$sunb}</b></label></td>
                                <td><input value="7" type="checkbox" name="unban" id="unban" value="on"></td>
                        </tr>
                </tbody></table>
        </form>
</center>
EOF;
	die(fakefoot());
}

/* Admin deletion */
function admindel() {
	global $path,$onlyimgdel,$tim;
        if(!isset($onlyimgdel))$onlyimgdel=false;
	if(!$_SESSION["candel"])die(lang("You do not have the necessary permissions to do that."));

	echo '
<form action="'.PHP_SELF.'?mode=admin" method="post">
	<table class="postlists">
		<thead><tr class="managehead"><th>'.lang("Delete?").'</th>';
        if($_SESSION['canflag'])echo '<th>'.lang("Sticky?").'</th>
                <th>'.lang("Closed?").'</th>';
        echo '<th>'.lang("Post No.").'</th><th>'.lang("Time").'</th><th>'.lang("Subject").'</th>
                <th>'.lang("Name").'</th><th>'.lang("IP").'</th><th>'.lang("Comment").'</th><th>'.lang("Host").
                '</th><th>'.lang("Filename").'</th><th>'.lang("Size").'<br />'.lang("(Bytes)").'</th>
                <th>'.lang("md5").'</th><th>'.lang("Reply #").'</th><th>'.lang("Timestamp (s)").'</th><th>'.lang("Timestamp (ms)").'</th></tr></thead>
		<tbody>';

	if(!$result=mysqli_call("select * from ".POSTTABLE." ORDER BY NO DESC"))echo S_SQLFAIL;
	while($row=mysqli_fetch_assoc($result)){
		if(isset($_POST["submit"])){
			if(isset($_POST['d'.$row["no"]])&&$_POST['d'.$row["no"]]){
				if(!isset($_POST["onlyimgdel"])||!$_POST["onlyimgdel"])mysqli_call("DELETE FROM ".POSTTABLE." WHERE `no`=".$row["no"]);
				if(is_file(THUMB_DIR.$tim.'s.jpg'))unlink(THUMB_DIR.$tim.'s.jpg');
				if(is_file(IMG_DIR.$row["tim"].'.'.$row["ext"]))unlink(IMG_DIR.$row["tim"].'.'.$row["ext"]);
				continue;
			}
                        if($_SESSION['canflag']){
                                mysqli_call("UPDATE ".POSTTABLE." SET `sticky`=".(isset($_POST['s'.$row["no"]])&&$_POST['s'.$row["no"]]=="sticky"?1:0)." WHERE `no`=".$row["no"]);
                                mysqli_call("UPDATE ".POSTTABLE." SET `closed`=".(isset($_POST['c'.$row["no"]])&&$_POST['c'.$row["no"]]=="closed"?1:0)." WHERE `no`=".$row["no"]);
                                $row=mysqli_fetch_assoc(mysqli_call("SELECT * FROM ".POSTTABLE." WHERE `no`=".$row["no"]));
                        }
		}
		echo '
			<tr id="'.$row["no"].'">
				<td><label><center><input type="checkbox" name="d'.$row["no"].'" value="delete"></center></label></td>';
                if($_SESSION['canflag'])echo '
				<td><label><center><input type="checkbox" name="s'.$row["no"].'"'.($row["sticky"]?" checked":'').' value="sticky"></center></label></td>
				<td><label><center><input type="checkbox" name="c'.$row["no"].'"'.($row["closed"]?" checked":'').' value="closed"></center></label></td>';
		echo '
				<td>'.$row["no"].'</td>
				<td>'.$row["now"].'</td>
				<td>'.$row["sub"].'</td>
				<td>'.$row["name"].'</td>
				<td><a href="?mode=admin&admin=ban&sugip='.$row["ip"].'">'.$row["ip"].'</a></td>
				<td>'.$row["com"].'</td>
				<td>'.$row["host"].'</td>
				<td>'.str_replace(",","<br/>",$row["filename"]).'</td>
				<td>'.str_replace(",","<br/>",$row["fsize"]).'</td>
				<td>'.str_replace(",","<br/>",$row["md5"]).'</td>
				<td>'.$row["resto"].'</td>
				<td>'.$row["time"].'</td>
				<td>'.$row["tim"].'</td>
			</tr>';
	}
	mysqli_free_result($result);

	echo '
		</tbody>
	</table>
	<input type="submit" name="submit" value="'.lang("Submit").'">
	<input type="reset">
</form>';

	die(fakefoot());
}
