# Sakomoto imageboard software
Sakomoto is an imageboard script based on fikaba

*I suck at writing documentation...*
## Credit
* Knarka (for making the script which this was based on, fikaba)
* Moot + Thatdog (for making the script which fikaba was based on, futallaby)
* Developers of 2chan.net (for making the script which futallaby was based on, futaba)
* LetsPHP (for making the script which futaba was based on, GazouBBS)
* Developers of Vichan (some code was ported from Vichan/tinyboard)
* Team4chan (for accidently leaking yotsuba, the script 4chan runs on. Some code was ported from there)
* Yushe (some code and tips)
* Repod (made the JS for Saguaro beta, some JS was ported from there)
# Dependencies
* PHP ~7
* MySQL
* GD 2.x
* FFMPEG (For creating webm thumbnails)
# Installation
1. Installing Sakomoto is easy, copy an "config_default.inc.php" config to "config.inc.php" in the same directory as "imgboard.php"
2. Adjust config to suit the board. MAKE SURE YOU CONFIGURE YOUR DATABASE SETTINGS.
3. Access "imgboard.php" from a browser
4. Login to the management panel by clicking "[Manage]" at the top right. (Default password is "password")
5. Click "[Account Management]" and setup accounts
You're done!
## Seting up multiple boards
BANTABLE, MANATABLE and BLOTTERTABLE should be configured the same accross all boards.

Just doing that would work...

But I recomend you change CORE_DIR, JS_DIR, CSS_DIR, FLAGS_DIR, EMOTES_DIR, PHP_BLOTTER, PHP_BANNED and CAPTCHA_IMG one level up and move those directories/files in accordance.

...
