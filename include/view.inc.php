<?php
/* 画像掲示板ビューワ

  ログから、画像情報などを抜き取り表示します
  futaba系のログ方式に対応しています

  最低限「ログファイル名」と「画像保存ディレクトリ」だけは設定してください

  2005/05/01 更新

*/

// 調整用 エラーレベル設定
//error_reporting(0);
//error_reporting(E_ALL & E_NOTICE);

// 設定項目 -------------------------------------------------------------------
// 画像掲示板（futaba.php/siokara.php/moepic.phpなど）の設定部分と同じにすること.

$bbte = "rel=\"lightbox\"";

define("LOGFILE", 'img.cgi');		// ログファイル名
define("TREEFILE", 'tree.cgi');		// ログファイル名
define("IMG_REF_DIR", 'ref/');		// 経由先html格納ディレクトリ
define("RE_COL", '#789922');		// ＞が付いた時の色

// VIEW設定基本項目 -----------------------------------------------------------
define("PHP_SELF_IMG", 'view.php');	// このスクリプト名
define("PAGE_COLS", 4);			// 1行に表示する画像数
define("HSIZE", 180);			// サムネ表示の横サイズ
define("VSIZE", 180);			// サムネ表示の縦サイズ

// VIEW設定拡張項目 ※重要※ --------------------------------------------------
// 以下の全項目は siokara.php 特有のものです.ほかのスクリプトには恐らくありません.
// 自動チェックとは siokara.php の処理によりログ内に付けられた印を自動チェックする機能です.
define("RES_FILE", 1);			// レスhtml経由機能を使用している
// レスhtml経由機能が無い、もしくは使用していない場合は、必ず '0' に設定してください
define("SAGE_START", 0);		// スレ主強制sage機能を使用している
// スレ主強制sage機能が無い、もしくは使用していない場合は、必ず '0' に設定してください

define("SAGE_MOJI", '(sage)');		// スレ主強制sageが効いていることを知らせる文字
define("UGO_MOJI", 'Animation!');	// アニメーションGIF時の文字（自動チェック:siokara.php
define("RPL_MOJI", '	');		// 画像差し替え時の文字（自動チェック:siokara.php
define("REPLACE_EXT", '.replaced');	// 差し替えの際、元々のサムネイルのお尻に付いた文字

// ナロブロ機能 ---------------------------------------------------------------
define("NARO_BURO", 0);			// ナロブロ機能を使用している
define("NOANI_OPTION", 'noani=1');	// GIF停止ページからのページの追加引数
define("PHP_EXT_NOANI", 'g'.PHP_EXT);	// 拡張子(GIF停止ページ)

// LOGS SEARCH 設定項目 -------------------------------------------------------
define("TITLE2", 'Search');	// タイトル（<title>とTOP
define("LINK_LIM", 15);			// [1] [2] [3]...の表示制限  '0'で無効化

// SQL conversion
define("POSTMAP",[
"no",
"now",
"name",
"email",
"sub",
"com",
false,
"host",
"pwd",
"ext",
"w",
"h",
"tim",
false
]);
define("TREEMAP",[
"resto",
"no"
]);
define("FOOT_ERN",<<<EOF
<center><small><p>
        - <a href="http://php.loglog.jp/" target="_blank">GazouBBS</a> +
        <a href="https://www.2chan.net/" target="_blank">futaba</a> +
        <a href="http://4ch.mooo.com/" target="_blank">Yotsubanome</a> +
        <a href="https://github.com/rileyjamesbell/sakomoto" target="_blank">sakomoto</a> -</small>
</p></small></center>
EOF);

$path = realpath("./").'/'.IMG_DIR;
$thumb_path = realpath("./").'/'.THUMB_DIR;

/* Convert SQL to flatfile format */
function fakeflatsql($table,$map){
        $result=mysqli_call("SELECT * FROM ".$table);
        $file=[];
        while($row=mysqli_fetch_assoc($result)){
                $numeric=[];
                foreach($map as $col){
                        if($col)$numeric[]=$row[$col];
                        else $numeric[]="";
                }
                $file[]=implode(",",$numeric);
        }
        return $file;
}

