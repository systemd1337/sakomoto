<?php

/*-----今何人カウンタ-----
ソース元：レッツPHP! / URL：http://php.s3.to/
改造：爺ちゃんねる / URL：http://w6.oroti.com/‾gchan/
Refined by RJB / URL : https://github.com/rileyjamesbell/sakomoto/

◇◇◇設置方法◇◇◇

 【public_html】
	｜
        └[BBS]┐                 
	       ├count.php [604] このスクリプト
	       ├user.cgi  [606] ログファイル
	       └bbs.php   [604] ←このスクリプトなどのHTML表示部分にインナーフレームで埋め込む

◇◇◇今何人カウンタを表示したいページに以下↓のタグを埋め込む◇◇◇  ※ src=\"./count.php\" と「\」が必要な場合もある
<iframe src="./count.php" width="100%" height="15" scrolling="no" frameborder="0" marginwidth="0" marginheight="0" border="0"></iframe>
-----今何人カウンタ-----*/

// 閲覧者ログファイル
define("USR_LST", "user.db.txt");
// リストに何秒間残すか
define("TIMEOUT", 300);

if(!is_file(USR_LST))touch(USR_LST);
chmod(USR_LST,0666);
$usr_arr = file(USR_LST);
$fp = fopen(USR_LST, "w");
$now = time();
$addr = $_SERVER['REMOTE_ADDR'];

for ($i = 0; $i < sizeof($usr_arr); $i++) {
  list($ip_addr,$stamp) = explode("|", $usr_arr[$i]);
  if (($now - $stamp) < TIMEOUT && $ip_addr != $addr) {
      fputs($fp, $ip_addr."|".$stamp);
  }
}
fputs($fp, $addr."|".$now."\n");
fclose($fp);

$count=count($usr_arr);$timeout=TIMEOUT;
echo <<<EOF
<html lang="en">
        <head>
                <meta charset="UTF-8"/>
                <title>Count</title>
                <meta http-equiv="refresh" content="{$timeout}"/>
        </head>
        <body bgcolor="#000000" text="#00ff00">
                <font style="font-size: 12px;">Currently <b>{$count}</b> unique users on this board.</font>
        </body>
</html>
EOF;
?>
