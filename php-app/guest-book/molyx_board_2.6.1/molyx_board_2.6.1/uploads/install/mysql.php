<?php

$mysql_data['CREATE']['ad'] = "
CREATE TABLE $pref"."ad (
  id mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  `type` varchar(30) NOT NULL default '',
  ad_in varchar(255) NOT NULL default '',
  starttime int(10) unsigned NOT NULL default '0',
  endtime int(10) unsigned NOT NULL default '0',
  codetype tinyint(3) unsigned NOT NULL default '0',
  code mediumtext NOT NULL default '',
  htmlcode mediumtext NOT NULL default '',
  click mediumint(8) unsigned NOT NULL default '0',
  displayorder smallint(3) NOT NULL default '0',
  PRIMARY KEY  (id)
);
";

$mysql_data['CREATE']['administrator'] = "
CREATE TABLE $pref"."administrator (
  aid mediumint(8) unsigned NOT NULL default '0',
  caneditsettings tinyint(1) NOT NULL default '0',
  caneditforums tinyint(1) NOT NULL default '0',
  caneditusers tinyint(1) NOT NULL default '0',
  caneditusergroups tinyint(1) NOT NULL default '0',
  canmassprunethreads tinyint(1) NOT NULL default '0',
  canmassmovethreads tinyint(1) NOT NULL default '0',
  caneditattachments tinyint(1) NOT NULL default '0',
  caneditbbcodes tinyint(1) NOT NULL default '0',
  caneditimages tinyint(1) NOT NULL default '0',
  caneditbans tinyint(1) NOT NULL default '0',
  caneditstyles tinyint(1) NOT NULL default '0',
  caneditcaches tinyint(1) NOT NULL default '0',
  caneditcrons tinyint(1) NOT NULL default '0',
  caneditmysql tinyint(1) NOT NULL default '0',
  caneditothers tinyint(1) NOT NULL default '0',
  caneditleagues tinyint(1) NOT NULL default '0',
  caneditadmins tinyint(1) NOT NULL default '0',
  canviewadminlogs tinyint(1) NOT NULL default '0',
  canviewmodlogs tinyint(1) NOT NULL default '0',
  caneditads tinyint(1) NOT NULL default '0',
  caneditinvite tinyint(1) NOT NULL default '0',
  caneditjs tinyint(1) NOT NULL default '0',
  caneditbank tinyint(1) NOT NULL default '0',
  cansendpms tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (aid)
);
";

$mysql_data['CREATE']['adminlog'] = "
CREATE TABLE $pref"."adminlog (
  adminlogid int(10) unsigned NOT NULL auto_increment,
  userid mediumint(8) unsigned NOT NULL default '0',
  script varchar(255) NOT NULL default '',
  action varchar(255) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  note mediumtext NOT NULL default '',
  host char(15) NOT NULL default '',
  PRIMARY KEY  (adminlogid),
  KEY userid (userid)
);
";


$mysql_data['CREATE']['adminsession'] = "
CREATE TABLE $pref"."adminsession (
  sessionhash varchar(32) NOT NULL default '',
  userid mediumint(8) unsigned NOT NULL default '0',
  username varchar(32) NOT NULL default '',
  host char(15) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  location varchar(250) NOT NULL default '',
  logintime int(10) unsigned NOT NULL default '0',
  lastactivity int(10) unsigned NOT NULL default '0'
) TYPE=HEAP;
";


$mysql_data['CREATE']['announcement'] = "
CREATE TABLE $pref"."announcement (
  id int(10) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  pagetext mediumtext NOT NULL default '',
  forumid mediumtext NOT NULL default '',
  userid mediumint(8) unsigned NOT NULL default '0',
  allowhtml tinyint(1) NOT NULL default '0',
  views int(10) unsigned NOT NULL default '0',
  startdate int(10) unsigned NOT NULL default '0',
  enddate int(10) unsigned NOT NULL default '0',
  active tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (id),
  KEY active (active,enddate,startdate)
);
";


$mysql_data['CREATE']['antispam'] = "
CREATE TABLE $pref"."antispam (
  regimagehash varchar(32) NOT NULL default '',
  imagestamp varchar(8) NOT NULL default '',
  host char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (regimagehash)
);
";


$mysql_data['CREATE']['attachment'] = "
CREATE TABLE $pref"."attachment (
  attachmentid int(10) unsigned NOT NULL auto_increment,
  filename varchar(250) NOT NULL default '',
  location varchar(250) NOT NULL default '',
  thumblocation varchar(250) NOT NULL default '',
  userid mediumint(8) NOT NULL default '0',
  counter int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  pmid int(10) unsigned NOT NULL default '0',
  postid int(10) unsigned NOT NULL default '0',
  blogid int(10) unsigned NOT NULL default '0',
  posthash varchar(32) NOT NULL default '',
  visible tinyint(1) NOT NULL default '1',
  filesize int(10) unsigned NOT NULL default '0',
  thumbwidth smallint(5) unsigned NOT NULL default '0',
  thumbheight smallint(5) unsigned NOT NULL default '0',
  image tinyint(1) NOT NULL default '0',
  temp tinyint(1) NOT NULL default '0',
  extension varchar(10) NOT NULL default '',
  attachpath varchar(13) NOT NULL default '',
  inpost tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (attachmentid),
  KEY posthash (posthash),
  KEY pmid (pmid),
  KEY postid (postid),
  KEY blogid (blogid),
  KEY userid (userid,dateline)
);
";


$mysql_data['CREATE']['attachmenttype'] = "
CREATE TABLE $pref"."attachmenttype (
  id smallint(5) unsigned NOT NULL auto_increment,
  extension varchar(18) NOT NULL default '',
  mimetype varchar(255) NOT NULL default '',
  usepost tinyint(1) NOT NULL default '1',
  useavatar tinyint(1) NOT NULL default '0',
  attachimg mediumtext NOT NULL default '',
  PRIMARY KEY  (id),
  KEY useavatar (useavatar),
  KEY usepost (usepost),
  KEY extension (extension)
);
";

$mysql_data['INSERT']['attachmenttype'] = "
INSERT INTO $pref"."attachmenttype (extension, mimetype, usepost, useavatar, attachimg) VALUES
('png', 'image/png', 1, 1, 'attach/quicktime.gif'),
('gif', 'image/gif', 1, 1, 'attach/gif.gif'),
('bmp', 'image/x-ms-bmp', 1, 0, 'attach/gif.gif'),
('jpg', 'image/jpeg', 1, 1, 'attach/gif.gif'),
('jpeg', 'image/jpeg', 1, 1, 'attach/gif.gif'),
('tiff', 'image/tiff', 1, 0, 'attach/quicktime.gif'),
('ico', 'image/ico', 1, 0, 'attach/gif.gif'),
('wav', 'audio/x-wav', 1, 0, 'attach/music.gif'),
('wmv', 'video/x-msvideo', 1, 0, 'attach/win_player.gif'),
('ram', 'audio/x-pn-realaudio', 1, 0, 'attach/real_audio.gif'),
('mov', 'video/quicktime', 1, 0, 'attach/quicktime.gif'),
('mp3', 'audio/x-mpeg', 1, 0, 'attach/music.gif'),
('mpg', 'video/mpeg', 1, 0, 'attach/quicktime.gif'),
('swf', 'application/x-shockwave-flash', 0, 0, 'attach/flash.gif'),
('htm', 'application/octet-stream', 1, 0, 'attach/html.gif'),
('html', 'application/octet-stream', 1, 0, 'attach/html.gif'),
('rtf', 'text/richtext', 1, 0, 'attach/rtf.gif'),
('doc', 'application/msword', 1, 0, 'attach/doc.gif'),
('txt', 'text/plain', 1, 0, 'attach/txt.gif'),
('xml', 'text/xml', 1, 0, 'attach/script.gif'),
('php', 'application/octet-stream', 1, 0, 'attach/php.gif'),
('css', 'text/css', 1, 0, 'attach/script.gif'),
('gz', 'application/x-gzip', 1, 0, 'attach/zip.gif'),
('rar', 'application/rar', 1, 0, 'attach/zip.gif'),
('tar', 'application/x-tar', 1, 0, 'attach/zip.gif'),
('zip', 'application/zip', 1, 0, 'attach/zip.gif'),
('torrent', 'application/x-bittorrent', 1, 0, 'attach/torrent.gif')
";


$mysql_data['CREATE']['award'] = "
CREATE TABLE $pref"."award (
  id smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  explanation varchar(255) NOT NULL default '',
  img varchar(255) NOT NULL default '',
  used tinyint(1) NOT NULL default '0',
  gender tinyint(1) NOT NULL default '0',
  `date` smallint(5) unsigned NOT NULL default '0',
  onlinetime smallint(5) unsigned NOT NULL default '0',
  posts smallint(5) unsigned NOT NULL default '0',
  reputation smallint(5) unsigned NOT NULL default '0',
  strategy smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY used (used)
);
";


$mysql_data['INSERT']['award'] = "
INSERT INTO $pref"."award (name, explanation, img) VALUES
('".$a_lang['mysql']['award']['bbsbulid']."', '".$a_lang['mysql']['award']['bbsbulidexplain']."', './images/award/7.gif'),
('".$a_lang['mysql']['award']['goodlove']."', '".$a_lang['mysql']['award']['goodlovexplain']."', './images/award/12.gif'),
('".$a_lang['mysql']['award']['excellencemod']."', '".$a_lang['mysql']['award']['excellencemodepl']."', './images/award/5.gif'),
('".$a_lang['mysql']['award']['bestproduce']."', '".$a_lang['mysql']['award']['bestproducepl']."', './images/award/14.gif'),
('".$a_lang['mysql']['award']['pourgenius']."', '".$a_lang['mysql']['award']['pourgeniusepl']."', './images/award/10.gif'),
('".$a_lang['mysql']['award']['pasteimgmaster']."', '".$a_lang['mysql']['award']['pasteimgmasterepl']."', './images/award/4.gif'),
('".$a_lang['mysql']['award']['bbsencourge']."', '".$a_lang['mysql']['award']['bbsencourgepl']."', './images/award/11.gif'),
('".$a_lang['mysql']['award']['bbskavass']."', '".$a_lang['mysql']['award']['bbskavassepl']."', './images/award/2.gif'),
('".$a_lang['mysql']['award']['chatgenius']."', '".$a_lang['mysql']['award']['chatgeniusepl']."', './images/award/9.gif'),
('".$a_lang['mysql']['award']['beautyuse']."', '".$a_lang['mysql']['award']['beautyusepl']."', './images/award/6.gif'),
('".$a_lang['mysql']['award']['handsomeuse']."', '".$a_lang['mysql']['award']['handsomeusepl']."', './images/award/8.gif'),
('".$a_lang['mysql']['award']['bestflack']."', '".$a_lang['mysql']['award']['bestflackepl']."', './images/award/1.gif'),
('".$a_lang['mysql']['award']['humormaster']."', '".$a_lang['mysql']['award']['humormasterepl']."', './images/award/13.gif')
";


$mysql_data['CREATE']['award_request'] = "
CREATE TABLE $pref"."award_request (
  aid smallint(5) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  post mediumtext NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (aid, uid)
);
";


$mysql_data['CREATE']['badword'] = "
CREATE TABLE $pref"."badword (
  id smallint(5) unsigned NOT NULL auto_increment,
  badbefore varchar(250) NOT NULL default '',
  badafter varchar(250) NOT NULL default '',
  type tinyint(1) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY badbefore (badbefore, type)
);
";


$mysql_data['CREATE']['banklog'] = "
CREATE TABLE $pref"."banklog (
  id mediumint(8) unsigned NOT NULL auto_increment,
  dateline int(10) unsigned NOT NULL default '0',
  action varchar(250) NOT NULL default '',
  fromuserid mediumint(8) unsigned NOT NULL default '0',
  touserid mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (id)
);
";


$mysql_data['CREATE']['banfilter'] = "
CREATE TABLE $pref"."banfilter (
  id smallint(5) unsigned NOT NULL auto_increment,
  type varchar(10) NOT NULL default 'ip',
  content mediumtext NOT NULL default '',
  PRIMARY KEY (id),
  KEY type (type)
);
";

$mysql_data['INSERT']['banfilter'] = "
INSERT INTO $pref"."banfilter (type, content) VALUES
('title', '".$a_lang['mysql']['banfilter']['admin']."'),
('title', '".$a_lang['mysql']['banfilter']['mod']."')
";


$mysql_data['CREATE']['bbcode'] = "
CREATE TABLE $pref"."bbcode (
  bbcodeid smallint(5) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  description mediumtext NOT NULL default '',
  bbcodetag varchar(255) NOT NULL default '',
  bbcodereplacement mediumtext NOT NULL default '',
  twoparams tinyint(1) NOT NULL default '0',
  bbcodeexample mediumtext NOT NULL default '',
  imagebutton tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (bbcodeid),
  KEY imagebutton (imagebutton)
);
";

