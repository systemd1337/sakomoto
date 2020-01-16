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

require_once("config.inc.php");
require_once(CORE_DIR."init.inc.php");

if(!is_file(USR_LST))touch(USR_LST);
chmod(USR_LST,0666);
$usr_arr=file(USR_LST);
$ips='';

$fp=fopen(USR_LST,"w");

$ip_hash=sha1($ip);
for ($i=0;$i<sizeof($usr_arr);$i++) {
        list($ip_addr,$stamp)=explode("|",$usr_arr[$i]);
        if (($time-(int)$stamp)<IP_COUNT_TIMEOUT&&$ip_addr!=$ip_hash){
                fputs($fp,implode("|",[$ip_addr,$stamp]));
        }
}

fputs($fp,implode("|",[$ip_hash,$time])."\n");
fclose($fp);
clearstatcache();

$a=lang("Currently ")."<b>".count($usr_arr)."</b>".lang(" unique users on this board.");
switch($return){
        case "json":
                echo json_encode(["count"=>count($usr_arr)]);
                break;
        case "js":
        case "javascript":
                echo <<<EOF
document.write("{$a}");
EOF;
                break;
        case "html":
        case "":
        default:
                $timeout=IP_COUNT_TIMEOUT;$lang=LANGUAGE;
                $sipcount=lang("IP count");
                echo <<<EOF
                <html lang="{$lang}">
                        <head>
                                <meta charset="UTF-8"/>
                                <meta http-equiv="content-type"  content="text/html;charset=utf-8"/>
                                <title>{$sipcount}</title>
                                <meta http-equiv="refresh" content="{$timeout}"/>
                                <meta http-equiv="cache-control" content="no-cache,no-store,must-revalidate"/>
                                <meta http-equiv="cache-control" content="max-age=0"/>
                                <meta http-equiv="pragma" content="no-cache"/>
                                <meta http-equiv="expires" content="0"/>
                                <meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT"/>
                                <meta name="robots" content="noindex noarchive"/>
                                <meta name="generator" content="sakomoto"/>
                                <meta name="pinterest" content="nopin"/>
                                <meta http-equiv="Content-Script-Type" content="text/javascript"/>
                                <meta http-equiv="Content-Style-Type" content="text/css"/>
                                <meta http-equiv="content-type"  content="text/html;charset=utf-8"/>
                                <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
                                <style>
                body{
                        background-color:black;
                        background-image:none!important;
                        color:#0F0;
                        padding:0!important;
                        margin:0!important;
                }
                                </style>
                EOF;
                foreach(STYLES as $stylename => $stylefile) {
                        echo "<link charset=\"UTF-8\" rel=\"".($stylename==CSSDEFAULT?'':"alternate ")."stylesheet\" type=\"text/css\" ".
                                "href=\"".CSS_DIR."styles/".$stylefile."\" title=\"".$stylename."\"/>";
                }
                echo "</head><body>".$a."</body></html>";
        break;
}
?>