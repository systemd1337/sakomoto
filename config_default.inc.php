<?php
const LOCKDOWN = false;	//Set to true to disable posting for users (not for managers)
error_reporting(E_ALL);

// General settings
const HEAD_EXTRA=""; //Extra HTML to append to the head
const HERE="http://mysite.tld/b/"; //URL link to this board
const LANGUAGE = 'en'; //Language. Fikaba provides en and ja by default
const BOARDLINKS = "[<a href=\"/a/\">a</a>/ <a href=\"/b/\">b</a> ...]"; //Boardlinks at top
//Rules under post form
const RULES = <<<EOF
<ul>
        <li>Rule 1</li>
        <li>Rule 2</li>
        <li>Rule 3</li>
        <li><iframe src="./count.php" width="100%" height="15" scrolling="no" frameborder="0" marginwidth="0" marginheight="0" border="0"></iframe></li>
</ul>
EOF;
const SHOWTITLEIMG = true; //Show image at top
const SHOWTITLETXT = true; //Show TITLE at top
const TITLEIMG = "banners/index.php"; //Banner image
const SEED = "CHANGEME"; //Set to some random text (Do not change after initial run)
const USE_GZIP=true; //Output buffer compression

//JavaScript settings
const ENABLEAPI = true; //Enable the JSON API?
const JSVARS = [ //Extra vars for JS
//var=>val
"jsPath"=>"js/jquery"
];
const JSPLUGINS=[ //Aditional JavaScripts (Relative to JS_DIR)
"jquery/stylebuttons.js",
"jquery/main.js",
"jquery/suite_settings.js",
"jquery/post_menu.js",
"jquery/custom_css.js",
"jquery/custom_js.js",
"jquery/custom_board_list.js",
"jquery/images_only.js",
"jquery/thread_stats.js",
"jquery/image_expansion.js",
"jquery/image_hover.js",
"jquery/thread_updater.js",
"jquery/quote_hover.js",
"jquery/inline_quote.js",
"jquery/center_threads.js",
"jquery/mark_quote.js",
"jquery/backlink_on_bottom.js",
"jquery/hide_back_links.js",
"jquery/quote_hash_nav.js",
"jquery/thread_nav.js",
"jquery/persistent_boards.js",
"jquery/thread_expansion.js",
"jquery/thread_watcher.js",
"jquery/tree_view.js",
"jquery/infinite_scroll.js",
"jquery/wysibb.js",
"jquery/nav_arrows.js",
];

/* Posting */
const BBCODES = [
//bb=>code
"bold"=>"b",
"b"=>"b",
"spoiler"=>"span style=\"background-color:black;color:black;\" onmouseover=\"this.style.color='white';\" onmouseout=\"this.style.color='black';\"",
"s"=>"span style=\"background-color:black;color:black;\" onmouseover=\"this.style.color='white';\" onmouseout=\"this.style.color='black';\"",
"italic"=>"i",
"i"=>"i",
"strike"=>"s",
"underline"=>"u",
"u"=>"u",
"aa"=>"font face=\"Mona,monospace\"",
];
const EMOTES = [
//emote=>file
"nigra"=>"nigra.gif",
"sage"=>"sage.gif",
"longcat"=>"longcat.gif",
"tacgnol"=>"tacgnol.gif",
"angry"=>"emo-yotsuba-angry.gif",
"astonish"=>"emo-yotsuba-astonish.gif",
"biggrin"=>"emo-yotsuba-biggrin.gif",
"closed-eyes"=>"emo-yotsuba-closed-eyes.gif",
"closed-eyes2"=>"emo-yotsuba-closed-eyes2.gif",
"cool"=>"emo-yotsuba-cool.gif",
"cry"=>"emo-yotsuba-cry.gif",
"dark"=>"emo-yotsuba-dark.gif",
"dizzy"=>"emo-yotsuba-dizzy.gif",
"drool"=>"emo-yotsuba-drool.gif",
"glare"=>"emo-yotsuba-glare.gif",
"glare1"=>"emo-yotsuba-glare-01.gif",
"glare2"=>"emo-yotsuba-glare-02.gif",
"happy"=>"emo-yotsuba-happy.gif",
"huh"=>"emo-yotsuba-huh.gif",
"nosebleed"=>"emo-yotsuba-nosebleed.gif",
"nyaoo-closedeyes"=>"emo-yotsuba-nyaoo-closedeyes.gif",
"nyaoo-closed-eyes"=>"emo-yotsuba-nyaoo-closedeyes.gif",
"nyaoo"=>"emo-yotsuba-nyaoo.gif",
"nyaoo2"=>"emo-yotsuba-nyaoo2.gif",
"ph34r"=>"emo-yotsuba-ph34r.gif",
"ninja"=>"emo-yotsuba-ph34r.gif",
"rolleyes"=>"emo-yotsuba-rolleyes.gif",
"rollseyes"=>"emo-yotsuba-rolleyes.gif",
"sad"=>"emo-yotsuba-sad.gif",
"smile"=>"emo-yotsuba-smile.gif",
"sweat"=>"emo-yotsuba-sweat.gif",
"sweat2"=>"emo-yotsuba-sweat2.gif",
"sweat3"=>"emo-yotsuba-sweat3.gif",
"tongue"=>"emo-yotsuba-tongue.gif",
"unsure"=>"emo-yotsuba-unsure.gif",
"wink"=>"emo-yotsuba-wink.gif",
"x3"=>"emo-yotsuba-x3.gif",
"xd"=>"emo-yotsuba-xd.gif",
"xp"=>"emo-yotsuba-xp.gif",
];
const TRIPCAP=[ //Give tripcodes a capcode
"!Ep8pui8Vw2"=>"<font color=\"pink\">## Faggot</font>",
];
const FILTERS = array( // Filters, in the format of IN => OUT
"spacechan"=>"spacecuck",
"basedpilled"=>"epic",
"unbased"=>"wow",
"based"=>"epic",
);
const BADSTRING = array(); //Posts containing any of these strings will be discarded (can be a nuisance, use with care);
const BADFILE = array(); //Files to be discarded (md5 hashes)
const PROXY_CHECK = true; //Enable proxy check
const STEAM = true; //Enable users to link to their steam profile?
//Default posting values
const DEFAULT_SUBJECT = "No subject";
const DEFAULT_NAME = "Anonymous";
const DEFAULT_COMMENT = "キターーー(゜∀゜)ーーーー!!!!!";