$mysql_data['INSERT']['bbcode'] = "
INSERT INTO $pref"."bbcode (title, description, bbcodetag, bbcodereplacement, twoparams, bbcodeexample, imagebutton) VALUES
('[THREAD]', '".$a_lang['mysql']['bbcode']['tagforthread']."', 'thread', '<a href=\'showthread.php?t={option}\'>{content}</a>', 1, '[thread=1]".$a_lang['mysql']['bbcode']['viewthread']."[/thread]', '0'),
('[POST]', '".$a_lang['mysql']['bbcode']['tagforpost']."', 'post', '<a href=\'redirect.php?goto=findpost&p={option}\'>{content}</a>', 1, '[post=1]".$a_lang['mysql']['bbcode']['viewpost']."[/post]', '0'),
('[MOVIE]', '[movie] ".$a_lang['mysql']['bbcode']['tagforallmovie']."', 'movie', '<div align=\'center\'><object id=\'player\' width=\'400\' height=\'300\' classid=\'clsid:6bf52a52-394a-11d3-b153-00c04f79faa6\' codebase=\'http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#version=6,4,5,715\' standby=\'Loading Microsoft Windows Media player Components...\' type=\'application/x-oleobject\' align=\'center\'><param name=\'url\' value=\'{content}\' /><param name=\'uimode\' value=\'full\' /><param name=\'autostart\' value=\'0\' /><param name=\'transparentatstart\' value=\'1\' /><param name=\'animationatstart\' value=\'1\' /><param name=\'showcontrols\' value=\'1\' /><param name=\'showstatusbar\' value=\'1\' /><embed type=\'application/x-mplayer2\' pluginspage=\'http://www.microsoft.com/windows/downloads/contents/products/mediaplayer/\' src=\'{content}\' align=\'middle\' width=\'400\' height=\'300\' showcontrols=\'1\' showstatusbar=\'1\' autostart=\'0\' showdisplay=\'1\' showstatusbar=\'0\'></embed></object><br /><a href=\'{content}\' target=\'_blank\'>".$a_lang['mysql']['bbcode']['clickdown']."</a></div>', 0, '[movie]http://website/movie.wmv[/movie]', '1'),
('[REAL]', '[real] ".$a_lang['mysql']['bbcode']['tagforallrmmovie']."', 'real', '<div align=\'center\'><object id=\'{content}\' classid=\'clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa\' height=\'300\' width=\'400\'><param name=\'controls\' value=\'imagewindow\' /><param name=\'nologo\' value=\'1\' /><param name=\'console\' value=\'{content}\' /><param name=\'autostart\' value=\'0\' /><embed type=\'audio/x-pn-realaudio-plugin\' console=\'clip1\' controls=\'imagewindow\' height=\'300\' width=\'400\' nologo=\'true\' autostart=\'0\' /></embed></object><br /><object id=\'{content}\' classid=\'clsid:cfcdaa03-8be4-11cf-b84b-0020afbbccfa\' width=\'400\' height=\'50\'><param name=\'controls\' value=\'controlpanel,statusbar\' /><param name=\'console\' value=\'{content}\' /><param name=\'autostart\' value=\'0\' /><param name=\'src\' value=\'{content}\' /><embed src=\'{content}\' type=\'audio/x-pn-realaudio-plugin\' console=\'clip1\' controls=\'controlpanel\' width=\'400\' height=\'50\' autostart=\'0\' nojava=\'true\'></embed></object><br /><a href=\'{content}\' target=\'_blank\'>".$a_lang['mysql']['bbcode']['clickdown']."</a></div>', 0, '[real]http://website/movie.rm[/real]', '1'),
('[MUSIC]', '[music] ".$a_lang['mysql']['bbcode']['tagforallmusic']."', 'music', '<div align=\'center\'><object id=\'player\' width=\'400\' height=\'66\' classid=\'clsid:6bf52a52-394a-11d3-b153-00c04f79faa6\' codebase=\'http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#version=6,4,5,715\' standby=\'Loading Microsoft Windows Media player Components...\' type=\'application/x-oleobject\' align=\'center\'><param name=\'url\' value=\'{content}\' /><param name=\'uimode\' value=\'mini\' /><param name=\'autostart\' value=\'0\' /><param name=\'transparentatstart\' value=\'1\' /><param name=\'showdisplay\' value=\'0\' /><param name=\'showtracker\' value=\'1\' /><param name=\'animationatstart\' value=\'1\' /><param name=\'showcaptioning\' value=\'0\' /><param name=\'allowchangedisplaysize\' value=\'0\' /><param name=\'showcontrols\' value=\'1\' /><param name=\'showstatusbar\' value=\'1\' /><embed type=\'application/x-mplayer2\' pluginspage=\'http://www.microsoft.com/windows/downloads/contents/products/mediaplayer/\' src=\'{content}\' align=\'middle\' width=\'400\' height=\'66\' showcontrols=\'1\' showstatusbar=\'1\' showdisplay=\'0\' showstatusbar=\'0\'></embed></object><br /><a href=\'{content}\' target=\'_blank\'>".$a_lang['mysql']['bbcode']['clickdown']."</a></div>', 0, '[music]http://website/flash.wav[/music]', '1')
";

$mysql_data['CREATE']['birthday'] = "
CREATE TABLE $pref"."birthday (
  id MEDIUMINT(8) unsigned NOT NULL default '0',
  dateline INT(10) unsigned NOT NULL default '0',
  PRIMARY KEY (id)
);
";


$mysql_data['CREATE']['cache'] = "
CREATE TABLE $pref"."cache (
  title varchar(255) NOT NULL default '',
  data mediumtext NOT NULL default '',
  `is_array` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (title)
);
";


$mysql_data['CREATE']['credit'] = "
CREATE TABLE $pref"."credit (
  creditid mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(40) NOT NULL default '',
  tag_name varchar(40) NOT NULL default '',
  newthread smallint(3) NOT NULL default '0',
  newreply smallint(3) NOT NULL default '0',
  quintessence smallint(3) NOT NULL default '0',
  award smallint(3) NOT NULL default '0',
  downattach smallint(3) NOT NULL default '0',
  sendpm smallint(3) NOT NULL default '0',
  search smallint(3) NOT NULL default '0',
  c_limit smallint(3) NOT NULL default '0',
  used tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (creditid)
);
";


$mysql_data['CREATE']['cron'] = "
CREATE TABLE $pref"."cron (
  cronid int(10) NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  filename varchar(255) NOT NULL default '',
  nextrun int(10) unsigned NOT NULL default '0',
  weekday tinyint(1) NOT NULL default '-1',
  monthday tinyint(2) NOT NULL default '-1',
  hour tinyint(2) NOT NULL default '-1',
  minute smallint(2) NOT NULL default '-1',
  cronhash varchar(32) NOT NULL default '',
  loglevel tinyint(1) NOT NULL default '0',
  description mediumtext NOT NULL default '',
  enabled tinyint(1) NOT NULL default '1',
  PRIMARY KEY (cronid),
  KEY enabled (enabled, nextrun)
);
";

$mysql_data['INSERT']['cron'] = "
INSERT INTO $pref"."cron (title, filename, nextrun, weekday, monthday, hour, minute, cronhash, loglevel, description, enabled) VALUES
('".$a_lang['mysql']['cron']['cleanout']."', 'cleanout.php', 0, -1, -1, 1, -1, '', 1, '".$a_lang['mysql']['cron']['cleanoutdesc']."', 1),
('".$a_lang['mysql']['cron']['rebuildstats']."', 'rebuildstats.php', 0, -1, -1, 1, 0, '', 1, '".$a_lang['mysql']['cron']['rebuildstatsdesc']."', 1),
('".$a_lang['mysql']['cron']['dailycleanout']."', 'dailycleanout.php', 0, -1, -1, 3, 0, '', 1, '".$a_lang['mysql']['cron']['dailycleanoutdesc']."', 1),
('".$a_lang['mysql']['cron']['birthdays']."', 'birthdays.php', 0, -1, -1, 0, 0, '', 1, '".$a_lang['mysql']['cron']['birthdaysdesc']."', 1),
('".$a_lang['mysql']['cron']['announcements']."', 'announcements.php', 0, -1, -1, 2, 0, '', 1, '".$a_lang['mysql']['cron']['announcementsdesc']."', 1),
('".$a_lang['mysql']['cron']['renameupload']."', 'renameupload.php', 0, -1, -1, 2, -1, '', 1, '".$a_lang['mysql']['cron']['renameuploaddesc']."', 0),
('".$a_lang['mysql']['cron']['promotion']."', 'promotion.php', 0, -1, -1, 1, -1, '', 1, '".$a_lang['mysql']['cron']['promotiondesc']."', 1),
('".$a_lang['mysql']['cron']['bankloancheck']."', 'bankloancheck.php', 0, -1, -1, 4, -1, '', 1, '".$a_lang['mysql']['cron']['bankloancheckdesc']."', 1),
('".$a_lang['mysql']['cron']['bankpayinterest']."', 'bankpayinterest.php', 0, -1, -1, 5, 0, '', 1, '".$a_lang['mysql']['cron']['bankpayinterestdesc']."', 1),
('".$a_lang['mysql']['cron']['award_promotion']."', 'award_promotion.php', 0, -1, -1, 12, -1, '', 1, '".$a_lang['mysql']['cron']['award_promotiondesc']."', 1),
('".$a_lang['mysql']['cron']['rebuildglobalstick']."', 'rebuildglobalstick.php', 0, -1, -1, 1, -1, '', 1, '".$a_lang['mysql']['cron']['rebuildglobalstickdesc']."', 1),
('".$a_lang['mysql']['cron']['cleantoday']."', 'cleantoday.php', 0, -1, -1, 0, 0, '', 0, '".$a_lang['mysql']['cron']['cleantodaydesc']."', 1),
('".$a_lang['mysql']['cron']['refreshjs']."', 'refreshjs.php', 0, -1, -1, 0, 0, '', 0, '".$a_lang['mysql']['cron']['refreshjsdesc']."', 1),
('".$a_lang['mysql']['cron']['threadviews']."', 'threadviews.php', 0, -1, -1, 1, -1, '', 1, '".$a_lang['mysql']['cron']['threadviewsdesc']."', 1),
('".$a_lang['mysql']['cron']['attachmentviews']."', 'attachmentviews.php', 0, -1, -1, 1, -1, '', 1, '".$a_lang['mysql']['cron']['attachmentviewsdesc']."', 1)
";


$mysql_data['CREATE']['cronlog'] = "
CREATE TABLE $pref"."cronlog (
  cronlogid int(10) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  description mediumtext NOT NULL default '',
  PRIMARY KEY  (cronlogid),
  KEY title (title)
);
";


$mysql_data['CREATE']['faq'] = "
CREATE TABLE $pref"."faq (
  id mediumint(8) NOT NULL auto_increment,
  title varchar(128) NOT NULL default '',
  text mediumtext NOT NULL default '',
  description mediumtext NOT NULL default '',
  parentid mediumint(8) NOT NULL default '0',
  displayorder mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY parentid (parentid)
)
";

$mysql_data['INSERT']['faq'] = "
INSERT INTO $pref"."faq (id, title, text, description, parentid, displayorder) VALUES
('1','".$a_lang['mysql']['faq']['faqtitle_1']."','','".$a_lang['mysql']['faq']['faqdesc_1']."','0','0'),
('2','".$a_lang['mysql']['faq']['faqtitle_2']."','','','0','1'),
('3','".$a_lang['mysql']['faq']['faqtitle_3']."','','','0','3'),
('4','".$a_lang['mysql']['faq']['faqtitle_4']."','".$a_lang['mysql']['faq']['faqtext_4']."','','1','0'),
('5','".$a_lang['mysql']['faq']['faqtitle_5']."','".$a_lang['mysql']['faq']['faqtext_5']."','','1','2'),
('6','".$a_lang['mysql']['faq']['faqtitle_6']."','".$a_lang['mysql']['faq']['faqtext_6']."','','1','6'),
('7','".$a_lang['mysql']['faq']['faqtitle_7']."','".$a_lang['mysql']['faq']['faqtext_7']."','','1','3'),
('8','".$a_lang['mysql']['faq']['faqtitle_8']."','".$a_lang['mysql']['faq']['faqtext_8']."','','1','4'),
('9','".$a_lang['mysql']['faq']['faqtitle_9']."','".$a_lang['mysql']['faq']['faqtext_9']."','','1','5'),
('10','".$a_lang['mysql']['faq']['faqtitle_10']."','".$a_lang['mysql']['faq']['faqtext_10']."','','1','7'),
('11','".$a_lang['mysql']['faq']['faqtitle_11']."','".$a_lang['mysql']['faq']['faqtext_11']."','','1','8'),
('12','".$a_lang['mysql']['faq']['faqtitle_12']."','".$a_lang['mysql']['faq']['faqtext_12']."','','1','9'),
('13','".$a_lang['mysql']['faq']['faqtitle_13']."','".$a_lang['mysql']['faq']['faqtext_13']."','','2','1'),
('14','".$a_lang['mysql']['faq']['faqtitle_14']."','".$a_lang['mysql']['faq']['faqtext_14']."','','2','2'),
('15','".$a_lang['mysql']['faq']['faqtitle_15']."','".$a_lang['mysql']['faq']['faqtext_15']."','','2','3'),
('16','".$a_lang['mysql']['faq']['faqtitle_16']."','".$a_lang['mysql']['faq']['faqtext_16']."','','2','4'),
('17','".$a_lang['mysql']['faq']['faqtitle_17']."','".$a_lang['mysql']['faq']['faqtext_17']."','','2','5'),
('18','".$a_lang['mysql']['faq']['faqtitle_18']."','".$a_lang['mysql']['faq']['faqtext_18']."','','3','1'),
('19','".$a_lang['mysql']['faq']['faqtitle_19']."','".$a_lang['mysql']['faq']['faqtext_19']."','','3','2'),
('20','".$a_lang['mysql']['faq']['faqtitle_20']."','".$a_lang['mysql']['faq']['faqtext_20']."','','3','3'),
('21','".$a_lang['mysql']['faq']['faqtitle_21']."','".$a_lang['mysql']['faq']['faqtext_21']."','','3','4'),
('22','".$a_lang['mysql']['faq']['faqtitle_22']."','".$a_lang['mysql']['faq']['faqtext_22']."','','3','5'),
('23','".$a_lang['mysql']['faq']['faqtitle_23']."','".$a_lang['mysql']['faq']['faqtext_23']."','','3','6'),
('24','".$a_lang['mysql']['faq']['faqtitle_24']."','".$a_lang['mysql']['faq']['faqtext_24']."','','3','7'),
('25','".$a_lang['mysql']['faq']['faqtitle_25']."','".$a_lang['mysql']['faq']['faqtext_25']."','','3','8'),
('26','".$a_lang['mysql']['faq']['faqtitle_26']."','','','0','4'),
('27','".$a_lang['mysql']['faq']['faqtitle_27']."','".$a_lang['mysql']['faq']['faqtext_27']."','','26','2'),
('28','".$a_lang['mysql']['faq']['faqtitle_28']."','".$a_lang['mysql']['faq']['faqtext_28']."','','26','2'),
('29','".$a_lang['mysql']['faq']['faqtitle_29']."','".$a_lang['mysql']['faq']['faqtext_29']."','','26','3')
";



