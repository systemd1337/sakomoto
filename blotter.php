<?php
const TITLE="CHANGEME";
const CSSDEFAULT = "Sakomoto"; // The name of the stylesheet to be used by default
const STYLES = array( // Array containing NAME => FILE of stylesheets
        "Giko"=>"giko.css",
	"Sakomoto"	=>	"sakomoto.css",
	"Yotsuba"	=>	"yotsuba.css",
	"Yotsuba B"	=>	"yotsublue.css",
	"Miku"     	=>	"miku.css",
	"Futaba"	=>	"futaba.css",
	"Burichan"	=>	"burichan.css",
	"Tomorrow"	=>	"tomorrow.css",
	"Photon"	=>	"photon.css",
	"Gurochan"	=>	"gurochan.css"
);
const BLOTTERTABLE = "CHANGEME"; Blotter table
const SQLHOST = "CHANGEME"; //MySQL server address, usually localhost
const SQLUSER = "CHANGEME"; //MySQL user (must be changed)
const SQLPASS = "CHANGEME"; //MySQL user's password (must be changed)
const SQLDB = "CHANGEME"; //Database used by image board
const BOARDLINKS = "[ a / b / c ]";

/* END OF CONFIG */
const FOOT= <<<EOF
                                <br clear="all"/>
                                <small>- <a href="https://github.com/rileyjamesbell/sakomoto/" target="_blank">sakomoto</a> -</small>
                        </p>
                </center>
        </body>
</html>
EOF;

function humantime($time) {
	$youbi = array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
	$yd = $youbi[gmdate("w", $time+9*60*60)];
	return gmdate("y/m/d",$time+9*60*60)."(".(string)$yd.")".gmdate("H:i",$time+9*60*60);
}

header("expires:0");
header("last-modified:".gmdate("D, d M Y H:i:s")." GMT");
header("cache-control:no-store, no-cache, must-revalidate");
header("pragma:no-cache");

if(!$con=mysqli_connect(SQLHOST,SQLUSER,SQLPASS))
	die("MySQL connection failure.");	//unable to connect to DB (wrong user/pass?)

if(!$db_id = mysqli_select_db($con, SQLDB))
	die("Database error, check SQL settings.");

//Head
$title=TITLE;
echo <<<EOF
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="content-type"  content="text/html;charset=utf-8"/>
                <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate"/>
                <meta http-equiv="pragma" content="no-cache"/>
                <meta http-equiv="expires" content="0"/>
                <meta name="robots" content="noarchive"/>
                <meta name="robots" content="index follow"/>
                <meta http-equiv="content-language" content="en"/>
                <meta name="language" content="en"/>
                <meta property="og:locale" content="en"/>
                <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes"/>
                <meta name="revisit-after" content="15 days"/>
                <meta name="referrer" content="origin"/>
                <meta name="theme-color" content="#FFD7B0"/>
                <meta name="msapplication-TileColor" content="#FFD7B0"/>
                <meta name="msapplication-navbutton-color" content="#FFD7B0"/>
                <meta property="og:type" content="website"/>
                <meta name="msapplication-window" content="width=1024;height=768"/>
                <meta name="pinterest" content="nopin"/>
                <title>{$title}</title>
                <style type="text/css">
.logo,.logohr{display:none;}
        #blotter,#blotter td,#blotter th{
        border-width:1px;
        border-color:black;
        border-style:solid;
}
                </style>
                <script src="js/sakomoto.js" type="text/javascript"></script>
EOF;
foreach(STYLES as $stylename => $stylefile) {
        echo "<link rel=\"".($stylename==CSSDEFAULT?'':"alternate ")."stylesheet\" type=\"text/css\" ".
                "href=\"css/styles/".$stylefile."\" title=\"".$stylename."\"/>";
}
echo "<script type=\"text/javascript\">var cssdef='".CSSDEFAULT."';</script>";
$boardlinks=BOARDLINKS;
echo <<<EOF
</head>
<body>
        <div id="top"></div>
        <script type="text/javascript">
/*<!--*/
document.write('<div align="right"><table id="delSub" align="right"><tbody></tbody></table></div>');
/*-->*/
        </script>
        <div class="boardNav"><span class="boardlist">{$boardlinks}</span></div>
        <br clear="all"/>
EOF;
$query="SELECT * FROM ".BLOTTERTABLE." ORDER BY `id` DESC";
if(!$result=mysqli_query($con,$query))die("<center><p>Critical SQL problem".FOOT);
if(!mysqli_num_rows($result))die("<center><p><i>No blotter</i>".FOOT);
echo <<<EOF
<center>
        <p>
                <table id="blotter" width="80%" border="1" bordercolor="black" cellspacing="0">
                        <thead>
                                <tr>
                                        <th class="postblock">Time</th>
                                        <th class="postblock">Message</th>
                                </tr>
                        </thead>
                        <tbody>
EOF;
while($row=mysqli_fetch_assoc($result)){
        echo "<tr bgcolor=\"white\" color=\"black\">";
        echo "<td><center><font color=\"black\">".humantime($row["time"])."</font></center></td>";
        echo "<td width=\"75%\"><font color=\"black\">".$row["message"]."</font></td>";
        echo "</tr>";
}
echo <<<EOF
        </tbody>
</table>
EOF.FOOT;
?>
