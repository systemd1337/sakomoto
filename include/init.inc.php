<?php
if(basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"]))
        die("This file is not to be called directly.");

ignore_user_abort(true);

session_start();
header("expires:0");
header("last-modified:".gmdate("D, d M Y H:i:s")." GMT");
header("cache-control:no-store, no-cache, must-revalidate");
header("pragma:no-cache");

require_once(CORE_DIR."generate.inc.php");
require_once(CORE_DIR."functions.inc.php");
if (!file_exists('config.inc.php')) {
	include CORE_DIR.'strings/en.inc.php';
        header("content-type:text/plain");
	die(lang("Error: Imageboard must be configured before usage."));
}
require_once("config.inc.php");
include(CORE_DIR."strings/".LANGUAGE.".inc.php");// String resource file

//This software is free, all I ask in return is that you leave proper credit
const FOOT="- <a href=\"http://php.loglog.jp/\" target=\"_blank\">GazouBBS</a> + ".
"<a href=\"http://www.2chan.net/\" target=\"_blank\">futaba</a> + ".
"<a href=\"http://www.1chan.net/futallaby/\" target=\"_blank\">futallaby</a> + ".
"<a href=\"https://github.com/knarka/fikaba\" target=\"_blank\">fikaba</a> + ".
"<a href=\"https://github.com/rileyjamesbell/sakomoto\" target=\"_blank\">sakomoto</a> -";

if (LOCKDOWN||file_exists(".lockdown")) {
	// if not trying to do something other than managing, die
	if (!isset($_SESSION['capcode']) && !($_GET['mode'] == 'admin' || $_POST['mode'] == 'admin'))
		die(lang("Board is currently disabled. Please check back later!"));
}

extract($_POST, EXTR_SKIP);
extract($_GET, EXTR_SKIP);
extract($_COOKIE, EXTR_SKIP);
if(isset($post)&&$post=="post"&&isset($_SESSION["oekaki"])&&$_SESSION["oekaki"]){
        $faketmp=tmpfile();
        fwrite($faketmp,$_SESSION["oekaki"]);
        
        $_FILES["upfile0"]=[
                "name"=>"oekaki.".$_SESSION["oekaki_ext"],
                "type"=>"image/".$_SESSION["oekaki_ext"],
                "tmp_name"=>stream_get_meta_data($faketmp)['uri'],
                "error"=>0,
                "size"=>strlen($_SESSION["oekaki"])
        ];
        $_SESSION["oekaki"]='';
}
$files=MAX_FILES;
$upfiles=[];
$upfiles_names=[];
$upfiles_errors=[];
$upfiles_count=0;
while($files--){
        if (isset($_FILES["upfile".$files])&&$_FILES["upfile".$files]["tmp_name"]) {
                $upfiles_names[]= $_FILES["upfile".$files]["name"];
                $upfiles[]= $_FILES["upfile".$files]["tmp_name"];
                $upfiles_errors[]= $_FILES["upfile".$files]["error"];
                if($_FILES["upfile".$files]["name"])$upfiles_count++;
        }
}

$time = time();
$tim = $time.substr(microtime(),2,3);

if(!isset($_SESSION["last_hit"]))$_SESSION["last_hit"]=0;
$ren3=$_SESSION["last_hit"]+RENZOKU5;
if(isset($res)&&!isset($mode)&&$time<$ren3&&!isset($_SESSION["capcode"])){
        $left=abs($time-$ren3);
        echo "<meta http-equiv=\"refresh\" content=\"".$left.";url=\"/>";
        error(lang("You must wait ").$left.lang(" more seconds before making another request."));
}
$_SESSION["last_hit"]=$time;

$path = realpath("./").'/'.IMG_DIR;

$cache=[
"posts"=>[]
];

if (!$con=mysqli_connect(SQLHOST,SQLUSER,SQLPASS))
	die(lang("MySQL connection failure."));	//unable to connect to DB (wrong user/pass?)

if (!file_exists(IMG_DIR) && !is_dir(IMG_DIR)) {
	mkdir(IMG_DIR, 0777);
	echo IMG_DIR.": ".lang("Creating folder!")."<br/>";
}
if (!file_exists(THUMB_DIR) && !is_dir(THUMB_DIR)) {
	mkdir(THUMB_DIR, 0777);
	echo THUMB_DIR.": ".lang("Creating folder!")."<br/>";
}

if (!file_exists(RES_DIR) && !is_dir(RES_DIR)) {
	mkdir(RES_DIR, 0777);
	echo RES_DIR.": ".lang("Creating folder!")."<br/>";
}

$db_id = mysqli_select_db($con, SQLDB);
if (!$db_id)
	echo lang("Database error, check SQL settings.")."<br/>";

if (!table_exist(POSTTABLE)) {
	echo POSTTABLE.': '.lang("Creating table!")."<br/>";
	$result = mysqli_call("create table ".POSTTABLE." (primary key(no),
		no    int not null auto_increment,
		resto int,
		root  timestamp,
		ip    text,
		host  text,
		sticky int,
		closed int,
		now   text,
		time  int,
		email text,
                steam text,
		name  text,
		trip text,
		id    text,
		capcode text,
                country	text,
                country_name text,
		sub   text,
		com   text,
		pwd   text,
		tim   text,
                num_files int,
                filename text,
		ext   text,
		fsize text,
		md5   text,
		w     text,
		h     text,
                tn_w text,
                tn_h text,
                filedeleted int,
                spoiler text)");
	if (!$result) {
		echo lang("Unable to create table!")."<br/>";
	}
	updatelog(); // in case of a database wipe or something
}

if (!table_exist(BANTABLE)) {
	echo BANTABLE.': '.lang("Creating table!")."<br/>";
	$result = mysqli_call("create table ".BANTABLE." (ip text not null,
		start int,
		expires int,
		reason text)");
	if (!$result) {echo S_TCREATEF;}
}

if (!table_exist(BLOTTERTABLE)) {
	echo BLOTTERTABLE.': '.lang("Creating table!")."<br/>";
	$result = mysqli_call("create table ".BLOTTERTABLE." (primary key(id),
                id int not null AUTO_INCREMENT,
		time int,
		message text)");
	if (!$result) {echo lang("Unable to create table!")."<br/>";}
}

if (!table_exist(MANATABLE)) {
	echo MANATABLE.': '.lang("Creating table!")."<br/>";
	$result = mysqli_call("create table ".MANATABLE." (name text not null,
		password text not null,
		capcode text not null,
		candel int not null,
		canban int not null,
		cancap int not null,
		canacc int not null,
		canedit int not null,
		canflag int not null)");
	if (!$result) {echo lang("Unable to create table!")."<br/>";}
	$query="insert into ".MANATABLE." (name,password,capcode,candel,canban,cancap,canacc) values ('admin', '".sha1("password")."','',0,0,0,1)";
	if (!$result=mysqli_call($query))echo lang("Critical SQL problem!")."<br/>"; // Post registration
	mysqli_free_result($result);
}

if (!table_exist(REPORTTABLE)) {
	echo REPORTTABLE.': '.lang("Creating table!")."<br/>";
	$result = mysqli_call("CREATE TABLE ".REPORTTABLE." (primary key(id),
		id int not null AUTO_INCREMENT,
                time int not null,
                ip text not null,
                post int not null,
                reason text not null
                )");
	if (!$result) {echo lang("Unable to create table!")."<br/>";}
}

$ip = $_SERVER['REMOTE_ADDR'];

if(!is_dir(CACHE_DIR))mkdir(CACHE_DIR);

//Prevent notices for unset variables
$iniv=['mode','name','email','sub','com','pwd','resto','pass','res','post','no',"res","steam",
        "capcode","spoiler","admin","pass","pwdc","q","json_response","paintsizew","paintsizeh",
        "cmd","pic","start"];
foreach($iniv as $iniva){
        if(!isset($$iniva))$$iniva=false;
}