$mysql_data['CREATE']['forum'] = "
CREATE TABLE $pref"."forum (
  id smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(128) NOT NULL default '',
  description mediumtext NOT NULL default '',
  forumicon varchar(255) NOT NULL default '',
  thread mediumint(6) unsigned NOT NULL default '0',
  post mediumint(6) unsigned NOT NULL default '0',
  todaypost mediumint(3) unsigned NOT NULL default '0',
  style smallint(5) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  lastposterid mediumint(8) unsigned NOT NULL default '0',
  lastposter varchar(32) NOT NULL default '',
  allowbbcode tinyint(1) NOT NULL default '1',
  allowhtml tinyint(1) NOT NULL default '0',
  status tinyint(1) NOT NULL default '1',
  password varchar(32) NOT NULL default '',
  lastthread varchar(128) NOT NULL default '',
  lastthreadid int(10) unsigned NOT NULL default '0',
  sortby varchar(32) NOT NULL default '',
  sortorder varchar(32) NOT NULL default '',
  prune tinyint(3) NOT NULL default '100',
  moderatepost tinyint(1) NOT NULL default '0',
  allowpoll tinyint(1) NOT NULL default '1',
  allowpollup tinyint(1) NOT NULL default '0',
  countposts tinyint(1) NOT NULL default '1',
  parentid mediumint(5) NOT NULL default '-1',
  parentlist varchar(250) NOT NULL default '',
  childlist varchar(250) NOT NULL default '',
  allowposting tinyint(1) default '1',
  customerror mediumtext NOT NULL default '',
  permissions mediumtext NOT NULL default '',
  showthreadlist tinyint(1) NOT NULL default '0',
  unmodthreads mediumint(6) NOT NULL default '0',
  unmodposts mediumint(6) NOT NULL default '0',
  displayorder tinyint(3) NOT NULL default '0',
  forumcolumns tinyint(1) unsigned NOT NULL default '0',
  threadprefix varchar(255) NOT NULL default '',
  paypoints varchar(10) NOT NULL default '',
  forcespecial tinyint(1) unsigned NOT NULL default '0',
  specialtopic varchar(255) NOT NULL default '',
  forumrule tinyint(1) NOT NULL default '0',
  url varchar(255) NOT NULL default '',
  PRIMARY KEY (id),
  KEY parentid (parentid)
);
";

$mysql_data['INSERT']['forum'] = "
INSERT INTO $pref"."forum (id, name, description, forumicon, thread, post, lastpost, lastposterid, lastposter, allowbbcode, allowhtml, status, password, lastthread, lastthreadid, sortby, sortorder, prune, moderatepost, allowpoll, allowpollup, countposts, parentid, parentlist, childlist, allowposting, customerror, permissions, showthreadlist, unmodthreads, unmodposts, displayorder, forumcolumns, threadprefix) VALUES
(1, '".$a_lang['mysql']['forum']['testsort']."', '', '', 0, 0, 0, 0, '', 0, 0, 0, '', '', 0, 'lastpost', 'desc', 100, 0, 1, 0, 1, -1, '1,-1', '1,2', 0, '', 'a:5:{s:8:\\\"canstart\\\";s:7:\\\"4,3,7,6\\\";s:8:\\\"canreply\\\";s:7:\\\"4,3,7,6\\\";s:7:\\\"canread\\\";s:1:\\\"*\\\";s:9:\\\"canupload\\\";s:7:\\\"4,3,7,6\\\";s:7:\\\"canshow\\\";s:1:\\\"*\\\";}', 0, 0, 0, 1, 0, ''),
(2, '".$a_lang['mysql']['forum']['testforum']."', '".$a_lang['mysql']['forum']['testdesc']."', '', 0, 0, 0, 0, '', 1, 0, 1, '', '', 0, 'lastpost', 'desc', 100, 0, 1, 0, 1, 1, '2,1,-1', '2', 1, '', 'a:5:{s:8:\\\"canstart\\\";s:7:\\\"4,3,7,6\\\";s:8:\\\"canreply\\\";s:7:\\\"4,3,7,6\\\";s:7:\\\"canread\\\";s:1:\\\"*\\\";s:9:\\\"canupload\\\";s:7:\\\"4,3,7,6\\\";s:7:\\\"canshow\\\";s:1:\\\"*\\\";}', 0, 0, 0, 2, 0, '')
";


$mysql_data['CREATE']['icon'] = "
CREATE TABLE $pref"."icon (
  id smallint(5) unsigned NOT NULL auto_increment,
  icontext varchar(32) NOT NULL default '',
  image varchar(128) NOT NULL default '',
  displayorder smallint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
);
";

$mysql_data['INSERT']['icon'] = "
INSERT INTO $pref"."icon (icontext, image, displayorder) VALUES
(':question:', 'question.gif', 0),
(':post:', 'post.gif', 0),
(':photo:', 'photo.gif', 0),
(':music:', 'music.gif', 0),
(':good:', 'good.gif', 0),
(':go:', 'go.gif', 0),
(':bad:', 'bad.gif', 0),
(':attention:', 'attention.gif', 0),
(':surprise:', 'surprise.gif', 0),
(':warter:', 'warter.gif', 0)
";

$mysql_data['CREATE']['inviteduser'] = "
CREATE TABLE $pref"."inviteduser (
  invitedid int(8) unsigned NOT NULL auto_increment,
  userid mediumint(8) unsigned NOT NULL default '0',
  email varchar(60) NOT NULL default '',
  sendtime int(10) unsigned NOT NULL default '0',
  expiry int(5) NOT NULL default '0',
  regsterid mediumint(8) unsigned NOT NULL default '0',
  validatecode varchar(10) NOT NULL default '',
  PRIMARY KEY  (invitedid),
  KEY email (email)
);
";

$mysql_data['CREATE']['javascript'] = "
CREATE TABLE $pref"."javascript (
  id smallint(6) NOT NULL auto_increment,
  `name` varchar(250) NOT NULL default '',
  description mediumtext NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  jsname varchar(250) NOT NULL default '',
  nextrun int(10) NOT NULL default '0',
  inids varchar(250) NOT NULL default '',
  numbers smallint(3) NOT NULL default '0',
  perline tinyint(1) NOT NULL default '0',
  selecttype varchar(20) NOT NULL default '',
  daylimit tinyint(1) NOT NULL default '0',
  orderby tinyint(1) NOT NULL default '0',
  trimtitle smallint(5) NOT NULL default '0',
  trimdescription smallint(5) NOT NULL default '0',
  trimpagetext smallint(5) NOT NULL default '-1',
  refresh smallint(5) unsigned NOT NULL default '0',
  export tinyint(1) NOT NULL default '0',
  htmlcode mediumtext NOT NULL default '',
  PRIMARY KEY  (id)
);
";

$mysql_data['CREATE']['league'] = "
CREATE TABLE $pref"."league (
  leagueid smallint(5) unsigned NOT NULL auto_increment,
  sitename varchar(250) NOT NULL default '',
  siteurl varchar(255) NOT NULL default '',
  siteimage varchar(250) NOT NULL default '',
  siteinfo mediumtext NOT NULL default '',
  displayorder smallint(3) unsigned NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (leagueid),
  KEY type (type)
);
";

$mysql_data['INSERT']['league'] = "
INSERT INTO $pref"."league (sitename, siteurl, siteimage, siteinfo, displayorder, type) VALUES
('".$a_lang['mysql']['league']['hogesoftitle']."', 'http://www.hogesoft.com', './images/league/hogesoft.gif', '".$a_lang['mysql']['league']['hogesofdesc']."', 1, 0),
('".$a_lang['mysql']['league']['molyxteam']."', 'http://www.molyx.com', './images/league/molyx_logo.gif', '".$a_lang['mysql']['league']['molyxdesc']."', 2, 0),
('W3C DHTML Valid!', 'http://validator.w3.org/check?uri=referer', 'http://www.w3.org/Icons/valid-xhtml10', 'Valid XHTML 1.0 Transitional', 4, 1),
('W3C CSS Valid!', 'http://jigsaw.w3.org/css-validator/', 'http://www.w3.org/Icons/valid-css', 'Valid CSS!', 5, 1)
";


$mysql_data['CREATE']['moderator'] = "
CREATE TABLE $pref"."moderator (
  moderatorid smallint(5) NOT NULL auto_increment,
  forumid smallint(5) unsigned NOT NULL default '0',
  userid mediumint(8) NOT NULL default '0',
  username varchar(32) NOT NULL default '',
  usergroupid smallint(3) unsigned NOT NULL default '0',
  usergroupname varchar(200) default NULL,
  isgroup tinyint(1) NOT NULL default '0',
  caneditposts tinyint(1) NOT NULL default '0',
  caneditthreads tinyint(1) NOT NULL default '0',
  candeleteposts tinyint(1) NOT NULL default '0',
  candeletethreads tinyint(1) NOT NULL default '0',
  canviewips tinyint(1) NOT NULL default '0',
  canopenclose tinyint(1) NOT NULL default '0',
  canremoveposts tinyint(1) NOT NULL default '0',
  canstickthread tinyint(1) NOT NULL default '0',
  canmoderateposts tinyint(1) NOT NULL default '0',
  canmanagethreads tinyint(1) NOT NULL default '0',
  caneditusers tinyint(1) NOT NULL default '0',
  cansplitthreads tinyint(1) NOT NULL default '0',
  canmergethreads tinyint(1) NOT NULL default '0',
  caneditrule tinyint(1) NOT NULL default '0',
  cansetst tinyint(1) NOT NULL,
  PRIMARY KEY  (moderatorid),
  KEY forumid (forumid, userid)
);
";


$mysql_data['CREATE']['moderatorlog'] = "
CREATE TABLE $pref"."moderatorlog (
  moderatorlogid int(10) unsigned NOT NULL auto_increment,
  forumid smallint(5) unsigned NOT NULL default '0',
  threadid int(10) unsigned NOT NULL default '0',
  postid int(10) unsigned NOT NULL default '0',
  userid mediumint(8) unsigned NOT NULL default '0',
  username varchar(32) NOT NULL default '',
  host char(15) NOT NULL default '',
  referer varchar(255) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  title varchar(128) NOT NULL default '',
  action varchar(128) NOT NULL default '',
  PRIMARY KEY  (moderatorlogid),
  KEY userid (userid)
);
";


$mysql_data['CREATE']['pm'] = "
CREATE TABLE $pref"."pm (
  pmid int(10) unsigned NOT NULL auto_increment,
  messageid int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  title varchar(255) NOT NULL default '',
  fromuserid mediumint(8) NOT NULL default '0',
  touserid mediumint(8) NOT NULL default '0',
  folderid smallint(3) NOT NULL default '0',
  pmread tinyint(1) NOT NULL default '0',
  attach int(10) unsigned NOT NULL default '0',
  tracking tinyint(1) default '0',
  userid mediumint(8) unsigned NOT NULL default '0',
  usergroupid varchar(255) NOT NULL default '',
  pmreadtime int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (pmid),
  KEY fromuserid (fromuserid,tracking),
  KEY usergroupid (usergroupid)
);
";


$mysql_data['CREATE']['pmtext'] = "
CREATE TABLE $pref"."pmtext (
  pmtextid int(10) unsigned NOT NULL auto_increment,
  dateline int(10) unsigned NOT NULL default '0',
  message mediumtext NOT NULL default '',
  savedcount smallint(5) NOT NULL default '0',
  deletedcount smallint(5) NOT NULL default '0',
  posthash varchar(32) NOT NULL default '0',
  fromuserid mediumint(8) NOT NULL default '0',
  PRIMARY KEY  (pmtextid),
  KEY dateline (dateline),
  KEY deletedcount (deletedcount)
);
";


$mysql_data['CREATE']['pmuserlist'] = "
CREATE TABLE $pref"."pmuserlist (
  id mediumint(8) unsigned NOT NULL auto_increment,
  contactid mediumint(8) unsigned NOT NULL default '0',
  userid mediumint(8) unsigned NOT NULL default '0',
  contactname varchar(32) NOT NULL default '',
  allowpm tinyint(1) NOT NULL default '0',
  description varchar(50) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY userid (userid,contactid)
);
";


$mysql_data['CREATE']['poll'] = "
CREATE TABLE $pref"."poll (
  pollid mediumint(8) unsigned NOT NULL auto_increment,
  tid int(10) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  options mediumtext NOT NULL default '',
  votes smallint(5) NOT NULL default '0',
  forumid smallint(5) NOT NULL default '0',
  question varchar(255) NOT NULL default '',
  voters mediumtext NOT NULL default '',
  multipoll tinyint(1) NOT NULL default '0',
  PRIMARY KEY (pollid),
  KEY tid (tid)
);
";


$mysql_data['CREATE']['post'] = "
CREATE TABLE $pref"."post (
  pid int(10) unsigned NOT NULL auto_increment,
  pagetext mediumtext NOT NULL default '',
  userid mediumint(8) unsigned NOT NULL default '0',
  username varchar(32) NOT NULL default '',
  showsignature tinyint(1) NOT NULL default '0',
  allowsmile tinyint(1) NOT NULL default '0',
  host char(15) NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  iconid smallint(5) unsigned NOT NULL default '0',
  moderate tinyint(1) NOT NULL default '0',
  threadid int(10) unsigned NOT NULL default '0',
  newthread tinyint(1) NOT NULL default '0',
  posthash varchar(32) NOT NULL default '',
  anonymous tinyint(1) NOT NULL default '0',
  reppost varchar(100) NOT NULL default '',
  cashpost varchar(100) NOT NULL default '',
  hidepost mediumtext NOT NULL default '',
  PRIMARY KEY  (pid),
  KEY userid (userid),
  KEY threadid (threadid),
  KEY dateline (dateline)
);
";


$mysql_data['CREATE']['search'] = "
CREATE TABLE $pref"."search (
  searchid varchar(32) NOT NULL default '',
  userid mediumint(8) unsigned default '0',
  threadid mediumtext NOT NULL default '',
  dateline int(10) unsigned NOT NULL default '0',
  maxpost int(10) unsigned NOT NULL default '0',
  maxthread int(3) unsigned NOT NULL default '0',
  sortby varchar(32) NOT NULL default 'lastpost',
  sortorder varchar(4) NOT NULL default 'desc',
  host char(15) NOT NULL default '',
  postid mediumtext NOT NULL default '',
  query mediumtext NOT NULL default '',
  PRIMARY KEY (searchid),
  KEY dateline (dateline),
  KEY userid (userid)
);
";