/* 画像一覧 */
function img_view(){
  global $path,$thumb_path,$bbte;

  // ツリーファイルからスレ元の記事No.とレス数を取得し配列に格納
//  $tree = file(TREEFILE);
  $tree=fakeflatsql(POSTTABLE,TREEMAP);
  $counttree = count($tree);
  $thread_no = array('dummy');
  for($i = 0; $i < $counttree; $i++) {
    $item = explode(",", rtrim($tree[$i]));
    $thread_no[$item[0]] = (count($item)-1);
  }

  // スレ配列からキーだけを抜き出す
  $thread_key = array_keys($thread_no);

  // 初期設定
  $image_cnt = 0;
  $image_cnt_thread = 0;
  $image_cnt_res = 0;
//  $p = 0;
  $counter = 0;
  $fimg = "";
  $finfo = "";
  $dispmsg = "";

  // 記事情報を表示するかどうか判断
  if (!isset($_POST["fileinfo"])) $_POST["fileinfo"] = "";
  $info_flag = (!strcmp($_POST["fileinfo"], "on")) ? TRUE : FALSE; // ファイル情報

  // クッキー保存
  $cooke_flag = FALSE;
  if (isset($_POST["submit"])) {
//    $cookiev = implode(",", array($info_flag));
    $cookiev = $info_flag;
    setcookie("user_data", $cookiev, time()+7*24*3600); /* 1週間で期限切れ */
    $cooke_flag = TRUE;
  }
  // クッキー読み出し
  if (isset($_COOKIE["user_data"]) && !$cooke_flag) {
//    $usdt = explode(",", $_COOKIE["user_data"]);
    $usdt = $_COOKIE["user_data"];
    $info_flag = ($usdt) ? TRUE : FALSE; // ファイル情報
  }

  // ページリンク作成
  $_GET['pg'] = (int)((isset($_GET['pg'])) ? $_GET['pg'] : 1);
  if(!$_GET["pg"])$_GET["pg"]++;
  $psta = PAGE_DEF * ($_GET['pg'] - 1) + 1;
  $pend = PAGE_DEF * $_GET['pg'];

  //ログファイル読み出し
//  $line = @file(LOGFILE);
  $line=fakeflatsql(POSTTABLE,POSTMAP);
  $countline = count($line);

  // 情報取得のため全行繰り返し
  $i = 0; // while ループ
  while($i < $countline) { // while ループ
//  for($i = 0; $i < $countline; $i++) { // for ループ
    $img_flag = FALSE;
    list($no,$now,$name,$email,$sub,$com,$url,
         $host,$pw,$ext,$w,$h,$time,$chk) = explode(",", $line[$i]);

    $i++; // while ループ
    // $extが存在しない(画像がない)場合はスキップ
    if (empty($ext)) { continue; }
    // 記事に画像があるかどうか判別
    $image_flag = (@is_file($path.$time.$ext)) ? TRUE : FALSE;
    if ($image_flag) {
      // スレ配列に該当記事があるかどうか判別
      $res_no = array_search($no, $thread_key);
      $thread_flag = ($res_no) ? TRUE : FALSE;
      // 画像数カウント
      if ($thread_flag) { $image_cnt_thread++; }
      else { $image_cnt_res++; }
      $image_cnt++;
      // 表示制限
//      $p++;
//      if ($p < $psta || $p > $pend) { continue; }
      if ($image_cnt < $psta || $image_cnt > $pend) { continue; }
      // No.変数を初期化
      $thread_time = ""; $fno = "";
      // 強制sageかどうか判断
      $sage_flag = (SAGE_START && $thread_flag && stristr($email, 'sage')) ? TRUE : FALSE;
      // アニメーションGIFかどうか判断
      $ani_flag = (!strcmp($ext, '.gif') && stristr($url, '_ugo_')) ? TRUE : FALSE;
      // 差し替えかどうか判断
      $rpl_flag = (@is_file($thumb_path.$time.'s.jpg'.REPLACE_EXT)) ? TRUE : FALSE;

      // ファイル情報を表示する
      if ($info_flag) {
        // レス主リンク
        if ($thread_flag) {
          $thread_time = $time; $fno = $no;
        } else {
          $find = FALSE;
          // ツリーファイルから、該当番号を探す
          for($j = 0; $j < $counttree; $j++) {
            $item = explode(",", rtrim($tree[$j]));
            if (!strcmp($no, $item[0]) || array_search($no, $item)) {
              $tno = $item[0];
              // ログファイルから時間を取得
              for($k = 0; $k < $countline; $k++) {
                list($nos,,,,,,,,,,,,$times,) = explode(",", $line[$k]);
                if (!strcmp($nos, $tno)) {
                  $thread_time = $times; $fno = $tno;
                  $find = TRUE;
                  break 2; // 該当するものがあればループを抜け出す
          } } } }
        }

        // スレ主、レス主を表示
        if ($thread_flag) {
          $note = 'Set: <a class="thr">OP</a>';
          if ($sage_flag) { $note .= SAGE_MOJI; }
          $note .= "<br>";
        } else {
          $note = 'Set: <a class="res">Reply</a><br>';
        }

        // 記事リンクを表示
        $no_noani = "";
        if (RES_FILE && $thread_time && @is_file(RES_DIR.$thread_time.PHP_EXT)) {
          $no_noani = '<a href="'.RES_DIR.$thread_time.PHP_EXT_NOANI.'">'.$no.'</a>';
          $no = '<a href="'.RES_DIR.$thread_time.PHP_EXT.'">'.$no.'</a>';
        } elseif (!RES_FILE && $fno) {
          $no_noani = '<a href="'.PHP_SELF.'?res='.$fno.'&'.NOANI_OPTION.'">'.$no.'</a>';
          $no = '<a href="'.PHP_SELF.'?res='.$fno.'">'.$no.'</a>';
        }
        $no = "No:".$no;
        if (NARO_BURO && RES_FILE && @is_file(RES_DIR.$thread_time.PHP_EXT_NOANI)) { $no .= " / ".$no_noani; }
        elseif (NARO_BURO && !RES_FILE) { $no .= " / ".$no_noani; }

        // 情報整理:あにGIF,差し替え
        $ugo = ($ani_flag) ? '<br><font color="#FF0099">'.UGO_MOJI.'</font>' : ""; // GIF
        $rpl = ($rpl_flag) ? '<br><font color="#FF0099">'.RPL_MOJI.'</font>' : ""; // 差し替え
      }
      // 画像リンク
      $img_flag = TRUE;
      if (@is_file($thumb_path.$time.'s.jpg')) { // サムネイルがある場合
        $scaleh = 1.0;
        $scalev = 1.0;
        if ($w > HSIZE) { $scaleh = HSIZE / $w; }
        if ($h > VSIZE) { $scalev = VSIZE / $h; }
        if ($scaleh > $scalev) { $scaleh = $scalev; }
        if ($rpl_flag) { // 差し替え切替
          $clip2 = '<img src="'.THUMB_DIR.$time.'s.jpg'.REPLACE_EXT.'" class="csrc"';
        } else {
          $clip2 = '<img src="'.THUMB_DIR.$time.'s.jpg" class="csrc"';
        }
        if (@is_file(IMG_REF_DIR.$time.'.htm')) { // ファイル経由切替
          $clip = '<a href="'.IMG_REF_DIR.$time.'.htm" '.$bbte.'>'.$clip2.
          '  width="'.ceil($w * $scaleh).'" height="'.ceil($h * $scaleh).'" alt="'.$time.$ext.'"></a>';
        } else {
          $clip = '<a href="'.IMG_DIR.$time.$ext.'" '.$bbte.'>'.$clip2.
          '  width="'.ceil($w * $scaleh).'" height="'.ceil($h * $scaleh).'" alt="'.$time.$ext.'"></a>';
        }
      } else { // サムネイルがない場合
        $clip = '<a href="'.IMG_DIR.$time.$ext.'" '.$bbte.'>'.$time.$ext.'</a>';
      }

      $fimg .= '    <td align="center" valign="middle" class="cfileimg">'.$clip."</td>\n";
      if ($info_flag) { $finfo .= '    <td align="center" valign="middle" class="cfileinfo">'.$note.$no.$ugo.$rpl."</td>\n"; }
      $disp_flag = FALSE;
      $counter++;
      if (($counter % PAGE_COLS) == 0) {
        $dispmsg .= "  <tr>\n".$fimg."  </tr>\n";
        if ($info_flag) { $dispmsg .= "  <tr>\n".$finfo."  </tr>\n"; }
        $disp_flag = TRUE;
        $fimg = ""; $finfo = ""; // クリア
      }
//      clearstatcache(); // ファイルのstatをクリア
    }
  }
//  if (!$disp_flag) {
    $dispmsg .= "  <tr>\n".$fimg."  </tr>\n";
//    if ($info_flag) { $dispmsg .= "  <tr>\n".$finfo."  </tr>\n"; }
//  }

  // ページリンク作成
  $pages = "";
  if ($image_cnt > PAGE_DEF) { // １ページのみの場合は表示しない
    $prev = $_GET['pg'] - 1;
    $next = $_GET['pg'] + 1;
//    $pages .= sprintf(" %d 番目から %d 番目の画像を表示<br>", $psta, $psta+$counter-1);
    ($_GET['pg'] > 1) ? $pages .=" <a href=\"".PHP_SELF_IMG."?pg=".$prev."\">&lt;&lt;Previous</a> " : $pages .= "&lt;&lt;Previous ";
    for($i = 1; $i <= $page_cnt; $i++) {
      if ($_GET['pg'] == $i) { // 今表示しているのはリンクしない
        $pages .= " [<b>$i</b>] ";
      } else {
        $pages .= sprintf(" [<a href=\"%s?pg=%d\"><b>%d</b></a>] ", PHP_SELF_IMG, $i, $i); // 他はリンク
      }
    }
    (($image_cnt - $pend) >= 1) ? $pages .= " <a href=\"".PHP_SELF_IMG."?pg=".$next."\">Next&gt;&gt;</a>" : $pages .= " Nextgt;&gt;";
  }

  // 表示項目選択
  $fselect = "";
  $fselect .= "<form action=\"".PHP_SELF_IMG."?pg=".$_GET['pg']."\" method=\"POST\">\n";
  $fselect .= '[<label><input type="checkbox" name="fileinfo" value="on"';
  if ($info_flag) { $fselect .= " checked"; } $fselect .= ">Show file information</label>]\n";
  $fselect .= '<BR><input type="submit" name="submit" class="potype"></form>';

  // ブラウザに表示する
  $self_img_path = PHP_SELF_IMG;
  $pgdeftxt = PAGE_DEF;
  $outnotxt = sprintf("Showing images %d to %d", $psta, $psta+$counter-1);
  $js_dir=JS_DIR;
  $css_dir=CSS_DIR;
  head($heads,<<<EOF
<script type="text/javascript" src="{$js_dir}spica.js"></script>
<script type="text/javascript" src="{$js_dir}lightbox_plus.js"></script>
<link rel="stylesheet" type="text/css" media="all" href="{$css_dir}/lightbox.css">
<style type="text/css">
<!--
body,tr,td,th { font-size:12pt; }
A:link { color:#0000EE; }
A:visited { color:#0000EE; }
A:active { color:#DD0000; }
A:hover { color:#DD0000; }
span { font-size:20pt; }
small { font-size:10pt; }

.thr { background-color:#FFFFEE; }
.res { background-color:#D6D6F6; }
.csrc { border:none; }
.cfileinfo { font-size:10pt; }

.potype {
  background-color:#F0E0D6;
  color:#800000;
  border:1px 1px 1px 1px solid #EEAA88;
}

table  { border:none; padding:0px; margin:0px; border-collapse:collapse; }
-->
</style>
EOF); // ヘッダ
echo $heads;
        echo ctrlnav("view");
  echo <<<EOD
<div align="center">
$fselect
<p>
$pages
</p>
<table>
$dispmsg</table>
<p>
$pages
</p>
<form action="$self_img_path" method="GET">
<table bgcolor="#F6F6F6">
<tr><th bgcolor="#D6D6F6" colspan=3>Information</th></tr>
<tr><td align="center">Total images:</td><td align="right"> <b>$image_cnt</b></td><!--<td>枚</td>--></tr>
<tr><td align="center">OP images:</td><td align="right"> <b>$image_cnt_thread</b></td><!--<td>枚</td>--></tr>
<tr><td align="center">Response images:</td><td align="right"> <b>$image_cnt_res</b></td><!--<td>枚</td>--></tr>
<tr><td align="center">Images per page: </td><td align="right"> <b>$pgdeftxt</b></td><!--<td>枚</td>--></tr>
<tr><td align="center" colspan=3><small>$outnotxt</small></td></tr>
<tr><td align="center" colspan=3><input type="hidden" name="mode" value="s">
<input type="text" name="w" value="" size=14 onFocus="this.style.backgroundColor='#FEF1C0';" onBlur="this.style.backgroundColor='#FFFFFF';">
<input type="submit" value="Search">
</td></tr>
</table>
</form>
<hr/>
</div>
<br clear="all"/>
<div align="right"><table id="delSub" align="right"><tbody></tbody></table></div>
<br clear="all"/>
EOD;
        echo FOOT_ERN;
  die("</body></html>");
}

/* 検索モード */
function search($word,$start,$page_def){

  if (get_magic_quotes_gpc()) $word = stripslashes($word); // ￥消去
  $joy = (trim($word)) ? "ドーモ" : "モード"; // お遊び

  $self_path = PHP_SELF;
  $self_img_path = PHP_SELF_IMG;
  $home_path = HOME;
  $title2_str = TITLE2;

  echo <<<__HEAD__
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<META name="ROBOTS" content="NOINDEX,NOFOLLOW,NOARCHIVE">
<META http-equiv="ROBOTS" content="NOINDEX,NOFOLLOW">
<META http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
<META http-equiv="Content-Style-Type" content="text/css">
<META http-equiv="Content-Script-Type" content="text/javascript">
<title>$title2_str</title>
<style type="text/css">
<!--
body,tr,td,th { font-size:12pt; }
A:link { text-decoration:none;color:#0000EE; }
A:visited { text-decoration:none;color:#0000EE; }
A:active { text-decoration:underline;color:#DD0000; }
A:hover { text-decoration:underline;color:#DD0000; }
span { font-size:20pt; }
small { font-size:10pt; }
.img { margin: 10px 10px 0px 10px; }
object,embed { margin: 10px 10px 0px 10px; }
.ctext   { color:#000000; background-color:#FFFFFF; border:1px solid #2F5376; }
.csubmit {
  font-family: Osaka , helvetica;
  font-size: 10pt;
  color:#2F5376;
  background-color:#E6E6FA;
  border:1px groove #74AFF0;
}
-->
</style>
<SCRIPT language="JavaScript">
function openWindow(url,w,h) {
var winName = 'fixedWindow';
var Width = 'width=' + w;
var Height = ',height=' + h;
var Left = ',left=' + ((screen.width - w) / 2);
var Top = ',top=' + ((screen.height- h) / 2);
var Option = ',status=0,menubar=0,scrollbars=1,resizable=1';
var features = Width + Height + Left + Top + Option;
winName = window.open(url,winName,features);
if (window.focus) {
winName.focus();
}
}
</SCRIPT>
</head>
<body bgcolor="#FFFFEE" text="#800000" link="#0000EE" vlink="#0000EE" alink="#DD0000">
<p align="right">
[<a href="$home_path" target="_top">Home</a>]
[<a href="$self_path?mode=admin">Manage</a>]
<p align="center">
<font color="#800000" size=5><b><SPAN>$title2_str</SPAN></b></font>
<p align="left">
[<a href="$self_img_path">Return</a>]
<table width="100%"><tr><th bgcolor="#BFB08F" align="center">
<font color="#551133">View mode: Search</font>
</th></tr></table>
<center>
<!--<table><tr><td align="left">
・ Separate multiple keywords with a space.<br>-->
<!--・ 検索条件は、AND検索 [A B] = (A かつ B) となっています。<br>-->
<!--・ 検索対象は、 記事No、名前、題名、本文、目欄 です。<br>-->
<!--・ Case sensitive<br>-->
<!--・ 検索単語は４色繰り返し使用して色つきで表示します。google風？<br>-->
<!--・ 変な検索方法はなるべくお控えください。バグがあればご報告を。<br>
・ この検索エンジンのベースは logoogle.php ver 0.1.1 です。<br>
</td></tr></table>-->
<hr width="90%" size=1>
<form action="$self_img_path" method="GET">
<input type="hidden" name="mode" value="s">
<input type="text" name="w" value="$word" class="ctext" size=60 onFocus="this.style.backgroundColor='#BFBFFF';" onBlur="this.style.backgroundColor='#FFFFFF';">
<input type="submit" value="Search" class="csubmit" onmouseover="this.style.backgroundColor='#A2E391';" onmouseout="this.style.backgroundColor='#E6E6FA';">
<input type="reset" value="Reset" class="csubmit" onmouseover="this.style.backgroundColor='#A2E3E1';" onmouseout="this.style.backgroundColor='#E6E6FA';">
<BR>Number of results:
<input class="csubmit" type="radio" name="pp" value="10" onmouseover="this.style.backgroundColor='#6F50FA';" onmouseout="this.style.backgroundColor='#E6E6FA';">10
<input class="csubmit" type="radio" name="pp" value="20" onmouseover="this.style.backgroundColor='#6F50FA';" onmouseout="this.style.backgroundColor='#E6E6FA';" checked>20
<input class="csubmit" type="radio" name="pp" value="30" onmouseover="this.style.backgroundColor='#6F50FA';" onmouseout="this.style.backgroundColor='#E6E6FA';">30
<input class="csubmit" type="radio" name="pp" value="40" onmouseover="this.style.backgroundColor='#6F50FA';" onmouseout="this.style.backgroundColor='#E6E6FA';">40
<input class="csubmit" type="radio" name="pp" value="50" onmouseover="this.style.backgroundColor='#6F50FA';" onmouseout="this.style.backgroundColor='#E6E6FA';">50
<input class="csubmit" type="radio" name="pp" value="100" onmouseover="this.style.backgroundColor='#6F50FA';" onmouseout="this.style.backgroundColor='#E6E6FA';">100
</form>\n
__HEAD__;

  // 前後のスペース除去
  if (trim($word) != "") {
    // 検索時間
    $ktime = getmicrotime();
    // URL エンコード
    $words = $word;
    $word2 = urlencode($words);
    // 検索文字フォーマット
//    $word = str_replace("<", "&lt;", $word); // 検索に < を含める
//    $word = str_replace(">", "&gt;", $word); // 検索に > を含める
    $word = htmlspecialchars($word); // 変換
    $word = str_replace("&amp;", "&", $word); // 特殊文字
    $word = str_replace(",", "&#44;", $word); // 検索に , を含める
    // スペース区切りを配列に
//    $word = preg_split("/(　| )+/", $word);
    $word = preg_replace('/(　| )+/', ' ', $word);
    $word = preg_replace('/(^ | $)+/', '', $word);
    $word = explode(' ', $word);
    // word に単語がない場合エラーを出す
    if ($word[0] == "") { error("Error: Invalid search query."); }
    // 重複確認
    $word = array_unique($word);
    //ログを読み込む
//    $tree = file(TREEFILE);
        $tree=fakeflatsql(POSTTABLE,TREEMAP);
//    $loge = @file(LOGFILE);
        $loge=fakeflatsql(POSTTABLE,POSTMAP);
    // ツリー配列
    $trees = array();
    foreach($tree as $l) {
      $trees[] = explode(",", /*(rtrim(*/$l/*)*/);
    }
    // ログ配列
    $logs = array();
    foreach($loge as $l) {
      $line = explode(",", rtrim($l));
      $logs[$line[0]] = $line;
    }
    // 記事No格納
    $hits = array();
    // 記事を検索する
    foreach($trees as $thread) {
      foreach($thread as $no) {
              if(!isset($logs[$no]))continue;
        $res = $logs[$no];
        $res[5] = str_replace("<br />", " ", $res[5]); // <br> を検索しちゃイヤン!
        $found = 0;
        foreach($word as $w) {
          foreach(array(0,2,3,4,5) as $idx) {//"$no,$now,$name,$email,$sub,$com,$url,$host,$pass,$ext,$W,$H,$tim,$chk,\n";
            if(strpos($res[$idx], $w) !== FALSE) {
              $found++;
              break;
            }
          }
        }
        if($found == count($word)) {
          $hits[] = array('no' => $no, 'thread' => $thread[0]);
        }
      }
    }
    // ページリンク作成
    $pages = "";
    $all = count($hits);
    $maxs = $all - 1;
    $ends = $start + $page_def - 1;
    if ($all > $page_def) {
      // prevページ
      if ($start > 0) {
        $prevstart = $start - $page_def;
        $pages .= "<a href=\"".PHP_SELF_IMG."?mode=s&w=$word2&pp=$page_def&st=$prevstart\">&lt;&lt;Previous</a>　";
      } else { $pages .= "&lt;&lt;Previous　"; }
      // ページズ
      $ima = ceil($start / $page_def); // イマノトコロ
      $goukei = ceil($all / $page_def); // スベテ
      $go = $ima - ceil(LINK_LIM / 2) + 1;
      if ($go < 0) { $go = 0; }
      $stop = $go + LINK_LIM;
      if ($stop > $goukei){
        $stop = $goukei;
        $go = $stop - LINK_LIM;
        if ($go < 0) { $go = 0; }
      }
      if (!LINK_LIM) { $go = 0; $stop = $goukei; }
      for ($a = $go; $a < $stop; $a++) {
        if ($a == $ima) { $pages .= "[<b>$a</b>] "; }
        else { $pages .= "[<a href=\"".PHP_SELF_IMG."?mode=s&w=$word2&pp=$page_def&st=".($a*$page_def)."\"><b>$a</b></a>] "; }
      }
      // nextページ
      if ($ends < $maxs) {
        $nextstart = $ends+1;
        $pages .= "　<a href=\"".PHP_SELF_IMG."?mode=s&w=$word2&pp=$page_def&st=$nextstart\">Next&gt;&gt;</a><br>";
      } else { $pages .= "　Next&gt;&gt;<br>"; }
    }

    // 検索単語を表示
    $searchlist = "";
    $i=0;$j=0;$k=0;
    foreach($word as $w) {
//      if($i++ % 2) { $bg = ($j++ % 2) ? "color:black;background-color:#ff9999;" : "color:black;background-color:#A0FFFF;"; }
//      else { $bg = ($k++ % 2) ? "color:black;background-color:#99ff99;" : "color:black;background-color:#ffff66;"; }
      if($i++ % 2) {
        if($j++ % 2){ $rpllist[] = str_replace($w, "<>>,".$w.",<<<", $w); $bg = "color:black;background-color:#ff9999;"; }
        else { $rpllist[] = str_replace($w, ">><,".$w.",<<<", $w); $bg = "color:black;background-color:#A0FFFF;"; }
      } else {
        if($k++ % 2){ $rpllist[] = str_replace($w, "><>,".$w.",<<<", $w); $bg = "color:black;background-color:#99ff99;"; }
        else { $rpllist[] = str_replace($w, ">>>,".$w.",<<<", $w); $bg = "color:black;background-color:#ffff66;"; }
      }
      $recol = str_replace($w, "<b style=\"$bg\">$w</b>", $w);
      $searchlist .= $recol." ";
    }

    // 検索結果を表示
    $resultlist = "";
    foreach($hits as $h) {
//    while(list($line, $h) = each($hits)) {
      if ($line < $start) { continue; } // ページリミット
      if(!isset($logs[$h["thread"]]))continue;
      $thread = $logs[$h['thread']];
      $res = $logs[$h['no']];
/*
      if (RES_FILE) {
        $reslist = (NARO_BURO) ? "スレリンク：<a href=\"".RES_DIR.$thread[12].PHP_EXT."\">$thread[0]</a> / <a href=\"".RES_DIR.$thread[12].PHP_EXT_NOANI."\">$thread[0]</a>" :
		"スレリンク：<a href=\"".RES_DIR.$thread[12].PHP_EXT."\">$thread[0]</a>";
      } else {
        if (NARO_BURO) { $reslist = "スレリンク：<a href=\"".PHP_SELF."?res=".$thread[0]."\">$thread[0]</a> / <a href=\"".PHP_SELF."?res=".$thread[0]."&".NOANI_OPTION."\">$thread[0]</a>"; }
        else { $reslist = "スレリンク：<a href=\"".PHP_SELF."?res=".$thread[0]."\">$thread[0]</a>"; }
      }*/
      if ($res[3]) { $email = (!strcmp($res[3], "sage")) ? "　sage" : "　<a href=\"mailto:".$res[3]."\">mail</a>"; }

      $res[5] = str_replace("<br />", "\n", $res[5]); // \nに変換
      $res = str_replace($word, $rpllist, $res); // ><, にマッチした単語を変換
//      $res = preg_replace("/\[b\](.*?)\[\/b\]/si", "<b>\\1</b>", $res);
/*
      $search = array(">>>,",
                      ">><,",
                      "><>,",
                      "<>>,",
                      ",<<<");
      $replace = array('<b style="color:black;background-color:#ffff66;">',
                       '<b style="color:black;background-color:#A0FFFF;">',
                       '<b style="color:black;background-color:#99ff99;">',
                       '<b style="color:black;background-color:#ff9999;">',
                       '</b>');
      $res = str_replace($search, $replace, $res);
*/
      $res = str_replace(">>>,", '<b style="color:black;background-color:#ffff66;">', $res);
      $res = str_replace(">><,", '<b style="color:black;background-color:#A0FFFF;">', $res);
      $res = str_replace("><>,", '<b style="color:black;background-color:#99ff99;">', $res);
      $res = str_replace("<>>,", '<b style="color:black;background-color:#ff9999;">', $res);
      $res = str_replace(",<<<", "</b>", $res);
      $res[5] = str_replace("\n", "<br>", $res[5]);  // \nを<br>に変換
      $res[5] = preg_replace("/(^|>)(&gt;[^<]*)/i", "\\1<font color=\"".RE_COL."\">\\2</font>", $res[5]); // 色をついでに付ける

//      $res[5] = preg_replace("/(^|[^=\]h])(ttp:)/i", "\\1http:", $res[5]); // ttp:→http:
//      $res[5] = preg_replace("/(https?|ftp|news)(:\/\/[0-9a-z\[\]\+\$\;\?\.%,!#‾*\/:@&=_-]+)/i", "<a href=\"\\1\\2\" target=\"_blank\">\\1\\2</a>", $res[5]); // オートリンク
//      // 長いURL省略
//      $length = 60;
//      preg_match_all("/(^|[^=\]])(https?:\/\/[\!-;\=\?-\‾]+)/si", $res[5], $reg);
//      for($i=0;$i<count($reg[0]);$i++){
//        $out[$i] = (strlen($reg[2][$i]) > $length) ? substr($reg[2][$i],0,$length)."..." : $reg[2][$i];
//        $res[5] = str_replace($reg[0][$i],$reg[1][$i]."<a href=".$reg[2][$i]." target=_blank>".$out[$i]."</a>", $res[5]);
//      }

//動画IDを構成する半角英数が検索にヒットした場合は装飾タグを無効化
if(preg_match("/ID:/",$res[4])){
      $res[4] = strip_tags($res[4]);
}
    //動画タグの挿入
if(preg_match("/^ID:/",$res[4])) {$res[4] = str_replace("ID:",  "", $res[4]);
    if(strlen($res[4]) == 11) {
      $res[v] = "<a href=\"javascript:openWindow('http://www.youtube.com/v/$res[4]',425,350)\" title=\"YouTube 動画ポップアップ再生\"><img src=\"http://img.youtube.com/vi/$res[4]/default.jpg\" border='0' alt=\"YouTube 動画ポップアップ再生\" class=\"img\" align=\"left\"></a>";
    }
    if(strlen($res[4]) == 15) {
      $res[v] = "<a href=\"javascript:openWindow('http://clipcast.jp/player/player.swf?id=$res[4]',435,400)\" title=\"ClipCast 動画ポップアップ再生\"><img src=\"http://convertor.clipcast.jp/mediastudio/player/thumbnail/index.php?id=$res[4]&s=5&size=m\" border='0' alt=\"ClipCast 動画ポップアップ再生\" class=\"img\" align=\"left\"></a>";
    }
    if(strlen($res[4]) == 14) {
      $res[v] = "<embed src=\"http://www.liveleak.com/e/$res[4]\" width=\"142\" height=\"117\" type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" scale=\"showall\" name=\"index\" align=\"left\"></embed><a href=\"javascript:openWindow('http://www.liveleak.com/e/$res[4]',425,350)\" title=\"LiveLeak 動画ポップアップ再生\">■</a>&nbsp;";
    }
    if(strlen($res[4]) == 6 || strlen($res[4]) == 7) {
      $res[v] = "<a href=\"javascript:openWindow('http://www.nicovideo.jp/watch/sm$res[4]',1024,768)\" title=\"ニコニコ動画ポップアップ再生\"><img src=\"http://tn-skr.smilevideo.jp/smile?i=$res[4]\" border='0' alt=\"ニコニコ動画ポップアップ再生\" class=\"img\" align=\"left\"> </a>";
    }
    if(strlen($res[4]) == 10 && !preg_match("/[a-zA-Z]/",$res[4])) {
      $res[v] = "<iframe width=\"150\" height=\"125\" src=\"http://ext.nicovideo.jp/thumb/$res[4]\" scrolling=\"no\" class=\"img\" align=\"left\"></iframe><a href=\"javascript:openWindow('http://www.nicovideo.jp/watch/$res[4]',1024,768)\" title=\"ニコニコ動画ポップアップ再生\">■</a>&nbsp;";
    }
    if(strlen($res[4]) == 8) {
	if (preg_match("/[a-zA-Z]/",$res[4])) {
		$res[v] = "<a href=\"javascript:openWindow('http://cliplife.jp/clip/?content_id=$res[4]',510,540)\" title=\"ClipLife 動画ポップアップ再生\"><img src=\"http://img.cliplife.jp/thumb/$res[4].jpg\" border='0' alt=\"ClipLife 動画ポップアップ再生\" class=\"img\" align=\"left\"></a>";
	}
	if (!preg_match("/[a-zA-Z]/",$res[4])) {
		$res[v] = "<a href=\"javascript:openWindow('http://www.nicovideo.jp/watch/sm$res[4]',1024,768)\" title=\"ニコニコ動画ポップアップ再生\"><img src=\"http://tn-skr.smilevideo.jp/smile?i=$res[4]\" border='0' alt=\"ニコニコ動画ポップアップ再生\" class=\"img\" align=\"left\"> </a>";
	}
    }
    if(ereg("^nm",$res[4])) {$res[4] = str_replace("nm",  "", $res[4]);
	if(strlen($res[4]) == 6 || strlen($res[4]) == 7) {
		$res[v] = "<a href=\"javascript:openWindow('http://www.nicovideo.jp/watch/nm$res[4]',1024,768)\" title=\"ニコニコ動画ポップアップ再生\"><img src=\"http://tn-skr.smilevideo.jp/smile?i=$res[4]\" border='0' alt=\"ニコニコ動画ポップアップ再生\" class=\"img\" align=\"left\"> </a>";
	}
    }
    if(ereg("^ca",$res[4])) {$res[4] = str_replace("ca",  "", $res[4]);
	if(strlen($res[4]) == 6 || strlen($res[4]) == 7) {
		$res[v] = "<a href=\"javascript:openWindow('http://www.nicovideo.jp/watch/ca$res[4]',1024,768)\" title=\"ニコニコ動画ポップアップ再生\"><img src=\"http://tn-skr.smilevideo.jp/smile?i=$res[4]\" border='0' alt=\"ニコニコ動画ポップアップ再生\" class=\"img\" align=\"left\"> </a>";
	}
    }
}

      $resultlist .= <<<END_OF_TR
<table width="80%" bgcolor="#F0E0D6" cellspacing=0 cellpadding=0 style="margin:10px 0px 10px 0px;border:1px solid #749FF1;"><tr><td>
<table width="100%" border=0 cellspacing=0 cellpadding=3><tr><td bgcolor="#eeaa88" align="left">
No.<b>$res[0]</b>　Subject:<font color="#cc1105" size="+1"><b>$res[4]</b></font>　Name:<font color="#117743"><b>$res[2]</b></font>　Date:$res[1]</td></tr>
<tr><td align="left">$res[5]</td></tr></table>
</td></tr></table>
END_OF_TR;
      if ($line == $ends) { break; } // ページリミット
    }

//    $resultlist = ($resultlist) ? $resultlist : "<table><tr><td>キーワードにマッチする記事がありませんでした。</td></tr></table>";

    // 現在の位置、フォーム
    if ($all) {
      $nowstate = ($ends < $maxs) ? "<b>".($start+1)."</b> - <b>".($ends+1)."</b>" : "<b>".($start+1)."</b> - <b>".$all."</b>";
      $forms = <<<__TMP__
<form action="$self_img_path" method="GET">
<table width="100%" border=0 cellpadding=0 cellspacing=0 style="margin:10px 0px 10px 0px;">
  <tr><td bgcolor="#3366cc"></td></tr>
  <tr>
    <td bgcolor="#e5ecf9" align="center">
      &nbsp;<br>
      <table align="center" border=0 cellpadding=0 cellspacing=0>
        <tr><td nowrap><font size="-1">
          <input type="hidden" name="mode" value="s">
          <input type="text" name="w" value="$words" class="ctext" size=60 onFocus="this.style.backgroundColor='#BFBFFF';" onBlur="this.style.backgroundColor='#FFFFFF';">
          <input type="submit" value="Search" class="csubmit" onmouseover="this.style.backgroundColor='#A2E391';" onmouseout="this.style.backgroundColor='#E6E6FA';">
          <input type="hidden" name="pp" value="$page_def">
        </font></td></tr>
      </table>
      <br><font size="-1"><a href="$self_img_path">View images</a> | <a href="$self_img_path?mode=s">Search</a><!-- | <a style="text-decoration:underline;color:#0000EE;">ヘルプ</a>--></font><br><br>
    </td>
  </tr>
  <tr><td bgcolor="#3366cc"></td></tr>
</table>
</form>
__TMP__;
    } else {
      $nowstate = "<b>0</b>";
      $joy2 = "";// お遊び
      if (count($word) - 1) {
        $joy2 = <<<__TMP__
<table cellpadding=0 cellspacing=0 border=0>
  <tr>
    <!--<td valign="bottom" height=30><font size="-1"><font color="#cc0000">ヒント:</font> より多くの検索結果を得るには、検索条件から、ぶっちゃけありえない単語を削除してください。</font></td>-->
  </tr>
</table>
__TMP__;
      }
      $forms = <<<__TMP__
<div align="left">
$joy2
<br><br>No results found for $searchlist<br><br>
Tips:
<blockquote>
- Make sure there are no typos in the keywords.<br>
- Try using different keywords.<br>
- Try using more general terms.<br>
- Try using fewer keywords.<br>
</blockquote>
<table cellpadding=0 cellspacing=0 border=0>
  <tr>
<!--    <td valign="bottom" height=30><font size="-1"><font color="#cc0000">追記:</font> 上記のことを試してもダメな場合は、恐らくもう手遅れなのでしょう。潔くあきらめてください。</font></td>-->
  </tr>
</table>
<br clear=all>
<center>
<table width="100%" border=0 cellpadding=0 cellspacing=0 style="margin:0px 0px 15px 0px;">
  <tr><td bgcolor="#3366cc"></td></tr>
  <tr><td align="center" bgcolor="#e5ecf9"><font size="-1">&nbsp;</font></td></tr>
</table>
</div>
__TMP__;
    }

    // 検索時間
    $ktime = getmicrotime() - $ktime;
    $ktime = substr($ktime, 0, 6);

    // 結果をブラウザに表示
    echo <<<EOD
<table width="100%" border=0 cellpadding=0 cellspacing=0>
  <tr><td bgcolor="#3366cc"></td></tr>
</table>
<table width="100%" bgcolor="#e5ecf9" border=0 cellpadding=0 cellspacing=0>
  <tr>
    <td bgcolor="#e5ecf9" nowrap><font face="arial,sans-serif" size="-1">&nbsp;<b>Results</b></font>&nbsp;</td>
    <td bgcolor="#e5ecf9" align="right" nowrap><font face="arial,sans-serif" size="-1">Search results for $searchlist <!--<b>$all</b>件中 $nowstate 件目  (<b>$ktime</b> 秒)&nbsp;--></font></td>
  </tr>
</table>
<p align="center">
$pages
$resultlist
$pages
$forms\n
EOD;
  }
echo "<hr>";
/*if( ( $my_html = file_get_contents( HEADERFILE, FALSE ) ) != FALSE )
{
    echo $my_html;
}*/
  echo "<small>- &copy;2005 <a href=\"http://www.nijiura.com/\" target=\"_blank\">にじうら</a> + <a href=\"http://w6.oroti.com/‾gchan/\" target=\"_blank\">gchan</a> + <a href=\"https://github.com/rileyjamesbell/sakomoto\" target=\"_blank\">sakomoto</a> -</small></center>\n";
  die("</body></html>");
}

/* 現在の時間をマイクロ秒単位で返す関数 */
function getmicrotime(){
  list($msec, $sec) = explode(" ", microtime());
  return ((float)$sec + (float)$msec);
}

/*-----------Main-------------*/
if (!isset($_REQUEST['mode'])) $_REQUEST['mode'] = "";
if (!isset($_GET["w"])) $_GET["w"] = "";
if (!isset($_GET["st"])) $_GET["st"] = 0;
if (!isset($_GET["pp"])) $_GET["pp"] = 20;
switch ($_REQUEST['mode']) {
  // 検索モード
  case 's':
    search($_GET["w"], $_GET["st"], $_GET["pp"]);
    break;
  // 通常表示
  default:
    img_view();
}
?>