/* RSS */
const USE_RSS = true;
const RSS_LIMIT=15;

/* Meta */
const KEYWORDS = "anonymous,imageboard,image,board,chan,forum"; //SEO keywords
const DESCRIPTION = "Sakomoto powered image board!"; //SEO description
const TITLE = "Sakomoto powered image board!"; //Name of this imageboard
const SUBTITLE = "Only fools would post facts."; //Subtitle below title
const ICON = ""; //URL to favicon (Leave empty for none)

// Database settings
const BLOTTERTABLE = "CHANGEME"; //Blotter table
const SQLHOST = "CHANGEME"; //MySQL server address, usually localhost
const SQLUSER = "CHANGEME"; //MySQL user (must be changed)
const SQLPASS = "CHANGEME"; //MySQL user's password (must be changed)
const SQLDB = "CHANGEME"; //Database used by image board
const BANTABLE = "CHANGEME"; //Bans table (NOT DATABASE)
const MANATABLE = "CHANGEME"; //Manager (admin, mod, janitor) table
const POSTTABLE = "CHANGEME"; //Post table (NOT DATABASE)
const REPORTTABLE = "CHANGEME"; //Reports table (NOT DATABASE)

// File-related settings
const JS_DIR = "js/";
const CSS_DIR = "css/";
const HOME = '../'; //Site home directory (up one level by default
const EMOTES_DIR="emotes/";
const FLAGS_DIR="flags/";
const CORE_DIR="include/";
const PHP_BLOTTER="blotter.php"; //Name of blotter script file
const PHP_BANNED="banned.php"; //Name of b& script
const PHP_LIST="list.html";
const FFMPEG = "ffmpeg"; //ffmpeg command
const CACHE_DIR="cache/";
const IMG_DIR = 'src/'; //Image directory (needs to be 777)
const THUMB_DIR = 'src/'; //Thumbnail directory (needs to be 777)
const RES_DIR = "thread/"; //Thread directory (needs to be 777)
const MAX_KB = 2000; //Maximum upload size in KB
const MAX_W = 250; //Images exceeding this width will be thumbnailed
const MAX_H = 250; //Images exceeding this height will be thumbnailed
const MIN_W = 30; //Images smaller than this width will be refused
const MIN_H = 30; //Images smaller than this height will be refused
const ALLOWED_EXT = [ //List of allowed file extensions
        ".jpg",
        ".jpeg",
        ".jfif",
        ".gif",
        ".png",
        ".webm",
];
const OEKAKI_DRIVER = "neo"; //Leave blank to disable. Oekaki driver, available drivers are: tegaki[4chan],neo
const FORCEIMAGE = true; //Whether or not threads must start with an image
const PHP_EXT = '.html'; //Extension used for board pages after first
const PHP_SELF = 'imgboard.php'; //Name of main script file
const PHP_SELF2 = 'index'.PHP_EXT; //Name of main html file
const PHP_CAT = "catalog".PHP_EXT; //Name of catalog file
const PHP_API = "api.php"; //Name of api script file
const RSS="index.rss"; //Name of RSS index file
const DUPECHECK = true; //Check for duplicate images
const MAX_FILES = 5; //Maximum number of files
const THUMBBACK = array(255,255,238); //Thumbnail background for transp. images. Usually the background of your body element.

/* Look and behavior */
const PAGE_DEF = 15; //Threads per page
const THREADLIMIT = 200; //Maxium number of entries
const BUMPLIMIT = 100; //Maximum topic bumps
const COLLAPSENUM = 5; //Number of replies to show in the index
const FORTUNE=false; //Enable fortune in the email field
const COUNTRY_FLAGS=false; //Display poster's country flag with each post
const RES_MARK="&gt;&gt;";

/* Spam/flood protection */
const RENZOKU = 5; //Seconds between posts (floodcheck)
const RENZOKU2 = 10; //Seconds between image posts (floodcheck)
const RENZOKU3 = 15; //Seconds between threads (floodcheck)
const RENZOKU4 = 3; //Maximum active threads (floodcheck)
const RENZOKU5 = 1; //Seconds between requests (DDOS)
const BR_CHECK = 15; //Max lines per post (0 = no limit)
const DISP_ID = false; //Display user IDs
const FORCED_ANON = false; //Force anonymous posting (except for managers)
//Captcha
const CAPTCHA_DRIVER="saguaro"; //Leave blank to disable. Enable captcha verification, available drivers are: saguaro
const USE_CAPTCHA=true; //Captcha validation
const CAPTCHA_IMG="captcha.php"; //Captcha generator

/* CSS */
const CSSDEFAULT = "Sakomoto"; // The name of the stylesheet to be used by default
const STYLES = array( // Array containing NAME => FILE of stylesheets
	"Giko"	=>	"giko.css",
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
