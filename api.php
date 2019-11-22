<?php
/*
 * Sakomoto JSON API
 * Re-written by lolico
 */

require_once("config.inc.php");
require_once(CORE_DIR."init.inc.php");

if(!ENABLEAPI)die(lang("API is currently disabled."));
$db=new mysqli(SQLHOST, SQLUSER, SQLPASS, SQLDB);
if($db->connect_errno)die(_("Unable to connect to MySQL database ;_;")."<br>".$db->connect_errno);

function unsetUnsafe($safe){
        unset($safe["pwd"]);
        unset($safe["ip"]);
        unset($safe["host"]);
        if(!DISP_ID)unset($safe["id"]);
        return $safe;
}

header("content-type:application/json");
switch(strtolower($mode)){
        case "threads":
                $threads=[];
                $threads["threads"]=[];
                $results=mysqli_query($db,"SELECT * FROM ".POSTTABLE." WHERE `resto`=0");
                while($thread=mysqli_fetch_assoc($results)){
                        $thread=unsetUnsafe($thread);
                        $threads["threads"][]=$thread;
                }
                echo json_encode($threads);
                break;
        case "res":
        case "thread":
                $thread=[];
                $thread["posts"]=[];
                $resultthread=mysqli_query($db,"SELECT * FROM ".POSTTABLE." WHERE `no`=".$res);
                if(!$resultthread)die(json_encode(["error"=>lang("That thread does not exist anymore")]));
                $thread["posts"][]=unsetUnsafe(
                        mysqli_fetch_assoc($resultthread));
                $results=mysqli_query($db,"SELECT * FROM ".POSTTABLE." WHERE `resto`=".$res);
                while($reply=mysqli_fetch_assoc($results)){
                        $reply=unsetUnsafe($reply);
                        $thread["posts"][]=$reply;
                }
                echo json_encode($thread);
                break;
        case "find":
        case "search":
                $posts=[];
                $posts["search"]=[];
                $results=mysqli_query($db,"SELECT * FROM ".POSTTABLE." WHERE `com` LIKE '%".$q."%' OR `sub` LIKE '%".$q."%' OR  `no`=".(int)$q);
                while($post=mysqli_fetch_assoc($results)){
                        $post=unsetUnsafe($post);
                        $posts["search"][]=$post;
                }
                echo json_encode($posts);
                break;
        default:
                echo json_encode(["error"=>lang("Malformed request")]);
                break;
}