$mysql_data['CREATE']['session'] = "
CREATE TABLE $pref"."session (
  sessionhash varchar(32) NOT NULL default '0',
  username varchar(32) NOT NULL default '',
  userid mediumint(8) unsigned NOT NULL default '0',
  host char(15) NOT NULL default '',
  useragent varchar(255) NOT NULL default '',
  lastactivity int(10) unsigned NOT NULL default '0',
  invisible tinyint(1) NOT NULL default '0',
  location varchar(250) NOT NULL default '',
  usergroupid smallint(3) unsigned NOT NULL default '0',
  inforum smallint(5) unsigned NOT NULL default '0',
  inthread int(10) unsigned NOT NULL default '0',
  inblog int(10) unsigned NOT NULL default '0',
  mobile tinyint(1) NOT NULL default '0',
  badlocation tinyint(1) NOT NULL default '0'
) TYPE=HEAP;
";

$mysql_data['CREATE']['setting'] = "
CREATE TABLE $pref"."setting (
  settingid int(10) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  description mediumtext NOT NULL default '',
  groupid smallint(5) NOT NULL default '0',
  type varchar(255) NOT NULL default '',
  varname varchar(255) NOT NULL default '',
  value mediumtext NOT NULL default '',
  defaultvalue mediumtext NOT NULL default '',
  dropextra mediumtext NOT NULL default '',
  displayorder smallint(3) unsigned NOT NULL default '0',
  addcache tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (settingid),
  KEY groupid ( groupid )
);
";

