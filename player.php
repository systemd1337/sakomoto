<?php
//Vichan webm player
/* This file is dedicated to the public domain; you may do as you wish with it. */
$v = @(string)$_GET['v'];
if(!is_file($v))die("<html><body>That file does not exist&hellip;</body></html>");
$t = @(string)$_GET['t'];
$loop = @(boolean)$_GET['loop'];
$params = '?v=' . urlencode($v) . '&amp;t=' . urlencode($t);
?><!DOCTYPE html>
<html>
        <head>
            <meta charset="utf-8">
            <title><?php echo htmlspecialchars($t); ?></title>
            <style>
body {
    background: black;
    color: white;
    margin: 0px;
}
#playerheader {
    position: absolute;
    left: 0px;
    right: 0px;
    top: 0px;
    height: 24px;
    padding: 0px 4px;
    text-align: right;
    font-size: 16px;
    z-index: 10;
}
#playerheader:hover{background-color:rgba(0,0,0,0.5);}
#playerheader a {
    color: white;
    text-decoration: none;
}
span.settings div {
    background: black;
    z-index: 1;
    padding-right: 4px;
}
#playercontent {
    position: absolute;
    left: 0px;
    right: 0px;
    top: 24px;
    bottom: 0px;
}
video {
    display: block;
    margin-left: auto;
    margin-right: auto;
    max-width: 100%;
    max-height: 100%;
}
                </style>
                <script>
if (typeof _ == 'undefined') {
  var _ = function(a) { return a; };
}

// Default settings
var defaultSettings = {
    "videoexpand": true,
    "videohover": false,
    "videovolume": 1.0
};

// Non-persistent settings for when localStorage is absent/disabled
var tempSettings = {};

// Scripts obtain settings by calling this function
function setting(name) {
    if (localStorage) {
        if (localStorage[name] === undefined) return defaultSettings[name];
        return JSON.parse(localStorage[name]);
    } else {
        if (tempSettings[name] === undefined) return defaultSettings[name];
        return tempSettings[name];
    }
}

// Settings should be changed with this function
function changeSetting(name, value) {
    if (localStorage) {
        localStorage[name] = JSON.stringify(value);
    } else {
        tempSettings[name] = value;
    }
}

// Create settings menu
var settingsMenu = document.createElement("div");
var prefix = "", suffix = "", style = "";
if (window.Options) {
  var tab = Options.add_tab("webm", "video-camera", _("WebM"));
  $(settingsMenu).appendTo(tab.content);
}
else {
  prefix = '<a class="unimportant" href="javascript:void(0)">'+_('WebM Settings')+'</a>';
  settingsMenu.style.textAlign = "right";
  settingsMenu.style.background = "inherit";
  suffix = '</div>';
  style = 'display: none; text-align: left; position: absolute; right: 1em; margin-left: -999em; margin-top: -1px; padding-top: 1px; background: inherit;';
}

settingsMenu.innerHTML = prefix
    + '<div style="'+style+'">'
    + '<label><input type="checkbox" name="videoexpand">'+_('Expand videos inline')+'</label><br>'
    + '<label><input type="checkbox" name="videohover">'+_('Play videos on hover')+'</label><br>'
    + '<label><input type="range" name="videovolume" min="0" max="1" step="0.01" style="width: 4em; height: 1ex; vertical-align: middle; margin: 0px;">'+_('Default volume')+'</label><br>'
    + suffix;

function refreshSettings() {
    var settingsItems = settingsMenu.getElementsByTagName("input");
    for (var i = 0; i < settingsItems.length; i++) {
        var control = settingsItems[i];
        if (control.type == "checkbox") {
            control.checked = setting(control.name);
        } else if (control.type == "range") {
            control.value = setting(control.name);
        }
    }
}

function setupControl(control) {
    if (control.addEventListener) control.addEventListener("change", function(e) {
        if (control.type == "checkbox") {
            changeSetting(control.name, control.checked);
        } else if (control.type == "range") {
            changeSetting(control.name, control.value);
        }
    }, false);
}

refreshSettings();
var settingsItems = settingsMenu.getElementsByTagName("input");
for (var i = 0; i < settingsItems.length; i++) {
    setupControl(settingsItems[i]);
}

if (settingsMenu.addEventListener && !window.Options) {
    settingsMenu.addEventListener("mouseover", function(e) {
        refreshSettings();
        settingsMenu.getElementsByTagName("a")[0].style.fontWeight = "bold";
        settingsMenu.getElementsByTagName("div")[0].style.display = "block";
    }, false);
    settingsMenu.addEventListener("mouseout", function(e) {
        settingsMenu.getElementsByTagName("a")[0].style.fontWeight = "normal";
        settingsMenu.getElementsByTagName("div")[0].style.display = "none";
    }, false);
}
    </script>
    <script>
if (window.addEventListener) window.addEventListener("load", function(e) {
    document.getElementById("playerheader").appendChild(settingsMenu);

    var video = document.getElementsByTagName("video")[0];

    var loopLinks = [document.getElementById("loop0"), document.getElementById("loop1")];
    function setupLoopLink(i) {
        loopLinks[i].addEventListener("click", function(e) {
            if (!e.shiftKey && !e.ctrlKey && !e.altKey && !e.metaKey) {
                video.loop = (i != 0);
                if (i != 0 && video.currentTime >= video.duration) {
                    video.currentTime = 0;
                }
                loopLinks[i].style.fontWeight = "bold";
                loopLinks[1-i].style.fontWeight = "inherit";
                e.preventDefault();
            }
        }, false);
    }
    for (var i = 0; i < 2; i++) {
        setupLoopLink(i);
    }

    video.muted = (setting("videovolume") == 0);
    video.volume = setting("videovolume");
    video.play();
}, false);
                </script>
        </head>
        <body>
            <div id="playerheader">
                [<a id="loop0" href="<?php echo $params; ?>&amp;loop=0"<?php if (!$loop) echo ' style="font-weight: bold"'; ?>>play once</a>]
                [<a id="loop1" href="<?php echo $params; ?>&amp;loop=1"<?php if ($loop) echo ' style="font-weight: bold"'; ?>>loop</a>]
            </div>
            <div id="playercontent">
                <video controls<?php if ($loop) echo ' loop'; ?> src="<?php echo htmlspecialchars($v); ?>">
                    Your browser does not support HTML5 video. [<a href="<?php echo htmlspecialchars($v); ?>">Download</a>]
                </video>
            </div>
        </body>
</html>