$mysql_data['INSERT']['setting'] = "
INSERT INTO $pref"."setting (title, description, groupid, type, varname, value, defaultvalue, dropextra, displayorder, addcache) VALUES
('".$a_lang['mysql']['setting']['uploadurl']."','".$a_lang['mysql']['setting']['uploadurldesc']."','1','input','uploadurl','','','','1','1'),
('".$a_lang['mysql']['setting']['uploadfolder']."','".$a_lang['mysql']['setting']['uploadfolderdesc']."','1','input','uploadfolder','','','uploadfolder','2','1'),
('".$a_lang['mysql']['setting']['remoteattach']."','".$a_lang['mysql']['setting']['remoteattachdesc']."','1','input','remoteattach','','','','3','1'),
('".$a_lang['mysql']['setting']['headerredirect']."','".$a_lang['mysql']['setting']['headerredirectdesc']."','1','dropdown','headerredirect','','location','".$a_lang['mysql']['setting']['headerredirectextra']."','4','1'),
('".$a_lang['mysql']['setting']['removeredirect']."','".$a_lang['mysql']['setting']['removeredirectdesc']."','1','yes_no','removeredirect','','0','','5','1'),
('".$a_lang['mysql']['setting']['numberformat']."','".$a_lang['mysql']['setting']['numberformatdesc']."','1','dropdown','numberformat','',',','".$a_lang['mysql']['setting']['numberformatextra']."','6','1'),
('".$a_lang['mysql']['setting']['gdversion']."','".$a_lang['mysql']['setting']['gdversiondesc']."','1','dropdown','gdversion','','2','1=GD1\n2=GD2','7','1'),
('".$a_lang['mysql']['setting']['isajax']."','".$a_lang['mysql']['setting']['isajaxdesc']."','1','yes_no','isajax','','1','','8','1'),
('".$a_lang['mysql']['setting']['default_lang']."','".$a_lang['mysql']['setting']['default_langdesc']."','1','dropdown','default_lang','','".$_POST['lang']."','#show_lang#','9','1'),
('".$a_lang['mysql']['setting']['showtoday']."','".$a_lang['mysql']['setting']['showtodaydesc']."','1','yes_no','showtoday','','1','','10','1'),
('".$a_lang['mysql']['setting']['miibeian']."','".$a_lang['mysql']['setting']['miibeiandesc']."','1','input','miibeian','','','','11','1'),
('".$a_lang['mysql']['setting']['cookietimeout']."','','2','input','cookietimeout','','15','','1','1'),
('".$a_lang['mysql']['setting']['loadlimit']."','".$a_lang['mysql']['setting']['loadlimitdesc']."','2','input','loadlimit','','','','2','1'),
('".$a_lang['mysql']['setting']['threadviewsdelay']."', '".$a_lang['mysql']['setting']['threadviewsdelaydesc']."', 2, 'yes_no', 'threadviewsdelay', '', '0', '', 3, 1),
('".$a_lang['mysql']['setting']['attachmentviewsdelay']."', '".$a_lang['mysql']['setting']['attachmentviewsdelaydesc']."', 2, 'yes_no', 'attachmentviewsdelay', '', '0', '', 4, 1),
('".$a_lang['mysql']['setting']['bbtitle']."','".$a_lang['mysql']['setting']['bbtitledesc']."','3','input','bbtitle','','MolyX BOARD','','1','1'),
('".$a_lang['mysql']['setting']['bburl']."','".$a_lang['mysql']['setting']['bburldesc']."','3','input','bburl','','http://localhost/_1','','2','1'),
('".$a_lang['mysql']['setting']['hometitle']."','".$a_lang['mysql']['setting']['hometitledesc']."','3','input','hometitle','','','','3','1'),
('".$a_lang['mysql']['setting']['homeurl']."','".$a_lang['mysql']['setting']['homeurldesc']."','3','input','homeurl','','http://localhost','','4','1'),
('".$a_lang['mysql']['setting']['adminurl']."','".$a_lang['mysql']['setting']['adminurldesc']."','3','input','adminurl','','admin','','5','1'),
('".$a_lang['mysql']['setting']['redirecturl']."','".$a_lang['mysql']['setting']['redirecturldesc']."','3','dropdown','redirecturl','','1','".$a_lang['mysql']['setting']['redirecturlextra']."','6','1'),
('".$a_lang['mysql']['setting']['cookiedomain']."','".$a_lang['mysql']['setting']['cookiedomaindesc']."','4','input','cookiedomain','','','','1','1'),
('".$a_lang['mysql']['setting']['cookieprefix']."','".$a_lang['mysql']['setting']['cookieprefixdesc']."','4','input','cookieprefix','','','','2','1'),
('".$a_lang['mysql']['setting']['cookiepath']."','".$a_lang['mysql']['setting']['cookiepathdesc']."','4','input','cookiepath','','','','3','1'),
('".$a_lang['mysql']['setting']['gzipoutput']."','".$a_lang['mysql']['setting']['gzipoutputdesc']."','4','yes_no','gzipoutput','','1','','4','1'),
('".$a_lang['mysql']['setting']['timezoneoffset']."','".$a_lang['mysql']['setting']['timezoneoffsetdesc']."','5','dropdown','timezoneoffset','','8','','1','1'),
('".$a_lang['mysql']['setting']['timeadjust']."','".$a_lang['mysql']['setting']['timeadjustdesc']."','5','input','timeadjust','','0','','2','1'),
('".$a_lang['mysql']['setting']['standardtimeformat']."','".$a_lang['mysql']['setting']['standardtimeformatdesc']."','5','input','standardtimeformat','','Y-m-d h:i A','','3','1'),
('".$a_lang['mysql']['setting']['longtimeformat']."','".$a_lang['mysql']['setting']['longtimeformatdesc']."','5','input','longtimeformat','','Y-m-d H:i','','4','1'),
('".$a_lang['mysql']['setting']['registereddateformat']."','".$a_lang['mysql']['setting']['registereddateformatdesc']."','5','input','registereddateformat','','Y-m-d','','5','1'),
('".$a_lang['mysql']['setting']['allowselectstyles']."','','6','yes_no','allowselectstyles','','1','','1','1'),
('".$a_lang['mysql']['setting']['usernameminlength']."', '".$a_lang['mysql']['setting']['usernameminlengthdesc']."', 6, 'input', 'usernameminlength', '', '2', '', '2', '1'),
('".$a_lang['mysql']['setting']['usernamemaxlength']."', '".$a_lang['mysql']['setting']['usernamemaxlengthdesc']."', 6, 'input', 'usernamemaxlength', '', '10', '', '3', '1'),
('".$a_lang['mysql']['setting']['titlechangeposts']."','".$a_lang['mysql']['setting']['titlechangepostsdesc']."','6','input','titlechangeposts','','500','','4','1'),
('".$a_lang['mysql']['setting']['locationmaxlength']."','','6','input','locationmaxlength','','14','','5','1'),
('".$a_lang['mysql']['setting']['signaturemaxlength']."','','6','input','signaturemaxlength','','500','','6','1'),
('".$a_lang['mysql']['setting']['signatureallowhtml']."','".$a_lang['mysql']['setting']['signatureallowhtmldesc']."','6','yes_no','signatureallowhtml','','0','','7','1'),
('".$a_lang['mysql']['setting']['signatureallowbbcode']."','','6','yes_no','signatureallowbbcode','','1','','8','1'),
('".$a_lang['mysql']['setting']['allowuploadsigimg']."','','6','yes_no','allowuploadsigimg','','1','','9','1'),
('".$a_lang['mysql']['setting']['sigimgdimension']."','".$a_lang['mysql']['setting']['sigimgdimensiondesc']."','6','input','sigimgdimension','','300x500','','10','1'),
('".$a_lang['mysql']['setting']['removesubscibe']."','".$a_lang['mysql']['setting']['removesubscibedesc']."','6','input','removesubscibe','','0','','11','1'),
('".$a_lang['mysql']['setting']['avatarsenabled']."','','6','yes_no','avatarsenabled','','1','','12','1'),
('".$a_lang['mysql']['setting']['defaultavatar']."','".$a_lang['mysql']['setting']['defaultavatardesc']."','6','input','defaultavatar','','','','13','1'),
('".$a_lang['mysql']['setting']['avatarextension']."','".$a_lang['mysql']['setting']['avatarextensiondesc']."','6','input','avatarextension','','gif,jpg,jpeg,png','','14','1'),
('".$a_lang['mysql']['setting']['avatarurl']."','','6','yes_no','avatarurl','','1','','15','1'),
('".$a_lang['mysql']['setting']['avatamaxsize']."','','6','input','avatamaxsize','','50','','16','1'),
('".$a_lang['mysql']['setting']['avatardimension']."','".$a_lang['mysql']['setting']['avatardimensiondesc']."','6','input','avatardimension','','120x120','','17','1'),
('".$a_lang['mysql']['setting']['avatardimensiondefault']."','".$a_lang['mysql']['setting']['avatardimensiondefaultdesc']."','6','input','avatardimensiondefault','','80x80','','18','1'),
('".$a_lang['mysql']['setting']['avatarcolspannumbers']."','','6','input','avatarcolspannumbers','','5','','19','1'),
('".$a_lang['mysql']['setting']['disableavatarsize']."','".$a_lang['mysql']['setting']['disableavatarsizedesc']."','6','yes_no','disableavatarsize','','0','','20','1'),
('".$a_lang['mysql']['setting']['guestviewsignature']."','','6','yes_no','guestviewsignature','','1','','21','1'),
('".$a_lang['mysql']['setting']['guestviewimg']."','','6','yes_no','guestviewimg','','1','','22','1'),
('".$a_lang['mysql']['setting']['guestviewavatar']."','','6','yes_no','guestviewavatar','','1','','23','1'),
('".$a_lang['mysql']['setting']['award_deduct']."','".$a_lang['mysql']['setting']['award_deductdesc']."','6','input','award_deduct','','100|3','','24','0'),
('".$a_lang['mysql']['setting']['maxpostchars']."','','7','input','maxpostchars','','20000','','1','1'),
('".$a_lang['mysql']['setting']['minpostchars']."','','7','input','minpostchars','','4','','2','1'),
('".$a_lang['mysql']['setting']['maxflashwidth']."','".$a_lang['mysql']['setting']['maxflashwidthdesc']."','7','input','maxflashwidth','','500','','3','1'),
('".$a_lang['mysql']['setting']['maxflashheight']."','".$a_lang['mysql']['setting']['maxflashheightdesc']."','7','input','maxflashheight','','400','','4','1'),
('".$a_lang['mysql']['setting']['perlineicons']."','','7','input','perlineicons','','8','','5','1'),
('".$a_lang['mysql']['setting']['rowsmiles']."','".$a_lang['mysql']['setting']['rowsmilesdesc']."','7','input','rowsmiles','','4','','6','1'),
('".$a_lang['mysql']['setting']['perlinesmiles']."','".$a_lang['mysql']['setting']['perlinesmilesdesc']."','7','input','perlinesmiles','','4','','7','1'),
('".$a_lang['mysql']['setting']['stripquotes']."','".$a_lang['mysql']['setting']['stripquotesdesc']."','7','yes_no','stripquotes','','1','','8','1'),
('".$a_lang['mysql']['setting']['imageextension']."','".$a_lang['mysql']['setting']['imageextensiondesc']."','7','input','imageextension','','gif,jpeg,jpg,png','','9','1'),
('".$a_lang['mysql']['setting']['guesttag']."','".$a_lang['mysql']['setting']['guesttagdesc']."','7','input','guesttag','','[".$a_lang['mysql']['setting']['guest']."]*','','10','1'),
('".$a_lang['mysql']['setting']['enablepolltags']."','','7','yes_no','enablepolltags','','1','','11','1'),
('".$a_lang['mysql']['setting']['maxpolloptions']."','','7','input','maxpolloptions','','10','','12','1'),
('".$a_lang['mysql']['setting']['addpolltimeout']."','".$a_lang['mysql']['setting']['addpolltimeoutdesc']."','7','input','addpolltimeout','','24','','13','1'),
('".$a_lang['mysql']['setting']['disablenoreplypoll']."','','7','yes_no','disablenoreplypoll','','0','','14','1'),
('".$a_lang['mysql']['setting']['floodchecktime']."','".$a_lang['mysql']['setting']['floodchecktimedesc']."','7','input','floodchecktime','','0','','15','1'),
('".$a_lang['mysql']['setting']['watermark']."','".$a_lang['mysql']['setting']['watermarkdesc']."','7','yes_no','watermark','','0','','16','1'),
('".$a_lang['mysql']['setting']['markposition']."','".$a_lang['mysql']['setting']['markpositiondesc']."','7','dropdown','markposition','','4','".$a_lang['mysql']['setting']['watermarkextra']."','17','1'),
('".$a_lang['mysql']['setting']['useantispam']."','".$a_lang['mysql']['setting']['useantispamdesc']."','7','yes_no','useantispam','','0','','18','1'),
('".$a_lang['mysql']['setting']['mxemode']."','".$a_lang['mysql']['setting']['mxemodedesc']."','8','dropdown','mxemode','','1','".$a_lang['mysql']['setting']['mxemodeextra']."','19','1'),
('".$a_lang['mysql']['setting']['matchbrowser']."','".$a_lang['mysql']['setting']['matchbrowserdesc']."','8','yes_no','matchbrowser','','0','','1','1'),
('".$a_lang['mysql']['setting']['allowdynimg']."','".$a_lang['mysql']['setting']['allowdynimgdesc']."','8','yes_no','allowdynimg','','0','','2','1'),
('".$a_lang['mysql']['setting']['allowimages']."','".$a_lang['mysql']['setting']['allowimagesdesc']."','8','yes_no','allowimages','','1','','3','1'),
('".$a_lang['mysql']['setting']['allowflash']."','".$a_lang['mysql']['setting']['allowflashdesc']."','8','yes_no','allowflash','','0','','4','1'),
('".$a_lang['mysql']['setting']['forcelogin']."','".$a_lang['mysql']['setting']['forcelogindesc']."','8','yes_no','forcelogin','','0','','5','1'),
('".$a_lang['mysql']['setting']['WOLenable']."','','8','yes_no','WOLenable','','1','','6','1'),
('".$a_lang['mysql']['setting']['enablesearches']."','','9','yes_no','enablesearches','','1','','1','1'),
('".$a_lang['mysql']['setting']['minsearchlength']."','','9','input','minsearchlength','','4','minsearchlength','2','1'),
('".$a_lang['mysql']['setting']['postsearchlength']."','".$a_lang['mysql']['setting']['postsearchlengthdesc']."','9','input','postsearchlength','','500','','3','1'),
('".$a_lang['mysql']['setting']['forumindex']."','".$a_lang['mysql']['setting']['forumindexdesc']."','10','input','forumindex','','index.php','','1','1'),
('".$a_lang['mysql']['setting']['shownewslink']."','','10','yes_no','shownewslink','','0','','2','1'),
('".$a_lang['mysql']['setting']['newsforumid']."','','10','dropdown','newsforumid','','','#show_forums#','3','1'),
('".$a_lang['mysql']['setting']['showloggedin']."','','10','yes_no','showloggedin','','1','','4','1'),
('".$a_lang['mysql']['setting']['showstatus']."','','10','yes_no','showstatus','','1','','5','1'),
('".$a_lang['mysql']['setting']['showbirthday']."','".$a_lang['mysql']['setting']['showbirthdaydesc']."','10','yes_no','showbirthday','','1','','6','1'),
('".$a_lang['mysql']['setting']['autohidebirthday']."','".$a_lang['mysql']['setting']['autohidebirthdaydesc']."','10','yes_no','autohidebirthday','','0','','7','1'),
('".$a_lang['mysql']['setting']['maxonlineusers']."','".$a_lang['mysql']['setting']['maxonlineusersdesc']."','10','input','maxonlineusers','','300','','8','1'),
('".$a_lang['mysql']['setting']['showguest']."','".$a_lang['mysql']['setting']['showguestdesc']."','10','yes_no','showguest','','0','','9','1'),
('".$a_lang['mysql']['setting']['birthday_send']."','".$a_lang['mysql']['setting']['birthday_senddesc']."','10','textarea','birthday_send','','0','','10','1'),
('".$a_lang['mysql']['setting']['birthday_send_type']."','".$a_lang['mysql']['setting']['birthday_send_typedesc']."','10','dropdown','birthday_send_type','','1','".$a_lang['mysql']['setting']['birthday_send_typeextra']."','11','0'),
('".$a_lang['mysql']['setting']['moderatorlist']."', '', 10, 'dropdown', 'indexmoderatorlist', '', '1', '1=".$a_lang['mysql']['setting']['moderatorlistd']."\r\n2=".$a_lang['mysql']['setting']['moderatorlisth']."', 12, 1),
('".$a_lang['mysql']['setting']['perpagepost']."','".$a_lang['mysql']['setting']['perpagepostdesc']."','11','input','perpagepost','','5,10,15,20,25,30,35,40','','1','1'),
('".$a_lang['mysql']['setting']['maxposts']."','','11','input','maxposts','','10','','2','1'),
('".$a_lang['mysql']['setting']['viewattachedimages']."','".$a_lang['mysql']['setting']['viewattachedimagesdesc']."','11','yes_no','viewattachedimages','','1','','3','1'),
('".$a_lang['mysql']['setting']['viewattachedthumbs']."','".$a_lang['mysql']['setting']['viewattachedthumbsdesc']."','11','yes_no','viewattachedthumbs','','1','viewattachedthumbs','4','1'),
('".$a_lang['mysql']['setting']['thumbswidth']."','".$a_lang['mysql']['setting']['thumbswidthdesc']."','11','input','thumbswidth','','200','','5','1'),
('".$a_lang['mysql']['setting']['thumbsheight']."','".$a_lang['mysql']['setting']['thumbsheightdesc']."','11','input','thumbsheight','','200','','6','1'),
('".$a_lang['mysql']['setting']['allowviewresults']."','".$a_lang['mysql']['setting']['allowviewresultsdesc']."','11','yes_no','allowviewresults','','1','','7','1'),
('".$a_lang['mysql']['setting']['onlyonesignatures']."','".$a_lang['mysql']['setting']['onlyonesignaturesdesc']."','11','yes_no','onlyonesignatures','','1','','8','1'),
('".$a_lang['mysql']['setting']['quickeditorloadmode']."', '".$a_lang['mysql']['setting']['quickeditorloadmodedesc']."', 11, 'dropdown', 'quickeditorloadmode', '', '2', '".$a_lang['mysql']['setting']['quickeditorloadmodeextra']."', 9, 1),
('".$a_lang['mysql']['setting']['quickeditordisplaymenu']."', '".$a_lang['mysql']['setting']['quickeditordisplaymenudesc']."', 11, 'yes_no', 'quickeditordisplaymenu', '', '1', '', 10, 1),
('".$a_lang['mysql']['setting']['maxthreads']."','','12','input','maxthreads','','25','','1','1'),
('".$a_lang['mysql']['setting']['gstickyprefix']."','','12','input','gstickyprefix','','".$a_lang['mysql']['setting']['gstickyprefixdval']."','','2','1'),
('".$a_lang['mysql']['setting']['stickyprefix']."','','12','input','stickyprefix','','".$a_lang['mysql']['setting']['stickyprefixdval']."','','3','1'),
('".$a_lang['mysql']['setting']['movedprefix']."','','12','input','movedprefix','','".$a_lang['mysql']['setting']['movedprefixdval']."','','4','1'),
('".$a_lang['mysql']['setting']['pollprefix']."','','12','input','pollprefix','','".$a_lang['mysql']['setting']['pollprefixdval']."','','5','1'),
('".$a_lang['mysql']['setting']['hotnumberposts']."','','12','input','hotnumberposts','','15','','6','1'),
('".$a_lang['mysql']['setting']['showforumusers']."','".$a_lang['mysql']['setting']['showforumusersdesc']."','12','yes_no','showforumusers','','0','showforumusers','7','1'),
('".$a_lang['mysql']['setting']['perpagethread']."','".$a_lang['mysql']['setting']['perpagethreaddesc']."','12','input','perpagethread','','5,10,15,20,25,30,35,40','','8','1'),
('".$a_lang['mysql']['setting']['showsubforums']."','".$a_lang['mysql']['setting']['showsubforumsdesc']."','12','yes_no','showsubforums','','1','','9','1'),
('".$a_lang['mysql']['setting']['showmoderatorcolumn']."','".$a_lang['mysql']['setting']['showmoderatorcolumndesc']."','12','yes_no','showmoderatorcolumn','','1','','10','1'),
('".$a_lang['mysql']['setting']['stickopentag']."','".$a_lang['mysql']['setting']['stickopentagdesc']."','12','input','stickopentag','','','','11','1'),
('".$a_lang['mysql']['setting']['stickclosetag']."','".$a_lang['mysql']['setting']['stickclosetagdesc']."','12','input','stickclosetag','','','','12','1'),
('".$a_lang['mysql']['setting']['threadpreview']."','".$a_lang['mysql']['setting']['threadpreviewdesc']."','12','dropdown','threadpreview','','0','".$a_lang['mysql']['setting']['threadpreviewextra']."','13','1'),
('".$a_lang['mysql']['setting']['showdescription']."','".$a_lang['mysql']['setting']['showdescriptiondesc']."','12','yes_no','showdescription','','1','','14','1'),
('".$a_lang['mysql']['setting']['moderatorlist']."', '', 12, 'dropdown', 'forumdisplaymoderatorlist', '', '1', '1=".$a_lang['mysql']['setting']['moderatorlistd']."\r\n2=".$a_lang['mysql']['setting']['moderatorlisth']."', 15, 1),
('".$a_lang['mysql']['setting']['pmallowbbcode']."','','13','yes_no','pmallowbbcode','','1','','1','1'),
('".$a_lang['mysql']['setting']['pmallowhtml']."','".$a_lang['mysql']['setting']['pmallowhtmldesc']."','13','yes_no','pmallowhtml','','0','','2','1'),
('".$a_lang['mysql']['setting']['emailreceived']."','".$a_lang['mysql']['setting']['emailreceiveddesc']."','13','input','emailreceived','','','','3','1'),
('".$a_lang['mysql']['setting']['emailsend']."','".$a_lang['mysql']['setting']['emailsenddesc']."','13','input','emailsend','','','','4','1'),
('".$a_lang['mysql']['setting']['sesureemail']."','".$a_lang['mysql']['setting']['sesureemaildesc']."','13','yes_no','sesureemail','','1','','5','1'),
('".$a_lang['mysql']['setting']['emailtype']."','".$a_lang['mysql']['setting']['emailtypedesc']."','13','dropdown','emailtype','','mail','mail=PHP Mail()\nsmtp=SMTP','6','1'),
('".$a_lang['mysql']['setting']['smtphost']."','".$a_lang['mysql']['setting']['smtphostdesc']."','13','input','smtphost','','localhost','','7','1'),
('".$a_lang['mysql']['setting']['smtpport']."','".$a_lang['mysql']['setting']['smtpportdesc']."','13','input','smtpport','','25','','8','1'),
('".$a_lang['mysql']['setting']['smtpuser']."','".$a_lang['mysql']['setting']['smtpuserdesc']."','13','input','smtpuser','','','','9','1'),
('".$a_lang['mysql']['setting']['smtppassword']."','".$a_lang['mysql']['setting']['smtppassworddesc']."','13','input','smtppassword','','','','10','1'),
('".$a_lang['mysql']['setting']['emailwrapbracket']."','".$a_lang['mysql']['setting']['emailwrapbracketdesc']."','13','yes_no','emailwrapbracket','','0','','11','1'),
('".$a_lang['mysql']['setting']['disablereport']."','".$a_lang['mysql']['setting']['disablereportdesc']."','13','yes_no','disablereport','','0','','12','1'),
('".$a_lang['mysql']['setting']['reporttype']."','','13','dropdown','reporttype','','pm','".$a_lang['mysql']['setting']['reporttypeextra']."','13','1'),
('".$a_lang['mysql']['setting']['enablerecyclebin']."','".$a_lang['mysql']['setting']['enablerecyclebindesc']."','14','yes_no','enablerecyclebin','','0','','1','1'),
('".$a_lang['mysql']['setting']['recycleforumid']."','".$a_lang['mysql']['setting']['recycleforumiddesc']."','14','dropdown','recycleforumid','','','#show_forums#','2','1'),
('".$a_lang['mysql']['setting']['recycleforadmin']."','".$a_lang['mysql']['setting']['recycleforadmindesc']."','14','yes_no','recycleforadmin','','1','','3','1'),
('".$a_lang['mysql']['setting']['recycleforsuper']."','".$a_lang['mysql']['setting']['recycleforsuperdesc']."','14','yes_no','recycleforsuper','','1','','4','1'),
('".$a_lang['mysql']['setting']['recycleformod']."','".$a_lang['mysql']['setting']['recycleformoddesc']."','14','yes_no','recycleformod','','1','','5','1'),
('".$a_lang['mysql']['setting']['allowregistration']."','".$a_lang['mysql']['setting']['allowregistrationdesc']."','15','yes_no','allowregistration','','1','','1','1'),
('".$a_lang['mysql']['setting']['enableantispam']."','".$a_lang['mysql']['setting']['enableantispamdesc']."','15','dropdown','enableantispam','','gif','".$a_lang['mysql']['setting']['enableantispamextra']."','2','1'),
('".$a_lang['mysql']['setting']['moderatememberstype']."','".$a_lang['mysql']['setting']['moderatememberstypedesc']."','15','dropdown','moderatememberstype','','0','".$a_lang['mysql']['setting']['moderatememberstypextra']."','3','1'),
('".$a_lang['mysql']['setting']['removemoderate']."','".$a_lang['mysql']['setting']['removemoderatedesc']."','15','input','removemoderate','','0','','4','1'),
('".$a_lang['mysql']['setting']['newregisterpost']."','".$a_lang['mysql']['setting']['newregisterpostdesc']."','15','input','newregisterpost','','0','','5','1'),
('".$a_lang['mysql']['setting']['registerrule']."','','15','textarea','registerrule','','".$a_lang['mysql']['setting']['registerruledval']."','','6','0'),
('".$a_lang['mysql']['setting']['newuser_give']."','".$a_lang['mysql']['setting']['newuser_givedesc']."','15','input','newuser_give','','','','7','0'),
('".$a_lang['mysql']['setting']['newuser_pm']."','".$a_lang['mysql']['setting']['newuser_pmdesc']."','15','textarea','newuser_pm','','','','8','0'),
('".$a_lang['mysql']['setting']['reg_ip_time']."','".$a_lang['mysql']['setting']['reg_ip_timedesc']."','15','input','reg_ip_time','','0','','9','1'),
('".$a_lang['mysql']['setting']['showprivacy']."','','16','yes_no','showprivacy','','0','','1','1'),
('".$a_lang['mysql']['setting']['privacyurl']."','".$a_lang['mysql']['setting']['privacyurldesc']."','16','input','privacyurl','','','','2','1'),
('".$a_lang['mysql']['setting']['privacytitle']."','','16','input','privacytitle','','','','3','1'),
('".$a_lang['mysql']['setting']['privacytext']."','".$a_lang['mysql']['setting']['privacytextdesc']."','16','textarea','privacytext','','','','4','0'),
('".$a_lang['mysql']['setting']['bbactive']."','".$a_lang['mysql']['setting']['bbactivedesc']."','17','yes_no','bbactive','','1','','1','1'),
('".$a_lang['mysql']['setting']['bbclosedreason']."','','17','textarea','bbclosedreason','','".$a_lang['mysql']['setting']['bbclosedreasondval']."','','2','0'),
('".$a_lang['mysql']['setting']['openbank']."','".$a_lang['mysql']['setting']['openbankdesc']."','18','yes_no','openbank','','1','','1','0'),
('".$a_lang['mysql']['setting']['bankcurrency']."','".$a_lang['mysql']['setting']['bankcurrencydesc']."','18','input','bankcurrency','','".$a_lang['mysql']['setting']['bankcurrencydval']."','','2','0'),
('".$a_lang['mysql']['setting']['bankcost']."','".$a_lang['mysql']['setting']['bankcostdesc']."','18','input','bankcost','','50','','3','0'),
('".$a_lang['mysql']['setting']['bankinterest']."','".$a_lang['mysql']['setting']['bankinterestdesc']."','18','input','bankinterest','','12','','4','0'),
('".$a_lang['mysql']['setting']['bankexcost']."','".$a_lang['mysql']['setting']['bankexcostdesc']."','18','input','bankexcost','','20','','5','0'),
('".$a_lang['mysql']['setting']['bankexcostskip']."','".$a_lang['mysql']['setting']['bankexcostskipdesc']."','18','input','bankexcostskip','','100','','6','0'),
('".$a_lang['mysql']['setting']['banknewthread']."','".$a_lang['mysql']['setting']['banknewthreaddesc']."','18','input','banknewthread','','2','','7','0'),
('".$a_lang['mysql']['setting']['bankreplythread']."','".$a_lang['mysql']['setting']['bankreplythreaddesc']."','18','input','bankreplythread','','1','','8','0'),
('".$a_lang['mysql']['setting']['bankquint']."','".$a_lang['mysql']['setting']['bankquintdesc']."','18','input','bankquint','','20','','9','0'),
('".$a_lang['mysql']['setting']['bankrepprice']."','".$a_lang['mysql']['setting']['bankreppricedesc']."','18','input','bankrepprice','','20000','','10','0'),
('".$a_lang['mysql']['setting']['bankrepsellprice']."','".$a_lang['mysql']['setting']['bankrepsellpricedesc']."','18','input','bankrepsellprice','','15000','','11','0'),
('".$a_lang['mysql']['setting']['bankloanonoff']."','".$a_lang['mysql']['setting']['bankloanonoffdesc']."','18','yes_no','bankloanonoff','','1','','12','0'),
('".$a_lang['mysql']['setting']['bankloanreglimit']."','".$a_lang['mysql']['setting']['bankloanreglimitdesc']."','18','input','bankloanreglimit','','48','','13','0'),
('".$a_lang['mysql']['setting']['bankloanpostlimit']."','".$a_lang['mysql']['setting']['bankloanpostlimitdesc']."','18','input','bankloanpostlimit','','100','','14','0'),
('".$a_lang['mysql']['setting']['bankloanreplimit']."','".$a_lang['mysql']['setting']['bankloanreplimitdesc']."','18','input','bankloanreplimit','','-20','','15','0'),
('".$a_lang['mysql']['setting']['bankloanusegroup']."','".$a_lang['mysql']['setting']['bankloanusegroupdesc']."','18','yes_no','bankloanusegroup','','0','','16','0'),
('".$a_lang['mysql']['setting']['bankloanamount']."','".$a_lang['mysql']['setting']['bankloanamountdesc']."','18','input','bankloanamount','','2000','','17','0'),
('".$a_lang['mysql']['setting']['bankloantimelimit']."','".$a_lang['mysql']['setting']['bankloantimelimitdesc']."','18','input','bankloantimelimit','','1,5,10,15,30','','18','0'),
('".$a_lang['mysql']['setting']['bankloaninterest']."','".$a_lang['mysql']['setting']['bankloaninterestdesc']."','18','input','bankloaninterest','','130,150,180,220,300','','19','0'),
('".$a_lang['mysql']['setting']['bankpbban']."','".$a_lang['mysql']['setting']['bankpbbandesc']."','18','input','bankpbban','','1','','20','0'),
('".$a_lang['mysql']['setting']['bankpbrerate']."','".$a_lang['mysql']['setting']['bankpbreratedesc']."','18','input','bankpbrerate','','80','','21','0'),
('".$a_lang['mysql']['setting']['bankpbreppanish']."','".$a_lang['mysql']['setting']['bankpbreppanishdesc']."','18','input','bankpbreppanish','','10','','22','0'),
('".$a_lang['mysql']['setting']['bankpbclean']."','".$a_lang['mysql']['setting']['bankpbcleandesc']."','18','input','bankpbclean','','1000','','23','0'),
('".$a_lang['mysql']['setting']['reducetmoney']."','".$a_lang['mysql']['setting']['reducetmoneydesc']."','18','input','reducetmoney','','20','','24','0'),
('".$a_lang['mysql']['setting']['reducepmoney']."','".$a_lang['mysql']['setting']['reducepmoneydesc']."','18','input','reducepmoney','','5','','25','0'),
('".$a_lang['mysql']['setting']['version']."','','-1','','version','','2.6.1','','1','1'),
('".$a_lang['mysql']['setting']['openolrank']."','".$a_lang['mysql']['setting']['openolrankdesc']."','19','yes_no','openolrank','1','1','','1','1'),
('".$a_lang['mysql']['setting']['olrankstart']."','".$a_lang['mysql']['setting']['olrankstartdesc']."','19','input','olrankstart','5','5','','2','1'),
('".$a_lang['mysql']['setting']['olrankstep']."','".$a_lang['mysql']['setting']['olrankstepdesc']."','19','input','olrankstep','2','2','','3','1'),
('".$a_lang['mysql']['setting']['isopeninvite']."','','20','yes_no','isopeninvite','','0','','1','1'),
('".$a_lang['mysql']['setting']['nolimitinviteusergroup']."','".$a_lang['mysql']['setting']['nolimitinviteusergroupdesc']."','20','multi','nolimitinviteusergroup','','6,7','#show_groups#','2','1'),
('".$a_lang['mysql']['setting']['limitinvitegroup']."','".$a_lang['mysql']['setting']['limitinvitegroupdesc']."','20','multi','limitinvitegroup','','6,7','#show_groups#','3','1'),
('".$a_lang['mysql']['setting']['limitregtime']."','".$a_lang['mysql']['setting']['limitregtimedesc']."','20','input','limitregtime','','300','','4','1'),
('".$a_lang['mysql']['setting']['limitposttitlenum']."','','20','input','limitposttitlenum','','500','','5','1'),
('".$a_lang['mysql']['setting']['inviteminnum']."','','20','input','inviteminnum','','5','','6','1'),
('".$a_lang['mysql']['setting']['invitemaxnum']."','','20','input','invitemaxnum','','10','','7','1'),
('".$a_lang['mysql']['setting']['invitexpiry']."','','20','input','invitexpiry','','5','','8','1'),
('".$a_lang['mysql']['setting']['inviteduserexpiry']."','','20','input','inviteduserexpiry','','6','','9','1'),
('".$a_lang['mysql']['setting']['timenotlogin']."','".$a_lang['mysql']['setting']['timenotlogindesc']."','20','input','timenotlogin','','30','','10','1'),
('".$a_lang['mysql']['setting']['modreptype']."','".$a_lang['mysql']['setting']['modreptypedesc']."','1','dropdown','modreptype','','2','".$a_lang['mysql']['setting']['modreptypextra']."','26','1'),
('".$a_lang['mysql']['setting']['modrepmax']."','".$a_lang['mysql']['setting']['modrepmaxdesc']."','1','input','modrepmax','100','100','','27','1'),
('".$a_lang['mysql']['setting']['spider_roup']."','".$a_lang['mysql']['setting']['spider_roupdesc']."','21','dropdown','spider_roup','','3','#show_groups#','1','1'),
('".$a_lang['mysql']['setting']['spiderid']."','".$a_lang['mysql']['setting']['spideriddesc']."','21','input','spiderid','','baiduspider|googlebot|msnbot|Slurp','','2','1'),
('".$a_lang['mysql']['setting']['adcolumns']."','".$a_lang['mysql']['setting']['adcolumnsdesc']."','22','input','adcolumns','','4','','1','1'),
('".$a_lang['mysql']['setting']['adinpost']."','".$a_lang['mysql']['setting']['adinpostdesc']."','22','input','adinpost','','0','','2','1'),
('".$a_lang['mysql']['setting']['rewritestatus']."','".$a_lang['mysql']['setting']['rewritestatusdesc']."','21','yes_no','rewritestatus','','0','','3','1')
";


$mysql_data['CREATE']['settinggroup'] = "
CREATE TABLE $pref"."settinggroup (
  groupid smallint(3) NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  description mediumtext NOT NULL default '',
  groupcount smallint(3) NOT NULL default '0',
  PRIMARY KEY  (groupid)
);
";

$mysql_data['INSERT']['settinggroup'] = "
INSERT INTO $pref"."settinggroup (groupid, title, description, groupcount) VALUES
(1, '".$a_lang['mysql']['settinggroup']['generalsetting']."', '".$a_lang['mysql']['settinggroup']['generalsettingdesc']."', 13),
(2, '".$a_lang['mysql']['settinggroup']['forumoptimize']."', '".$a_lang['mysql']['settinggroup']['forumoptimizedesc']."', 2),
(3, '".$a_lang['mysql']['settinggroup']['sitenameurl']."', '".$a_lang['mysql']['settinggroup']['sitenameurldesc']."', 6),
(4, '".$a_lang['mysql']['settinggroup']['cookieoption']."', '".$a_lang['mysql']['settinggroup']['cookieoptiondesc']."', 4),
(5, '".$a_lang['mysql']['settinggroup']['datetimeoption']."', '".$a_lang['mysql']['settinggroup']['datetimeoptiondesc']."', 5),
(6, '".$a_lang['mysql']['settinggroup']['userpara']."', '".$a_lang['mysql']['settinggroup']['userparadesc']."', 24),
(7, '".$a_lang['mysql']['settinggroup']['postoption']."', '".$a_lang['mysql']['settinggroup']['postoptiondesc']."', 16),
(8, '".$a_lang['mysql']['settinggroup']['securityctrl']."', '".$a_lang['mysql']['settinggroup']['securityctrldesc']."', 7),
(9, '".$a_lang['mysql']['settinggroup']['searchoption']."', '', 3),
(10, '".$a_lang['mysql']['settinggroup']['indexsetting']."', '".$a_lang['mysql']['settinggroup']['indexsettingdesc']."', 10),
(11, '".$a_lang['mysql']['settinggroup']['showthread']."', '', 8),
(12, '".$a_lang['mysql']['settinggroup']['forumdisplay']."', '', 14),
(13, '".$a_lang['mysql']['settinggroup']['emailpmsetting']."','".$a_lang['mysql']['settinggroup']['emailpmsettingdesc']."', 13),
(14, '".$a_lang['mysql']['settinggroup']['recyclesetting']."','".$a_lang['mysql']['settinggroup']['recyclesettingdesc']."', 5),
(15, '".$a_lang['mysql']['settinggroup']['userregoption']."', '', 8),
(16, '".$a_lang['mysql']['settinggroup']['privacyance']."', '".$a_lang['mysql']['settinggroup']['privacyancedesc']."', 4),
(17, '".$a_lang['mysql']['settinggroup']['opencloseforum']."', '".$a_lang['mysql']['settinggroup']['opencloseforumdesc']."', 2),
(18, '', '', 22),
(19, '".$a_lang['mysql']['settinggroup']['olranksetting']."', '".$a_lang['mysql']['settinggroup']['olranksettingdesc']."', 3),
(20, '".$a_lang['mysql']['settinggroup']['invitesetting']."', '".$a_lang['mysql']['settinggroup']['invitesettingdesc']."', 10),
(21, '".$a_lang['mysql']['settinggroup']['searchenginesetting']."', '".$a_lang['mysql']['settinggroup']['searchenginesettingdesc']."', 3),
(22, '".$a_lang['mysql']['settinggroup']['adsetting']."', '".$a_lang['mysql']['settinggroup']['adsettingdesc']."', 2)
";


$mysql_data['CREATE']['smile'] = "
CREATE TABLE $pref"."smile (
  id smallint(5) unsigned NOT NULL auto_increment,
  smiletext varchar(32) NOT NULL default '',
  image varchar(128) NOT NULL default '',
  displayorder smallint(3) NOT NULL default '0',
  PRIMARY KEY  (id)
);
";

$mysql_data['INSERT']['smile'] = "
INSERT INTO $pref"."smile (smiletext, image, displayorder) VALUES
(':run:', 'run.gif', 0),
(':cry:', 'cry.gif', 0),
(':prejudice:', 'prejudice.gif', 0),
(':laugh:', 'laugh.gif', 0),
(':glad:', 'glad.gif', 0),
(':cool:', 'cool.gif', 0),
(':bother:', 'bother.gif', 0),
(':sweat:', 'sweat.gif', 0),
(':bored:', 'bored.gif', 0),
(':angry:', 'angry.gif', 0),
(':afraid:', 'afraid.gif', 0),
(':shine:', 'shine.gif', 0),
(':smile:', 'smile.gif', 0),
(':surprise:', 'surprise.gif', 0),
(':teeth:', 'teeth.gif', 0)
";

$mysql_data['CREATE']['specialtopic'] = "
CREATE TABLE $pref"."specialtopic (
  id mediumint(8) NOT NULL auto_increment,
  name varchar(32) NOT NULL default '',
  forumids varchar(255) NOT NULL default '',
  PRIMARY KEY  (id)
);
";

$mysql_data['CREATE']['strikes'] = "
CREATE TABLE $pref"."strikes (
  striketime int(10) unsigned NOT NULL default '0',
  strikeip char(15) NOT NULL default '',
  username varchar(32) NOT NULL default '',
  KEY striketime (striketime),
  KEY strikeip (strikeip, username)
);
";

$mysql_data['CREATE']['style'] = "
CREATE TABLE $pref"."style (
  styleid smallint(5) NOT NULL auto_increment,
  title varchar(150) NOT NULL default '',
  imagefolder varchar(200) NOT NULL default '',
  userselect tinyint(1) NOT NULL default '0',
  usedefault tinyint(1) NOT NULL default '0',
  csstype tinyint(1) NOT NULL default '0',
  parentid smallint(6) NOT NULL default '0',
  parentlist varchar(250) NOT NULL default '-1',
  css mediumtext NOT NULL default '',
  csscache mediumtext NOT NULL default '',
  version varchar(20) NOT NULL default '',
  PRIMARY KEY  (styleid)
);
";



$mysql_data['CREATE']['subscribeforum'] = "
CREATE TABLE $pref"."subscribeforum (
  subscribeforumid mediumint(8) unsigned NOT NULL auto_increment,
  userid mediumint(8) NOT NULL default '0',
  forumid smallint(5) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (subscribeforumid),
  KEY userid (userid),
  KEY forumid (forumid, userid)
);
";



$mysql_data['CREATE']['subscribethread'] = "
CREATE TABLE $pref"."subscribethread (
  subscribethreadid mediumint(8) unsigned NOT NULL auto_increment,
  userid mediumint(8) NOT NULL default '0',
  threadid int(10) NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (subscribethreadid),
  KEY userid (userid)
);
";


$mysql_data['CREATE']['template'] = "
CREATE TABLE $pref"."template (
  tid int(10) unsigned NOT NULL auto_increment,
  styleid smallint(5) NOT NULL default '0',
  templategroup varchar(255) NOT NULL default '',
  title varchar(255) NOT NULL default '',
  template mediumtext NOT NULL default '',
  PRIMARY KEY (tid),
  KEY styleid (styleid)
);
";


$mysql_data['CREATE']['templategroup'] = "
CREATE TABLE $pref"."templategroup (
  templategroupid int(10) unsigned NOT NULL auto_increment,
  title varchar(255) NOT NULL default '',
  templategroup mediumtext NOT NULL default '',
  noheader tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (templategroupid),
  KEY title (title)
);
";

$mysql_data['INSERT']['templategroup'] = "
INSERT INTO $pref"."templategroup (title, templategroup, noheader) VALUES
('addpoll', '<<<addpoll>>>', 0),
('announcement', '<<<announcement>>>', 0),
('attachment', '<<<attachment>>>', 1),
('archive_index', '<<<archive_header>>>\r\n<<<archive_body>>>\r\n<<<archive_index>>>\r\n<<<archive_footer>>>', 1),
('archive_showthread', '<<<archive_header>>>\r\n<<<archive_body>>>\r\n<<<archive_showthread>>>\r\n<<<archive_footer>>>', 1),
('archive_threadlist', '<<<archive_header>>>\r\n<<<archive_body>>>\r\n<<<archive_threadlist>>>\r\n<<<archive_footer>>>', 1),
('errors', '<<<errors>>>', 0),
('faq', '<<<faq>>>', 0),
('footer', '<<<footer>>>', 1),
('footjs', '<<<footjs>>>', 1),
('forumdisplay', '<<<sub_forum>>>\r\n<<<forum_rule>>>\r\n<<<forum_top>>>\r\n<<<forum_announce>>>\r\n<<<forum_threadlist>>>\r\n<<<forum_end>>>', 0),
('forum_password', '<<<forum_password>>>', 0),
('header', '<<<header>>>\r\n<<<body>>>\r\n<<<memberbar>>>\r\n<<<navigation>>>\r\n<<<pmlink>>>', 1),
('headjs', '<<<headjs>>>', 1),
('index', '<<<index_top>>>\r\n<<<forumlist>>>\r\n<<<infomation>>>\r\n<<<league>>>', 0),
('login', '<<<login>>>', 0),
('lostpassword', '<<<lostpassword>>>', 0),
('lostpassword_safe', '<<<lostpassword_safe>>>', 0),
('mailmember', '<<<mailmember>>>', 0),
('memberlist', '<<<memberlist>>>', 0),
('misc_bbcode', '<<<misc_bbcode>>>', 0),
('misc_icons', '<<<misc_icons>>>', 1),
('misc_whobought', '<<<misc_whobought>>>', 1),
('misc_whovoters', '<<<misc_whovoters>>>', 1),
('misc_privacy', '<<<misc_privacy>>>', 0),
('misc_rss', '<<<misc_rss>>>', 0),
('mod_announcement', '<<<mod_menu>>>\r\n<<<mod_announcement>>>\r\n<<<mod_end>>>', 0),
('mod_delpost_one', '<<<mod_menu>>>\r\n<<<mod_delpost_one>>>\r\n<<<mod_end>>>', 0),
('mod_deletethread', '<<<mod_menu>>>\r\n<<<mod_deletethread>>>\r\n<<<mod_end>>>', 0),
('mod_editpoll', '<<<mod_menu>>>\r\n<<<mod_editpoll>>>\r\n<<<mod_end>>>', 0),
('mod_editthread', '<<<mod_menu>>>\r\n<<<mod_editthread>>>\r\n<<<mod_end>>>', 0),
('mod_edituser', '<<<mod_menu>>>\r\n<<<mod_edituser>>>\r\n<<<mod_end>>>', 0),
('mod_findmember', '<<<mod_menu>>>\r\n<<<mod_findmember>>>\r\n<<<mod_end>>>', 0),
('mod_merge', '<<<mod_menu>>>\r\n<<<mod_merge>>>\r\n<<<mod_end>>>', 0),
('mod_move', '<<<mod_menu>>>\r\n<<<mod_move>>>\r\n<<<mod_end>>>', 0),
('mod_specialtopic', '<<<mod_menu>>>\r\n<<<mod_specialtopic>>>\r\n<<<mod_end>>>', 0),
('mod_movepost', '<<<mod_menu>>>\r\n<<<mod_movepost>>>\r\n<<<mod_end>>>', 0),
('mod_newannouncement', '<<<mod_menu>>>\r\n<<<mod_newannouncement>>>\r\n<<<mod_end>>>', 0),
('mod_showmember', '<<<mod_menu>>>\r\n<<<mod_showmember>>>\r\n<<<mod_end>>>', 0),
('mod_splitthread', '<<<mod_menu>>>\r\n<<<mod_splitthread>>>\r\n<<<mod_end>>>', 0),
('newpost_smiles', '<<<newpost_smiles>>>', 1),
('newpost_wysiwyg', '<<<newpost_header>>>\r\n<<<newpost_wysiwyg>>>\r\n<<<newpost_end>>>', 0),
('offline', '<<<offline>>>', 0),
('online', '<<<online>>>', 0),
('pm_buddylist', '<<<usercp_menu>>>\r\n<<<pm_buddylist>>>\r\n<<<usercp_end>>>', 0),
('pm_buddyedit', '<<<usercp_menu>>>\r\n<<<pm_buddyedit>>>\r\n<<<usercp_end>>>', 0),
('pm_editfolders', '<<<usercp_menu>>>\r\n<<<pm_editfolders>>>\r\n<<<usercp_end>>>', 0),
('pm_emptyfolders', '<<<usercp_menu>>>\r\n<<<pm_emptyfolders>>>\r\n<<<usercp_end>>>', 0),
('pm_newpm', '<<<usercp_menu>>>\r\n<<<pm_newpm>>>\r\n<<<usercp_end>>>', 0),
('pm_pmlist', '<<<usercp_menu>>>\r\n<<<pm_pmlist>>>\r\n<<<usercp_end>>>', 0),
('pm_pmtracker', '<<<usercp_menu>>>\r\n<<<pm_pmtracker>>>\r\n<<<usercp_end>>>', 0),
('pm_showpm', '<<<usercp_menu>>>\r\n<<<pm_showpm>>>\r\n<<<usercp_end>>>', 0),
('printthread', '<<<printthread>>>', 1),
('profile', '<<<userinfo>>>', 0),
('redirect', '<<<redirect>>>', 1),
('register', '<<<register>>>', 0),
('register_validate', '<<<register_validate>>>', 0),
('register_reactivation', '<<<register_reactivation>>>', 0),
('report', '<<<report>>>', 0),
('showthread', '<<<showthread_top>>>\r\n<<<showthread_post>>>\r\n<<<showthread_post_end>>>\r\n<<<showthread_end>>>', 0),
('sendtofriend', '<<<sendtofriend>>>', 0),
('search', '<<<search>>>', 0),
('search_results', '<<<search_results>>>', 0),
('showthread_poll', '<<<showthread_poll>>>', 1),
('usercp_invitesend', '<<<usercp_menu>>>\r\n<<<usercp_invitesend>>>\r\n<<<usercp_end>>>', 0),
('usercp_main', '<<<usercp_menu>>>\r\n<<<usercp_main>>>\r\n<<<usercp_end>>>', 0),
('usercp_subscribe_thread', '<<<usercp_menu>>>\r\n<<<usercp_subscribe_thread>>>\r\n<<<usercp_end>>>', 0),
('usercp_subscribe_forum', '<<<usercp_menu>>>\r\n<<<usercp_subscribe_forum>>>\r\n<<<usercp_end>>>', 0),
('usercp_profile', '<<<usercp_menu>>>\r\n<<<usercp_profile>>>\r\n<<<usercp_end>>>', 0),
('usercp_signature', '<<<usercp_menu>>>\r\n<<<usercp_signature>>>\r\n<<<usercp_end>>>', 0),
('usercp_avatar', '<<<usercp_menu>>>\r\n<<<usercp_avatar>>>\r\n<<<usercp_end>>>', 0),
('usercp_attachment', '<<<usercp_menu>>>\r\n<<<usercp_attachment>>>\r\n<<<usercp_end>>>', 0),
('usercp_ignorelist', '<<<usercp_menu>>>\r\n<<<usercp_ignorelist>>>\r\n<<<usercp_end>>>', 0),
('usercp_change_password', '<<<usercp_menu>>>\r\n<<<usercp_change_password>>>\r\n<<<usercp_end>>>', 0),
('usercp_change_email', '<<<usercp_menu>>>\r\n<<<usercp_change_email>>>\r\n<<<usercp_end>>>', 0),
('usercp_avatar_category', '<<<usercp_menu>>>\r\n<<<usercp_avatar_category>>>\r\n<<<usercp_end>>>', 0),
('usercp_forum_setting', '<<<usercp_menu>>>\r\n<<<usercp_forum_setting>>>\r\n<<<usercp_end>>>', 0),
('mod_money', '<<<mod_menu>>>\r\n<<<mod_money>>>\r\n<<<mod_end>>>', 0),
('bank_main', '<<<bank_menu>>>\r\n<<<bank_main>>>\r\n<<<bank_end>>>', 0),
('bank_loan', '<<<bank_menu>>>\r\n<<<bank_main>>>\r\n<<<bank_loan>>>\r\n<<<bank_end>>>', 0),
('bank_showlog', '<<<bank_showlog>>>', 1),
('bank_purge', '<<<bank_menu>>>\r\n<<<bank_purge>>>\r\n<<<bank_end>>>', 0),
('wap_index', '<<<wap_header>>>\r\n<<<wap_index>>>\r\n<<<wap_footer>>>', 1),
('wap_info', '<<<wap_header>>>\r\n<<<wap_info>>>\r\n<<<wap_footer>>>', 1),
('wap_login', '<<<wap_header>>>\r\n<<<wap_login>>>\r\n<<<wap_footer>>>', 1),
('wap_redirect', '<<<wap_header>>>\r\n<<<wap_redirect>>>\r\n<<<wap_footer>>>', 1),
('wap_register', '<<<wap_header>>>\r\n<<<wap_register>>>\r\n<<<wap_footer>>>', 1),
('wap_forumdisplay', '<<<wap_header>>>\r\n<<<wap_forumdisplay>>>\r\n<<<wap_footer>>>', 1),
('wap_forum_password', '<<<wap_header>>>\r\n<<<wap_forum_password>>>\r\n<<<wap_footer>>>', 1),
('wap_search', '<<<wap_header>>>\r\n<<<wap_search>>>\r\n<<<wap_footer>>>', 1),
('wap_search_results', '<<<wap_header>>>\r\n<<<wap_search_results>>>\r\n<<<wap_footer>>>', 1),
('wap_showthread', '<<<wap_header>>>\r\n<<<wap_showthread>>>\r\n<<<wap_footer>>>', 1),
('wap_post', '<<<wap_header>>>\r\n<<<wap_post>>>\r\n<<<wap_footer>>>', 1),
('wap_pm_home', '<<<wap_header>>>\r\n<<<wap_pm_home>>>\r\n<<<wap_footer>>>', 1),
('wap_pm_list', '<<<wap_header>>>\r\n<<<wap_pm_list>>>\r\n<<<wap_footer>>>', 1),
('wap_pm_show', '<<<wap_header>>>\r\n<<<wap_pm_show>>>\r\n<<<wap_footer>>>', 1),
('wap_pm_post', '<<<wap_header>>>\r\n<<<wap_pm_post>>>\r\n<<<wap_footer>>>', 1),
('wap_announcement', '<<<wap_header>>>\r\n<<<wap_announcement>>>\r\n<<<wap_footer>>>', 1),
('wap_attachment', '<<<wap_header>>>\r\n<<<wap_attachment>>>\r\n<<<wap_footer>>>', 1),
('showthread_post', '<<<showthread_post>>>', 1),
('attach', '<<<attach>>>', 1),
('showaward', '<<<showaward>>>', 0),
('award_request', '<<<award_request>>>', 1),
('forumlist_column', '<<<forumlist_column>>>', 1),
('forumlist_normal', '<<<forumlist_normal>>>', 1),
('sub_forum_column', '<<<sub_forum_column>>>', 1),
('sub_forum_normal', '<<<sub_forum_normal>>>', 1),
('forum_ad', '<<<forum_ad>>>', 1)
";


$mysql_data['CREATE']['thread'] = "
CREATE TABLE $pref"."thread (
  tid int(10) unsigned NOT NULL auto_increment,
  title varchar(250) NOT NULL default '',
  description varchar(250) default NULL,
  open tinyint(1) unsigned default '1',
  post int(10) unsigned NOT NULL default '0',
  postuserid mediumint(8) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  lastposterid mediumint(8) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  iconid smallint(5) unsigned NOT NULL default '0',
  postusername varchar(32) NOT NULL default '',
  lastposter varchar(32) NOT NULL default '',
  pollstate tinyint(1) NOT NULL default '0',
  lastvote int(10) unsigned NOT NULL default '0',
  views int(10) unsigned NOT NULL default '0',
  forumid smallint(5) unsigned NOT NULL default '0',
  visible tinyint(1) NOT NULL default '1',
  sticky tinyint(1) NOT NULL default '0',
  moved varchar(64) NOT NULL default '',
  votetotal int(10) unsigned NOT NULL default '0',
  attach smallint(3) unsigned NOT NULL default '0',
  firstpostid mediumint(8) unsigned NOT NULL default '0',
  lastpostid mediumint(8) unsigned NOT NULL default '0',
  modposts smallint(5) unsigned NOT NULL default '0',
  quintessence tinyint(1) NOT NULL default '0',
  allrep smallint(5) NOT NULL default '0',
  allcash int(10) NOT NULL default '0',
  stopic mediumint(8) NOT NULL default '0',
  logtext mediumtext NOT NULL default '',
  PRIMARY KEY  (tid),
  KEY forumid (forumid,sticky,lastpost),
  KEY visible (visible),
  KEY stopic (stopic),
  KEY quintessence (quintessence)
);
";


$mysql_data['CREATE']['user'] = "
CREATE TABLE $pref"."user (
  id mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL default '',
  usergroupid smallint(3) NOT NULL default '0',
  membergroupids varchar(255) NOT NULL default '',
  password varchar(32) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  joindate int(10) unsigned NOT NULL default '0',
  host char(15) NOT NULL default '',
  posts mediumint(7) unsigned NOT NULL default '0',
  customtitle varchar(64) default NULL,
  timezoneoffset varchar(4) default '8',
  style smallint(5) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  forbidpost varchar(100) NOT NULL default '0',
  lastvisit int(10) unsigned NOT NULL default '0',
  lastactivity int(10) unsigned NOT NULL default '0',
  viewprefs varchar(64) NOT NULL default '-1&-1',
  moderate varchar(100) NOT NULL default '0',
  liftban varchar(100) NOT NULL default '',
  salt varchar(5) NOT NULL default '',
  qq int(15) NOT NULL default '0',
  popo varchar(150) NOT NULL default '',
  uc int(15) NOT NULL default '0',
  skype varchar(150) NOT NULL default '',
  aim varchar(40) NOT NULL default '',
  icq int(15) NOT NULL default '0',
  yahoo varchar(40) NOT NULL default '',
  msn varchar(150) NOT NULL default '',
  website varchar(250) NOT NULL default '',
  birthday varchar(10) NOT NULL default '',
  gender tinyint(1) unsigned default '0',
  location varchar(250) NOT NULL default '',
  pmfolders mediumtext NOT NULL default '',
  pmunread tinyint(2) unsigned default '0',
  pmtotal smallint(5) unsigned NOT NULL default '0',
  signature mediumtext NOT NULL default '',
  avatarlocation varchar(128) NOT NULL default '',
  avatarsize varchar(9) NOT NULL default '',
  avatartype tinyint(1) NOT NULL default '0',
  options int(10) unsigned NOT NULL default '0',
  quintessence smallint(5) NOT NULL default '0',
  cash int(15) NOT NULL default '0',
  bank int(15) NOT NULL default '0',
  mkaccount int(10) NOT NULL default '0',
  reputation smallint(5) NOT NULL default '0',
  mobile bigint(20) unsigned NOT NULL default '0',
  onlinetime int(10) unsigned NOT NULL default '0',
  ishasinvite varchar(200) NOT NULL default '',
  referrerid mediumint(8) unsigned NOT NULL default '0',
  award_data varchar(255) NOT NULL default '',
  PRIMARY KEY (id),
  KEY usergroupid (usergroupid),
  KEY mobile (mobile),
  KEY mkaccount (mkaccount, bank),
  KEY lastactivity (lastactivity),
  KEY award_data (award_data)
);
";


$mysql_data['CREATE']['useractivation'] = "
CREATE TABLE $pref"."useractivation (
  useractivationid varchar(32) NOT NULL default '',
  userid mediumint(8) unsigned NOT NULL default '0',
  usergroupid smallint(3) unsigned NOT NULL default '0',
  tempgroup smallint(3) unsigned NOT NULL default '0',
  dateline int(10) unsigned NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  host char(15) NOT NULL default '',
  PRIMARY KEY (useractivationid),
  KEY type (type)
);
";


$mysql_data['CREATE']['usergroup'] = "
CREATE TABLE $pref"."usergroup (
  usergroupid smallint(3) unsigned NOT NULL auto_increment,
  grouptitle varchar(32) NOT NULL default '',
  groupranks varchar(100) NOT NULL default '',
  groupicon varchar(250) NOT NULL default '',
  onlineicon varchar(250) NOT NULL default '',
  opentag varchar(250) NOT NULL default '',
  closetag varchar(250) NOT NULL default '',
  cancustomtitle tinyint(1) NOT NULL default '0',
  cananonymous tinyint(1) NOT NULL default '0',
  canview tinyint(1) NOT NULL default '0',
  canviewmember tinyint(1) NOT NULL default '0',
  canviewothers tinyint(1) NOT NULL default '0',
  cansearch tinyint(1) NOT NULL default '0',
  cansearchpost tinyint(1) NOT NULL default '0',
  canemail tinyint(1) NOT NULL default '0',
  caneditprofile tinyint(1) NOT NULL default '0',
  canpostnew tinyint(1) NOT NULL default '0',
  canreplyown tinyint(1) NOT NULL default '0',
  canreplyothers tinyint(1) NOT NULL default '0',
  caneditpost tinyint(1) NOT NULL default '0',
  candeletepost tinyint(1) NOT NULL default '0',
  canopenclose tinyint(1) NOT NULL default '0',
  candeletethread tinyint(1) NOT NULL default '0',
  canpostpoll tinyint(1) NOT NULL default '0',
  canvote tinyint(1) NOT NULL default '0',
  supermod tinyint(1) NOT NULL default '0',
  cancontrolpanel tinyint(1) NOT NULL default '0',
  canappendedit tinyint(1) NOT NULL default '0',
  canviewoffline tinyint(1) NOT NULL default '0',
  passmoderate tinyint(1) NOT NULL default '0',
  passflood tinyint(1) NOT NULL default '0',
  canuseavatar tinyint(1) NOT NULL default '0',
  hidelist tinyint(1) NOT NULL default '0',
  canpostclosed tinyint(1) NOT NULL default '0',
  canposthtml tinyint(1) NOT NULL default '0',
  caneditthread tinyint(1) NOT NULL default '0',
  canpmattach tinyint(1) NOT NULL default '0',
  candownload tinyint(1) NOT NULL default '1',
  canshow tinyint(1) NOT NULL default '1',
  canblog tinyint(1) NOT NULL default '0',
  canuseflash tinyint(1) NOT NULL default '0',
  cansignature tinyint(1) NOT NULL default '1',
  cansigimg tinyint(1) NOT NULL default '0',
  passbadword tinyint(1) NOT NULL default '0',
  perpostattach int(10) NOT NULL default '0',
  pmquota int(5) NOT NULL default '50',
  pmsendmax int(5) NOT NULL default '0',
  searchflood mediumint(6) NOT NULL default '20',
  edittimecut int(10) NOT NULL default '0',
  attachlimit int(10) NOT NULL default '0',
  attachnum int(10) NOT NULL default '4',
  bankloanlimit int(10) NOT NULL default '0',
  canmodrep varchar(255) NOT NULL default '',
  displayorder smallint(3) UNSIGNED NOT NULL default '0',
  PRIMARY KEY  (usergroupid),
  KEY displayorder (displayorder)
);
";

$mysql_data['INSERT']['usergroup'] = "
INSERT INTO $pref"."usergroup (usergroupid, grouptitle, groupranks, groupicon, onlineicon, opentag, closetag, cancustomtitle, cananonymous, canview, canviewmember, canviewothers, cansearch, canemail, caneditprofile, canpostnew, canreplyown, canreplyothers, caneditpost, candeletepost, canopenclose, candeletethread, canpostpoll, canvote, supermod, cancontrolpanel, canappendedit, canviewoffline, passmoderate, passflood, canuseavatar, hidelist, canpostclosed, canposthtml, caneditthread, canpmattach, candownload, canshow, canuseflash, passbadword, perpostattach, pmquota, pmsendmax, searchflood, edittimecut, attachlimit, attachnum, canblog, canmodrep, cansigimg, cansignature, cansearchpost, displayorder) VALUES
(1, '".$a_lang['mysql']['usergroup']['waittingvalidate']."', '', '', '', '', '', 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 20, 0, 0, 0, 0, '', 0, 0, 0, 1),
(2, '".$a_lang['mysql']['usergroup']['guest']."', '', '', 'images/online/guest.gif', '', '', 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 20, 0, 0, 0, 0, '', 0, 0, 0, 2),
(3, '".$a_lang['mysql']['usergroup']['regmember']."', '', '', 'images/online/member.gif', '', '', 0, 0, 1, 0, 1, 0, 0, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1000, 20, 1, 20, 10, 100000, 4, 0, '', 0, 1, 0, 3),
(4, '".$a_lang['mysql']['usergroup']['admin']."', '".$a_lang['mysql']['usergroup']['admin']."', '', 'images/online/admin.gif', '', '', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 1, 0, 500, 5, 0, 5, 0, 4, 1, '1,3,4,5,6,7', 1, 1, 1, 4),
(5, '".$a_lang['mysql']['usergroup']['banuser']."', '', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 5, 20, 0, 0, 0, 0, '', 0, 0, 0, 5),
(6, '".$a_lang['mysql']['usergroup']['supermod']."', '".$a_lang['mysql']['usergroup']['supermod']."', '', 'images/online/smod.gif ', '', '', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 0, 70, 0, 0, 0, 0, 4, 1, '1,3,4,5,6,7', 1, 1, 1, 6),
(7, '".$a_lang['mysql']['usergroup']['mod']."', '".$a_lang['mysql']['usergroup']['mod']."', '', 'images/online/mod.gif', '', '', 1, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 1, 0, 0, 0, 1, 1, 1, 1, 1, 0, 5000, 70, 5, 0, 0, 300000, 4, 1, '1,3,4,5,6', 1, 1, 1, 7)
";

$mysql_data['CREATE']['userolrank'] = "
CREATE TABLE $pref"."userolrank (
  onlinerankid smallint(5) unsigned NOT NULL auto_increment,
  onlinerankimg mediumtext NOT NULL default '',
  maxnum smallint(5) unsigned NOT NULL default '1',
  onlineranklevel smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (onlinerankid),
  KEY onlineranklevel (onlineranklevel)
);
";

$mysql_data['INSERT']['userolrank'] = "
INSERT INTO $pref"."userolrank (onlinerankid, onlinerankimg, maxnum, onlineranklevel) VALUES
(1, 'ollevel1.gif', 3, 1),
(2, 'ollevel2.gif', 3, 2),
(3, 'ollevel3.gif', 3, 3)
";

$mysql_data['CREATE']['userpromotion'] = "
CREATE TABLE $pref"."userpromotion (
  userpromotionid int(10) unsigned NOT NULL auto_increment,
  usergroupid int(10) unsigned NOT NULL default '0',
  joinusergroupid int(10) unsigned NOT NULL default '0',
  date int(10) unsigned NOT NULL default '0',
  posts int(10) unsigned NOT NULL default '0',
  reputation int(10) NOT NULL default '0',
  strategy smallint(6) NOT NULL default '0',
  type smallint(6) NOT NULL default '2',
  date_sign varchar(2) NOT NULL DEFAULT '>=',
  posts_sign varchar(2) NOT NULL DEFAULT '>=',
  reputation_sign varchar(2) NOT NULL DEFAULT '>=',
  PRIMARY KEY (userpromotionid),
  KEY usergroupid (usergroupid)
);
";


$mysql_data['CREATE']['usertitle'] = "
CREATE TABLE $pref"."usertitle (
  id smallint(5) unsigned NOT NULL auto_increment,
  post int(10) unsigned NOT NULL default '0',
  title varchar(128) NOT NULL default '',
  ranklevel varchar(128) NOT NULL default '',
  PRIMARY KEY (id),
  KEY post (post)
);
";

$mysql_data['INSERT']['usertitle'] = "
INSERT INTO $pref"."usertitle (post, title, ranklevel) VALUES
(0, '".$a_lang['mysql']['usertitle']['newuser']."', '1'),
(100, '".$a_lang['mysql']['usertitle']['mediatemember']."', '2'),
(300, '".$a_lang['mysql']['usertitle']['highermember']."', '3')
";

$mysql_data['CREATE']['userexpand'] = "
CREATE TABLE $pref"."userexpand (
  id mediumint(8) unsigned NOT NULL default '0',
  PRIMARY KEY (id)
);
";

$mysql_data['CREATE']['userextra'] = "
CREATE TABLE $pref"."userextra (
  id mediumint(8) unsigned NOT NULL default '0',
  loanreturn int(10) NOT NULL default '0',
  loanamount int(10) NOT NULL default '0',
  loaninterest smallint(5) NOT NULL default '0',
  question varchar(255) NOT NULL default '',
  answer varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
);
";
?>