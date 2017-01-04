DROP TABLE IF EXISTS pw_actions;
CREATE TABLE pw_actions (
  id smallint(6) unsigned NOT NULL auto_increment,
  images char(15) NOT NULL default '',
  name char(15) NOT NULL default '',
  descrip char(100) NOT NULL default '',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

INSERT INTO pw_actions(images,name,descrip) VALUES ('1.gif', '{#action_1}', '{#act_1}');
INSERT INTO pw_actions(images,name,descrip) VALUES ('2.gif', '{#action_2}', '{#act_2}');
INSERT INTO pw_actions(images,name,descrip) VALUES ('3.gif', '{#action_3}', '{#act_3}');
INSERT INTO pw_actions(images,name,descrip) VALUES ('4.gif', '{#action_4}', '{#act_4}');
INSERT INTO pw_actions(images,name,descrip) VALUES ('5.gif', '{#action_5}', '{#act_5}');

DROP TABLE IF EXISTS pw_adminset;
CREATE TABLE pw_adminset (
  gid tinyint(3) unsigned NOT NULL default '0',
  value text NOT NULL default '',
  PRIMARY KEY  (gid)
) TYPE=MyISAM;

INSERT INTO pw_adminset VALUES (3, 'settings	1\n1\nupdatecache	1\n1\npostcache	1\n1\ncredit	1\n1\nsetforum	1\n1\nuniteforum	1\n1\ncreathtm	1\n1\nsetuser	1\n1\nipsearch	1\n1\nuserstats	1\n1\nupgrade	1\n1\neditgroup	1\n1\nlevel	1\n1\narticle	1\n1\nmember	1\n1\nmessage	1\n1\nbanuser	1\n1\nviewban	1\n1\nipban	1\n1\nsetbwd	1\n1\ntpccheck	1\n1\npostcheck	1\n1\nreport	1\n1\ncheckemail	1\n1\ncheckreg	1\n1\nannouncement	1\n1\nmailuser	1\n1\nsend_msg	1\n1\ngiveuser	1\n1\nattachment	1\n1\nattachstats	1\n1\nattachrenew	1\n1\nadminlog	1\n1\nforumlog	1\n1\ncreditlog	1\n1\nuserlog	1\n1\nsetads	1\n1\nipstates	1\n1\nshare	1\n1\nviewtody	1\n1\nc_set	1\n1\nc_forum	1\n1\nc_unite	1\n1\nc_htm	1\n1\naddatc	1\n1\nschatc	1\n1\nbakout	0\n1\nbakin	0\n1\nrepair	0\n1\nsetstyles	1');
INSERT INTO pw_adminset VALUES (4, 'settings	0\n1\nupdatecache	0\n1\npostcache	1\n1\ncredit	0\n1\nsetforum	0\n1\nuniteforum	0\n1\ncreathtm	0\n1\nsetuser	0\n1\nipsearch	1\n1\nuserstats	1\n1\nupgrade	0\n1\neditgroup	1\n1\nlevel	0\n1\narticle	0\n1\nmember	0\n1\nmessage	1\n1\nbanuser	1\n1\nviewban	1\n1\nipban	1\n1\nsetbwd	1\n1\ntpccheck	1\n1\npostcheck	1\n1\nreport	1\n1\ncheckemail	1\n1\ncheckreg	1\n1\nannouncement	1\n1\nmailuser	0\n1\nsend_msg	0\n1\ngiveuser	1\n1\nattachment	1\n1\nattachstats	1\n1\nattachrenew	1\n1\nadminlog	0\n1\nforumlog	1\n1\ncreditlog	1\n1\nuserlog	1\n1\nsetads	1\n1\nipstates	0\n1\nshare	1\n1\nviewtody	1\n1\nc_set	0\n1\nc_forum	0\n1\nc_unite	0\n1\nc_htm	0\n1\naddatc	0\n1\nschatc	0\n1\nbakout	0\n1\nbakin	0\n1\nrepair	0\n1\nsetstyles	0');
INSERT INTO pw_adminset VALUES (5, 'settings	0\n1\nupdatecache	0\n1\npostcache	0\n1\ncredit	0\n1\nsetforum	0\n1\nuniteforum	0\n1\ncreathtm	0\n1\nsetuser	0\n1\nipsearch	0\n1\nuserstats	0\n1\nupgrade	0\n1\neditgroup	0\n1\nlevel	0\n1\narticle	0\n1\nmember	0\n1\nmessage	0\n1\nbanuser	1\n1\nviewban	1\n1\nipban	0\n1\nsetbwd	0\n1\ntpccheck	0\n1\npostcheck	0\n1\nreport	1\n1\ncheckemail	0\n1\ncheckreg	0\n1\nannouncement	1\n1\nmailuser	0\n1\nsend_msg	0\n1\ngiveuser	0\n1\nattachment	0\n1\nattachstats	0\n1\nattachrenew	0\n1\nadminlog	0\n1\nforumlog	1\n1\ncreditlog	1\n1\nuserlog	0\n1\nsetads	0\n1\nipstates	0\n1\nshare	0\n1\nviewtody	0\n1\nc_set	0\n1\nc_forum	0\n1\nc_unite	0\n1\nc_htm	0\n1\naddatc	0\n1\nschatc	0\n1\nbakout	0\n1\nbakin	0\n1\nrepair	0\n1\nsetstyles	0');

DROP TABLE IF EXISTS pw_announce;
CREATE TABLE pw_announce (
  aid smallint(6) unsigned NOT NULL auto_increment,
  fid smallint(6) NOT NULL default '-1',
  ffid smallint(6) NOT NULL default '0',
  vieworder smallint(6) NOT NULL default '0',
  author varchar(15) NOT NULL default '',
  startdate varchar(15) NOT NULL default '',
  url varchar(80) NOT NULL default '',
  enddate varchar(15) NOT NULL default '',
  subject varchar(100) NOT NULL default '',
  content mediumtext NOT NULL default '',
  PRIMARY KEY  (aid),
  KEY ffid (ffid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_attachs;
CREATE TABLE pw_attachs (
  aid mediumint(8) unsigned NOT NULL auto_increment,
  fid smallint(6) unsigned NOT NULL default '0',
  uid mediumint(8) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  name char(80) NOT NULL default '',
  type char(30) NOT NULL default '',
  size int(10) unsigned NOT NULL default '0',
  attachurl char(80) NOT NULL default '0',
  hits mediumint(8) unsigned NOT NULL default '0',
  needrvrc smallint(6) unsigned NOT NULL default '0',
  uploadtime int(10) NOT NULL default '0',
  descrip char(100) NOT NULL default '',
  PRIMARY KEY  (aid),
  KEY  (fid),
  KEY  (uid),
  KEY  (type)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_banuser;
CREATE TABLE pw_banuser (
  uid mediumint(8) unsigned NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  startdate int(10) NOT NULL default '0',
  days int(4) NOT NULL default '0',
  admin char(15) NOT NULL default '',
  reason char(80) NOT NULL default '',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_bbsinfo;
CREATE TABLE pw_bbsinfo (
  id smallint(3) unsigned NOT NULL auto_increment,
  newmember varchar(15) NOT NULL default '',
  totalmember mediumint(8) unsigned NOT NULL default '0',
  higholnum smallint(6) unsigned NOT NULL default '0',
  higholtime int(10) unsigned NOT NULL default '0',
  tdtcontrol int(10) unsigned NOT NULL default '0',
  yposts mediumint(8) unsigned NOT NULL default '0',
  hposts mediumint(8) unsigned NOT NULL default '0',
  hit_tdtime int(10) unsigned NOT NULL default '0',
  hit_control tinyint(2) unsigned NOT NULL default '0',
  birthcontrol int(10) unsigned NOT NULL default '0',
  birthman text NOT NULL default '',
  plantime int(10) NOT NULL default '0',
  KEY id (id)
) TYPE=MyISAM;

INSERT INTO pw_bbsinfo VALUES (1,'',0,0,0,0,0,0,0,0,0,'','0');

DROP TABLE IF EXISTS pw_config;
CREATE TABLE pw_config (
  db_name varchar(30) NOT NULL default '',
  db_value text NOT NULL default '',
  decrip text NOT NULL default '',
  PRIMARY KEY  (db_name)
) TYPE=MyISAM;

INSERT INTO pw_config(db_name,db_value) VALUES('db_hour','20');
INSERT INTO pw_config(db_name,db_value) VALUES('db_http','N');
INSERT INTO pw_config(db_name,db_value) VALUES('db_autochange','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_bbsifopen','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_whybbsclose','{#db_whybbsclose}');
INSERT INTO pw_config(db_name,db_value) VALUES('db_bbsname','PHPwind Board');
INSERT INTO pw_config(db_name,db_value) VALUES('db_bbsurl','http://www.phpwind.net');
INSERT INTO pw_config(db_name,db_value) VALUES('db_wwwname','PHPwind Studio');
INSERT INTO pw_config(db_name,db_value) VALUES('db_wwwurl','http://www.phpwind.net');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ceoconnect','http://www.phpwind.net/sendemail.php?username=fengyu');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ceoemail','webmaster@phpwind.com');
INSERT INTO pw_config(db_name,db_value) VALUES('db_newtime','3600');
INSERT INTO pw_config(db_name,db_value) VALUES('db_signwindcode','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpic[\'pic\']','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpic[\'flash\']','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpic[\'picwidth\']','700');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpic[\'picheight\']','200');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpic[\'size\']','3');
INSERT INTO pw_config(db_name,db_value) VALUES('db_cvtime','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_timedf','8');
INSERT INTO pw_config(db_name,db_value) VALUES('db_perpage','20');
INSERT INTO pw_config(db_name,db_value) VALUES('db_readperpage','10');
INSERT INTO pw_config(db_name,db_value) VALUES('db_replysendmail','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_postmax','50000');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpost[\'pic\']','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpost[\'picwidth\']','700');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpost[\'picheight\']','700');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpost[\'size\']','5');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpost[\'flash\']','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_indexnotice','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_showreplynum','5');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windreply[\'pic\']','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windreply[\'flash\']','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtreplyrvrc','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_refreshtime','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_onlinetime','3600');
INSERT INTO pw_config(db_name,db_value) VALUES('db_footertime','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_threadonline','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_uploadmaxsize','2048000');
INSERT INTO pw_config(db_name,db_value) VALUES('db_uploadfiletype','gif jpg rar txt zip swf jqf');
INSERT INTO pw_config(db_name,db_value) VALUES('db_attachdir','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_attachnum','4');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpost[\'mpeg\']','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windpost[\'iframe\']','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtpostrvrc','10');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtpostmoney','5');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtjhrvrc','50');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtjhmoney','5');
INSERT INTO pw_config(db_name,db_value) VALUES('db_onlinelmt','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtdelrvrc','10');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtdelrprvrc','10');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtdelmoney','5');
INSERT INTO pw_config(db_name,db_value) VALUES('db_indexonline','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ifjump','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_regpopup','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ifonlinetime','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_showguest','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_recycle','3');
INSERT INTO pw_config(db_name,db_value) VALUES('db_indexshowbirth','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_obstart','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_indexlink','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_indexmqshare','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_defaultstyle','wind');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ckpath','/');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ckdomain','');
INSERT INTO pw_config(db_name,db_value) VALUES('db_windreadable','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_schpernum','10000');
INSERT INTO pw_config(db_name,db_value) VALUES('db_threademotion','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_todaypost','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_threadshowpost','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_indexfmlogo','2');
INSERT INTO pw_config(db_name,db_value) VALUES('db_autoimg','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_postmin','3');
INSERT INTO pw_config(db_name,db_value) VALUES('db_selcount','15');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ipfrom','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_dtreplymoney','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ipcheck','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_today','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_postallowtime','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_showonline','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ipban','');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ifcheck','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ads','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_hithour','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_upgrade','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_lp','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_topped','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_msgsound','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_datefm','Y-m-d H:i');
INSERT INTO pw_config(db_name,db_value) VALUES('db_lgck','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_bfn','index.php');
INSERT INTO pw_config(db_name,db_value) VALUES('db_hfn','bbs');
INSERT INTO pw_config(db_name,db_value) VALUES('db_cfn','c_index.php');
INSERT INTO pw_config(db_name,db_value) VALUES('db_showgroup',',3,4,5,16,');
INSERT INTO pw_config(db_name,db_value) VALUES('db_upload','1	200	180	20480');
INSERT INTO pw_config(db_name,db_value) VALUES('db_allowupload','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_adminshow','0');
INSERT INTO pw_config(db_name,db_value) values('db_maxpage','1000');
INSERT INTO pw_config(db_name,db_value) VALUES('db_maxmember','1000');
INSERT INTO pw_config(db_name,db_value) VALUES('db_titlemax','100');
INSERT INTO pw_config(db_name,db_value) VALUES('db_credits','{#db_credits}');
INSERT INTO pw_config(db_name,db_value) VALUES('db_savecheck','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_hash','?3@d#s$7^');
INSERT INTO pw_config(db_name,db_value) VALUES('db_maxresult','500');
INSERT INTO pw_config(db_name,db_value) VALUES('db_creditset','a:3:{s:4:"rvrc";a:6:{s:6:"Digest";i:20;s:4:"Post";i:10;s:5:"Reply";i:10;s:8:"Undigest";i:20;s:6:"Delete";i:10;s:8:"Deleterp";i:10;}s:5:"money";a:6:{s:6:"Digest";i:20;s:4:"Post";i:10;s:5:"Reply";i:10;s:8:"Undigest";i:20;s:6:"Delete";i:10;s:8:"Deleterp";i:10;}i:1;a:6:{s:6:"Digest";i:0;s:4:"Post";i:0;s:5:"Reply";i:0;s:8:"Undigest";i:0;s:6:"Delete";i:0;s:8:"Deleterp";i:0;}}');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ajaxsubject','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ajaxcontent','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_debug','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_gdcheck','0	0	0	0	0	0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_signmoney','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_signgroup',',5,6,7,16,8,9,10,11,12,13,14,15,');
INSERT INTO pw_config(db_name,db_value) VALUES('db_watermark','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_waterpos','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_waterimg','mark.gif');
INSERT INTO pw_config(db_name,db_value) VALUES('db_watertext','http://www.phpwind.net');
INSERT INTO pw_config(db_name,db_value) VALUES('db_waterfont','5');
INSERT INTO pw_config(db_name,db_value) VALUES('db_watercolor','#0000FF');
INSERT INTO pw_config(db_name,db_value) VALUES('db_waterpct','85');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ifgif','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_jpgquality','75');
INSERT INTO pw_config(db_name,db_value) VALUES('db_cvtimes','30');
INSERT INTO pw_config(db_name,db_value) VALUES('db_forumdir','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_attachurl','N');
INSERT INTO pw_config(db_name,db_value) VALUES('db_kijiji','1');
INSERT INTO pw_config(db_name,db_value) VALUES('db_adminreason','{#db_adminreason}');
INSERT INTO pw_config(db_name,db_value) VALUES('db_charset','{#db_charset}');
INSERT INTO pw_config(db_name,db_value) VALUES('db_cc','0');

INSERT INTO pw_config(db_name,db_value) VALUES('db_jsifopen','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_jsper','600');
INSERT INTO pw_config(db_name,db_value) VALUES('db_bindurl','');
INSERT INTO pw_config(db_name,db_value) VALUES('db_columns','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_head','');
INSERT INTO pw_config(db_name,db_value) VALUES('db_foot','');
INSERT INTO pw_config(db_name,db_value) VALUES('db_ptable','');

INSERT INTO pw_config(db_name,db_value) VALUES('ml_mailifopen','1');
INSERT INTO pw_config(db_name,db_value) VALUES('ml_mailmethod','1');
INSERT INTO pw_config(db_name,db_value) VALUES('ml_smtpauth','1');
INSERT INTO pw_config(db_name,db_value) VALUES('ml_smtpfrom','');
INSERT INTO pw_config(db_name,db_value) VALUES('ml_smtphost','');
INSERT INTO pw_config(db_name,db_value) VALUES('ml_smtppass','');
INSERT INTO pw_config(db_name,db_value) VALUES('ml_smtpport','');
INSERT INTO pw_config(db_name,db_value) VALUES('ml_smtpuser','');


INSERT INTO pw_config(db_name,db_value) VALUES('rg_whyregclose','{#rg_whyregclose}');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_allowregister','1');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_reg','1');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_regdetail','1');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_regsendemail','1');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_emailcheck','0');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_regminname','3');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_regmaxname','12');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_regsendmsg','0');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_regrvrc','10');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_regmoney','0');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_ifcheck','0');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_allowsameip','Y');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_welcomemsg','{#rg_welcomemsg}');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_rgpermit','{#rg_rgpermit}');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_rglower','0');
INSERT INTO pw_config(db_name,db_value) VALUES('rg_banname','{#rg_banname}');

INSERT INTO pw_config(db_name,db_value) VALUES('ol_onlinepay','0');
INSERT INTO pw_config(db_name,db_value) VALUES('ol_whycolse','{#ol_whycolse}');
INSERT INTO pw_config(db_name,db_value) VALUES('fc_shownum','7');

DROP TABLE IF EXISTS pw_credits;
CREATE TABLE pw_credits (
  cid tinyint(3) unsigned NOT NULL auto_increment,
  name char(30) NOT NULL default '',
  unit char(30) NOT NULL default '',
  description char(255) NOT NULL default '',
  PRIMARY KEY  (cid)
) TYPE=MyISAM;

INSERT INTO pw_credits VALUES ('1', '{#credit_name}','{#credit_unit}', '{#credit_descrip}');

DROP TABLE IF EXISTS pw_favors;
CREATE TABLE pw_favors (
  uid mediumint(8) unsigned NOT NULL default '0',
  tids text NOT NULL default '',
  type varchar(255) NOT NULL default '',
  KEY uid (uid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_forums;
CREATE TABLE pw_forums (
  fid smallint(6) unsigned NOT NULL auto_increment,
  fup smallint(6) unsigned NOT NULL default '0',
  ifsub tinyint(1) NOT NULL default '0',
  childid tinyint(1) NOT NULL default '0',
  type enum('category','forum','sub') NOT NULL default 'forum',
  logo char(100) NOT NULL default '',
  name char(50) NOT NULL default '',
  descrip char(255) NOT NULL default '',
  dirname char(15) NOT NULL default '',
  vieworder tinyint(3) NOT NULL default '0',
  forumadmin char(255) NOT NULL default '',
  fupadmin char(255) NOT NULL default '',
  style char(12) NOT NULL default '',
  across TINYINT NOT NULL default '0',
  allowhtm tinyint(1) NOT NULL default '0',
  allowhide tinyint(1) NOT NULL default '1',
  allowsell tinyint(1) NOT NULL default '1',
  allowreward tinyint(1) NOT NULL default '0',
  copyctrl tinyint(1) NOT NULL default '0',
  allowencode tinyint(1) NOT NULL default '1',
  password char(32) NOT NULL default '',
  viewsub tinyint(1) NOT NULL default '0',
  allowvisit char(120) NOT NULL default '',
  allowpost char(120) NOT NULL default '',
  allowrp char(120) NOT NULL default '',
  allowdownload char(120) NOT NULL default '',
  allowupload char(120) NOT NULL default '',
  f_type enum('forum','former','hidden','vote') NOT NULL default 'forum',
  f_check tinyint(1) unsigned NOT NULL default '0',
  t_type char(255) NOT NULL default '',
  cms tinyint(1) NOT NULL default '0',
  ifhide tinyint(1) NOT NULL default '1',
  showsub tinyint(1) NOT NULL default '0',

  PRIMARY KEY  (fid),
  KEY fup (fup),
  KEY type (ifsub,vieworder,fup)
) TYPE=MyISAM;

INSERT INTO pw_forums SET fid = 1, fup = 0, ifsub = 0, childid = 1, type = 'category', logo = '', name = 'PHPwind Board', descrip = '', vieworder = 0, forumadmin = '', style = '', across=0,allowhtm = 0, allowhide = 1, allowsell = 1, copyctrl = 0, allowencode = 1, password = '', viewsub = 0, allowvisit = '', allowpost = '', allowrp = '', allowdownload = '', allowupload = '', f_check = 0, t_type = '0', cms = 0, ifhide = 1;
INSERT INTO pw_forums SET fid = 2, fup = 1, ifsub = 0, childid = 0, type = 'forum', logo = '', name = '{#default_forum}', descrip = '', vieworder = 0, forumadmin = '', style = '', across=0,allowhtm = 0, allowhide = 1, allowsell = 1, copyctrl = 0, allowencode = 1, password = '', viewsub = 0, allowvisit = '', allowpost = '', allowrp = '', allowdownload = '', allowupload = '', f_type = 'forum',  f_check = 0, t_type = '0', cms = 0, ifhide = 1;
INSERT INTO pw_forums SET fid = 3, fup = 1, ifsub = 0, childid = 0, type = 'forum', logo = '', name = '{#default_recycle}', descrip = '', vieworder = 0, forumadmin = '', style = '', across=0,allowhtm = 0, allowhide = 1, allowsell = 1, copyctrl = 0, allowencode = 1, password = '', viewsub = 0, allowvisit = ',3,', allowpost = '', allowrp = '', allowdownload = '', allowupload = '', f_type = 'hidden', f_check = 0, t_type = '0', cms = 0, ifhide = 1;

DROP TABLE IF EXISTS pw_forumdata;
CREATE TABLE pw_forumdata (
  fid smallint(6) unsigned NOT NULL auto_increment,
  tpost mediumint(8) unsigned NOT NULL default '0',
  topic mediumint(8) unsigned NOT NULL default '0',
  article mediumint(8) unsigned NOT NULL default '0',
  subtopic mediumint(8) unsigned NOT NULL default '0',
  top1 smallint(6) unsigned NOT NULL default '0',
  top2 smallint(6) unsigned NOT NULL default '0',
  lastpost char(135) NOT NULL default '',
  PRIMARY KEY  (fid)
) TYPE=MyISAM;

INSERT INTO pw_forumdata SET fid = 1, tpost = 0, topic = 0, article = 0, subtopic = 0, top1 = 0, top2 = 0, lastpost = '';
INSERT INTO pw_forumdata SET fid = 2, tpost = 0, topic = 0, article = 0, subtopic = 0, top1 = 0, top2 = 0, lastpost = '';
INSERT INTO pw_forumdata SET fid = 3, tpost = 0, topic = 0, article = 0, subtopic = 0, top1 = 0, top2 = 0, lastpost = '';


DROP TABLE IF EXISTS pw_hack;
CREATE TABLE pw_hack (
  hk_name varchar(30) NOT NULL default '',
  hk_value text NOT NULL default '',
  decrip text NOT NULL default '',
  PRIMARY KEY  (hk_name)
) TYPE=MyISAM;

INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_A','a:1:{s:10:"rvrc_money";a:6:{i:0;s:4:"{#rvrc}";i:1;s:4:"{#money}";i:2;s:1:"2";i:3;s:1:"3";i:4;s:1:"1";i:5;i:1;}}','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_ddate','10','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_drate','10','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_num','10','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_open','1','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_per','5','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_rate','5','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_timelimit','2','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_virelimit','10','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_virement','1','');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES('bk_virerate','10','');

INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES ('currrate1', 'a:4:{s:4:"rvrc";i:100;s:5:"money";i:100;s:6:"credit";i:1;i:1;i:5;}', '');
INSERT INTO pw_hack(hk_name,hk_value,decrip) VALUES ('currrate2', '', '');


DROP TABLE IF EXISTS pw_ipstates;
CREATE TABLE pw_ipstates (
day CHAR( 10 ) NOT NULL default '',
month CHAR( 7 ) NOT NULL default '',
nums INT( 10 ) NOT NULL default '0',
PRIMARY KEY (day)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_keyword;
CREATE TABLE pw_keyword (
  kid int(10) NOT NULL auto_increment,
  kname varchar(20) NOT NULL default '',
  PRIMARY KEY  (kid),
  KEY kname (kname)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_ks;
CREATE TABLE pw_ks (
  kid int(10) NOT NULL default '0',
  tid int(10) NOT NULL default '0',
  KEY kid (kid),
  KEY tid (tid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_membercredit;
CREATE TABLE pw_membercredit (
  uid mediumint(8) unsigned NOT NULL default '0',
  cid tinyint(3) NOT NULL default '0',
  value mediumint(8) unsigned NOT NULL default '0',
  KEY uid (uid),
  KEY cid (cid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_memberdata;
CREATE TABLE pw_memberdata (
  uid mediumint(8) unsigned NOT NULL default '1',
  postnum int(10) unsigned NOT NULL default '0',
  digests smallint(6) NOT NULL default '0',
  rvrc int(10) NOT NULL default '0',
  money int(10) NOT NULL default '0',
  credit int(10) NOT NULL default '0',
  currency INT( 10 ) NOT NULL default '0',
  editor tinyint(1) NOT NULL default '0',
  lastvisit int(10) unsigned NOT NULL default '0',
  thisvisit int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  onlinetime int(10) unsigned NOT NULL default '0',
  monoltime int(10) unsigned NOT NULL default '0',
  todaypost smallint(6) unsigned NOT NULL default '0',
  monthpost smallint(6) unsigned NOT NULL default '0',
  uploadtime int(10) unsigned NOT NULL default '0',
  uploadnum smallint(6) unsigned NOT NULL default '0',
  onlineip char(30) NOT NULL default '',
  starttime int(10) unsigned NOT NULL default '0',
  postcheck char(16) NOT NULL default '',
  PRIMARY KEY uid (uid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_memberinfo;
CREATE TABLE pw_memberinfo (
  uid mediumint(8) unsigned NOT NULL default '1',
  adsips mediumtext NOT NULL default '',
  credit varchar(50) NOT NULL default '',
  deposit int(10) NOT NULL default '0',
  startdate int(10) NOT NULL default '0',
  ddeposit int(10) NOT NULL default '0',
  dstartdate int(10) NOT NULL default '0',
  regreason varchar(255) NOT NULL default '',
  readmsg mediumtext NOT NULL default '',
  delmsg mediumtext NOT NULL default '',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_members;
CREATE TABLE pw_members (
  uid mediumint(8) unsigned NOT NULL auto_increment,
  username varchar(20) NOT NULL default '',
  password varchar(40) NOT NULL default '',
  email varchar(60) NOT NULL default '',
  publicmail tinyint(1) NOT NULL default '0',
  groupid tinyint(3) NOT NULL default '-1',
  memberid tinyint(3) NOT NULL default '0',
  groups varchar(255) NOT NULL default '',
  icon varchar(100) NOT NULL default '',
  gender tinyint(1) NOT NULL default '0',
  regdate int(10) unsigned NOT NULL default '0',
  signature text NOT NULL default '',
  introduce text NOT NULL default '',
  oicq varchar(12) NOT NULL default '',
  icq varchar(12) NOT NULL default '',
  kf varchar(22) NOT NULL default '',
  msn varchar(35) NOT NULL default '',
  yahoo varchar(35) NOT NULL default '',
  site varchar(75) NOT NULL default '',
  location varchar(36) NOT NULL default '',
  honor varchar(30) NOT NULL default '',
  bday date NOT NULL default '0000-00-00',
  receivemail tinyint(1) NOT NULL default '0',
  lastaddrst varchar(255) NOT NULL default '',
  yz int(10) NOT NULL default '1',
  timedf varchar(5) NOT NULL default '',
  style varchar(12) NOT NULL default '',
  datefm varchar(15) NOT NULL default '',
  t_num tinyint(3) unsigned NOT NULL default '0',
  p_num tinyint(3) unsigned NOT NULL default '0',
  attach varchar(50) NOT NULL default '',
  hack varchar(255) NOT NULL default '0',
  signchange tinyint(1) NOT NULL default '0',
  newpm tinyint(1) NOT NULL default '0',
  banpm text NOT NULL default '',
  msggroups VARCHAR( 255 ) NOT NULL default '',
  showsign tinyint(1) UNSIGNED NOT NULL DEFAULT 1,
  medals varchar(255) NOT NULL default '',
  payemail varchar(60) NOT NULL default '',
  PRIMARY KEY  (uid),
  KEY username (username),
  KEY groupid (groupid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_msg;
CREATE TABLE pw_msg (
  mid int(10) unsigned NOT NULL auto_increment,
  touid mediumint(8) unsigned NOT NULL default '0',
  togroups varchar(80) NOT NULL default '',
  fromuid mediumint(8) unsigned NOT NULL default '0',
  username varchar(15) NOT NULL default '',
  type enum('rebox','sebox','public') NOT NULL default 'rebox',
  ifnew tinyint(1) NOT NULL default '0',
  title varchar(130) NOT NULL default '',
  mdate int(10) unsigned NOT NULL default '0',
  content text NOT NULL default '',
  PRIMARY KEY  (mid),
  KEY touid (touid),
  KEY fromuid (fromuid),
  KEY type (type)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_polls;
CREATE TABLE pw_polls (
  tid mediumint(8) unsigned NOT NULL,
  voteopts mediumtext NOT NULL default '',
  state tinyint(1) NOT NULL default '0',
  modifiable tinyint(1) NOT NULL default '0',
  previewable tinyint(1) NOT NULL default '0',
  timelimit int(3) NOT NULL default '0',
  PRIMARY KEY  (tid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_posts;
CREATE TABLE pw_posts (
  pid int(10) unsigned NOT NULL auto_increment,
  fid smallint(6) unsigned NOT NULL default '0',
  tid mediumint(8) unsigned NOT NULL default '0',
  aid text NOT NULL default '',
  author varchar(15) NOT NULL default '',
  authorid mediumint(8) unsigned NOT NULL default '0',
  icon tinyint(2) NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  subject varchar(100) NOT NULL default '',
  userip varchar(15) NOT NULL default '',
  ifsign tinyint(1) NOT NULL default '0',
  buy text NOT NULL default '',
  alterinfo varchar(50) NOT NULL default '',
  remindinfo varchar(150) NOT NULL default '',
  ipfrom varchar(30) NOT NULL default '',
  ifconvert tinyint(1) NOT NULL default '0',
  ifcheck tinyint(1) NOT NULL default '0',
  content mediumtext NOT NULL default '',
  ifmark varchar(255) NOT NULL default '',
  ifreward tinyint(1) NOT NULL default '0',
  ifshield tinyint(1) unsigned NOT NULL default '0',
  anonymous tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (pid),
  KEY fid (fid),
  KEY postdate (postdate),
  KEY tid (tid,postdate),
  KEY authorid (authorid),
  KEY ifcheck (ifcheck)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_report;
CREATE TABLE pw_report (
  id int(10) unsigned NOT NULL auto_increment,
  tid mediumint(8) unsigned NOT NULL default '0',
  pid int(10) unsigned NOT NULL default '0',
  uid mediumint(9) NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  reason char(255) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY type (type)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_schcache;
CREATE TABLE pw_schcache (
  sid int(10) unsigned NOT NULL auto_increment,
  schline varchar(255) NOT NULL default '',
  schtime int(10) unsigned NOT NULL default '0',
  total mediumint(8) unsigned NOT NULL default '0',
  schedid text NOT NULL default '',
  PRIMARY KEY  (sid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_sharelinks;
CREATE TABLE pw_sharelinks (
  sid smallint(6) unsigned NOT NULL auto_increment,
  threadorder tinyint(3) NOT NULL default '0',
  name char(100) NOT NULL default '',
  url char(100) NOT NULL default '',
  descrip char(200) NOT NULL default '',
  logo char(100) NOT NULL default '',
  PRIMARY KEY  (sid)
) TYPE=MyISAM;

INSERT INTO pw_sharelinks (threadorder ,name ,url ,descrip ,logo ) VALUES ('0', 'PHPwind Board', 'http://www.phpwind.net', '{#sharelinks}', 'logo.gif');

DROP TABLE IF EXISTS pw_smiles;
CREATE TABLE pw_smiles (
  id smallint(6) unsigned NOT NULL auto_increment,
  path varchar(20) NOT NULL default '',
  name varchar(20) NOT NULL default '',
  vieworder tinyint(2) NOT NULL default '0',
  type smallint(6) unsigned NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;

INSERT INTO pw_smiles (path,name,vieworder,type) VALUES ('default','{#smile}','1','0');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('smile.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('mrgreen.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('wink.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('redface.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('sad.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('cool.gif','0','1');
INSERT INTO pw_smiles (path,vieworder,type) VALUES ('crazy.gif','0','1');

DROP TABLE IF EXISTS pw_styles;
CREATE TABLE pw_styles (
  sid smallint(6) unsigned NOT NULL auto_increment,
  name char(50) NOT NULL default '',
  stylepath char(50) NOT NULL default '',
  tplpath char(50) NOT NULL default '',
  yeyestyle char(3) NOT NULL default '',
  bgcolor char(7) NOT NULL default '',
  linkcolor char(7) NOT NULL default '',
  tablecolor char(7) NOT NULL default '',
  tdcolor char(7) NOT NULL default '',
  tablewidth char(4) NOT NULL default '',
  mtablewidth char(4) NOT NULL default '',
  headcolor char(7) NOT NULL default '',
  headborder char(7) NOT NULL default '',
  headfontone char(7) NOT NULL default '',
  headfonttwo char(7) NOT NULL default '',
  cbgcolor char(7) NOT NULL default '',
  cbgborder char(7) NOT NULL default '',
  cbgfont char(7) NOT NULL default '',
  forumcolorone char(7) NOT NULL default '',
  forumcolortwo char(7) NOT NULL default '',
  PRIMARY KEY  (sid)
) TYPE=MyISAM;

INSERT INTO pw_styles VALUES (1, 'wind', 'wind', 'wind', '1', '#fff', '#2f5fa1', '#A6CBE7', '#D4EFF7', '98%', '98%', '#E0F0F9', '#dbecF4', '#004c7d', '#5599bb', '#f3f8ef', '#daebcd', '#659b28', '#ffffff', '#F4FBFF');

DROP TABLE IF EXISTS pw_threads;
CREATE TABLE pw_threads (
  tid mediumint(8) unsigned NOT NULL auto_increment,
  fid smallint(6) NOT NULL default '0',
  icon tinyint(2) NOT NULL default '0',
  titlefont char(15) NOT NULL default '',
  author char(15) NOT NULL default '',
  authorid mediumint(8) unsigned NOT NULL default '0',
  subject char(100) NOT NULL default '',
  toolinfo char(16) NOT NULL default '',
  toolfield int(10) unsigned NOT NULL default '0',
  ifcheck tinyint(1) NOT NULL default '0',
  type tinyint(2) NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  lastpost int(10) unsigned NOT NULL default '0',
  lastposter char(15) NOT NULL default '',
  hits int(10) unsigned NOT NULL default '0',
  replies int(10)  unsigned NOT NULL default '0',
  topped smallint(6) NOT NULL default '0',
  locked tinyint(1) NOT NULL default '0',
  digest tinyint(1) NOT NULL default '0',
  special tinyint(1) NOT NULL default '0',
  ifupload tinyint(1) NOT NULL default '0',
  ifmail tinyint(1) NOT NULL default '0',
  ifmark smallint(6) NOT NULL default '0',
  rewardinfo varchar(80) NOT NULL default '',
  ifshield tinyint(1) NOT NULL default '0',
  anonymous tinyint(1) NOT NULL default '0',
  ptable tinyint(3) NOT NULL default '0',
  PRIMARY KEY  (tid),
  KEY authorid (authorid),
  KEY postdate (postdate),
  KEY digest   (digest),
  KEY type     (type),
  KEY special  (special),
  KEY lastpost (fid,topped,lastpost)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_tmsgs;
CREATE TABLE pw_tmsgs (
  tid mediumint(8) unsigned NOT NULL default '0',
  aid text NOT NULL default '',
  userip varchar(15) NOT NULL default '',
  ifsign tinyint(1) NOT NULL default '0',
  buy text NOT NULL default '',
  ipfrom varchar(80) NOT NULL default '',
  alterinfo varchar(50) NOT NULL default '',
  remindinfo varchar(150) NOT NULL default '',
  ifconvert tinyint(1) NOT NULL default '1',
  content mediumtext NOT NULL default '',
  form varchar(30) NOT NULL default '',
  ifmark varchar(255) NOT NULL default '',
  c_from varchar(30) NOT NULL default '',
  PRIMARY KEY  (tid)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_usergroups;
CREATE TABLE pw_usergroups (
  gid smallint(5) unsigned NOT NULL auto_increment,
  gptype enum('default','member','system','special') NOT NULL default 'member',
  grouptitle varchar(60) NOT NULL default '',
  groupimg varchar(15) NOT NULL default '',
  grouppost int(10) NOT NULL default '0',
  maxmsg int(10) NOT NULL default '10',
  allowhide tinyint(1) NOT NULL default '0',
  allowread tinyint(1) NOT NULL default '0',
  allowportait tinyint(1) NOT NULL default '0',
  upload tinyint(1) NOT NULL default '0',
  allowrp tinyint(1) NOT NULL default '0',
  allowhonor tinyint(1) NOT NULL default '0',
  allowdelatc tinyint(1) NOT NULL default '0',
  allowpost tinyint(1) NOT NULL default '0',
  allownewvote tinyint(1) NOT NULL default '0',
  allowvote tinyint(1) NOT NULL default '0',
  allowactive tinyint(1) NOT NULL default '0',
  htmlcode tinyint(1) NOT NULL default '0',
  wysiwyg tinyint(1) unsigned NOT NULL default '1',
  allowhidden tinyint(1) NOT NULL default '0',
  allowencode tinyint(1) NOT NULL default '0',
  allowsell tinyint(1) NOT NULL default '0',
  allowsearch tinyint(1) NOT NULL default '0',
  allowmember tinyint(1) NOT NULL default '0',
  allowprofile tinyint(1) NOT NULL default '0',
  allowreport tinyint(1) NOT NULL default '0',
  allowmessege tinyint(1) NOT NULL default '0',
  allowsort tinyint(1) NOT NULL default '0',
  alloworder tinyint(1) NOT NULL default '0',
  allowupload tinyint(1) NOT NULL default '0',
  allowdownload tinyint(1) NOT NULL default '0',
  allowloadrvrc tinyint(1) NOT NULL default '0',
  allownum int(10) NOT NULL default '10',
  edittime mediumint(6) NOT NULL default '0',
  postpertime mediumint(6) NOT NULL default '0',
  searchtime mediumint(6) NOT NULL default '0',
  signnum mediumint(6) NOT NULL default '0',
  uploadmoney mediumint(6) NOT NULL default '0',
  mright text NOT NULL default '',
  ifdefault tinyint(1) unsigned NOT NULL default '1',
  allowadmincp tinyint(1) NOT NULL default '0',
  visithide tinyint(1) NOT NULL default '0',
  delatc tinyint(1) NOT NULL default '0',
  moveatc tinyint(1) NOT NULL default '0',
  copyatc tinyint(1) NOT NULL default '0',
  typeadmin tinyint(1) NOT NULL default '0',
  viewcheck tinyint(1) NOT NULL default '0',
  viewclose tinyint(1) NOT NULL default '0',
  attachper tinyint(1) NOT NULL default '0',
  delattach tinyint(1) NOT NULL default '0',
  viewip tinyint(1) NOT NULL default '0',
  markable tinyint(1) NOT NULL default '0',
  maxcredit smallint(6) NOT NULL default '0',
  credittype varchar(255) NOT NULL default '',
  creditlimit varchar(30) NOT NULL default '',
  banuser tinyint(1) NOT NULL default '0',
  bantype tinyint(1) NOT NULL default '0',
  banmax mediumint(6) NOT NULL default '0',
  viewhide tinyint(1) NOT NULL default '0',
  postpers tinyint(1) NOT NULL default '0',
  atccheck tinyint(1) NOT NULL default '0',
  replylock tinyint(1) NOT NULL default '0',
  modown tinyint(1) NOT NULL default '0',
  modother tinyint(1) NOT NULL default '0',
  deltpcs tinyint(1) NOT NULL default '0',
  sright text NOT NULL default '',
  PRIMARY KEY  (gid),
  KEY gptype (gptype),
  KEY grouppost (grouppost)
) TYPE=MyISAM;

INSERT INTO pw_usergroups SET gid = 1,  gptype = 'default',grouptitle = 'default',    groupimg = '8', grouppost = 0,     maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 1, upload = 0, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 1, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 0, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 100,  uploadmoney = 0, mright = 'show	1\n1\nviewipfrom	0\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize\n1\nmsggroup	0\n1\nmaxfavor	100\n1\nviewvote	0\n1\natccheck	1\n1\nmarkable	0\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb	Array|||', ifdefault = 1, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 2,  gptype = 'default',grouptitle = '{#level_1}', groupimg = '8', grouppost = 0,     maxmsg = 0,  allowhide = 0, allowread = 1, allowportait = 0, upload = 0, allowrp = 0, allowhonor = 0, allowdelatc = 0, allowpost = 0, allownewvote = 0, allowvote = 0, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 0, allowencode = 0, allowsell = 0, allowsearch = 0, allowmember = 0, allowprofile = 0, allowreport = 0, allowmessege = 0, allowsort = 0, alloworder = 0, allowupload = 0, allowdownload = 0, allowloadrvrc = 0, allownum = 0, edittime = 1, postpertime = 10, searchtime = 10,  signnum = 0,    uploadmoney = 0, mright = 'show	0\n1\nviewipfrom	0\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize	\n1\nmsggroup	0\n1\nmaxfavor	100\n1\nshowsign	\n1\nviewvote	0\n1\natccheck	0\n1\nmarkable	0\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb				', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 3,  gptype = 'system', grouptitle = '{#level_3}', groupimg = '3', grouppost = 0,     maxmsg = 100,allowhide = 1, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 1, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 1, htmlcode = 1, wysiwyg = 1, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 2, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 0, searchtime = 0,   signnum = 1000, uploadmoney = 0, mright = 'show	1\n1\nviewipfrom	1\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize	\n1\nmsggroup	1\n1\nmaxfavor	100\n1\nviewvote	1\n1\natccheck	1\n1\nmarkable	1\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb	300|-20|20|,rvrc,money,credit,1,', ifdefault = 0, allowadmincp = 1, visithide = 0, delatc = 1, moveatc = 1, copyatc = 1, typeadmin = 1, viewcheck = 1, viewclose = 1, attachper = 0, delattach = 1, viewip = 1, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 1, bantype = 1, banmax = 30, viewhide = 1, postpers = 1, atccheck = 0, replylock = 1, modown = 0, modother = 1, deltpcs = 1, sright = 'topped	3\n1\ntpctype	1\n1\ntpccheck	1';
INSERT INTO pw_usergroups SET gid = 4,  gptype = 'system', grouptitle = '{#level_4}', groupimg = '4', grouppost = 0,     maxmsg = 30, allowhide = 0, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 1, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 1, htmlcode = 1, wysiwyg = 1, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 2, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 10, searchtime = 10, signnum = 1000, uploadmoney = 0, mright = 'show	1\n1\nviewipfrom	1\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize	\n1\nmsggroup	1\n1\nmaxfavor	100\n1\nviewvote	1\n1\natccheck	1\n1\nmarkable	1\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb	100|-10|10|,rvrc,money,', ifdefault = 0, allowadmincp = 1, visithide = 0, delatc = 1, moveatc = 1, copyatc = 1, typeadmin = 1, viewcheck = 1, viewclose = 1, attachper = 0, delattach = 1, viewip = 1, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 1, bantype = 1, banmax = 20, viewhide = 1, postpers = 1, atccheck = 0, replylock = 1, modown = 0, modother = 1, deltpcs = 1, sright = 'topped	3\n1\ntpctype	1\n1\ntpccheck	1';
INSERT INTO pw_usergroups SET gid = 5,  gptype = 'system', grouptitle = '{#level_5}', groupimg = '5', grouppost = 0,     maxmsg = 40, allowhide = 0, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 1, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 1, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 2, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 10, searchtime = 10, signnum = 500,  uploadmoney = 0, mright = 'show	1\n1\nviewipfrom	0\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize	\n1\nmsggroup	0\n1\nmaxfavor	100\n1\nviewvote	0\n1\natccheck	1\n1\nmarkable	0\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb	50|-5|5|,money,', ifdefault = 0, allowadmincp = 1, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 2, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 10, viewhide = 0, postpers = 0, atccheck = 0, replylock = 1, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 6,  gptype = 'system', grouptitle = '{#level_6}', groupimg = '8', grouppost = 0,     maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 0, upload = 0, allowrp = 0, allowhonor = 0, allowdelatc = 0, allowpost = 0, allownewvote = 0, allowvote = 0, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 0, allowencode = 0, allowsell = 0, allowsearch = 0, allowmember = 0, allowprofile = 0, allowreport = 0, allowmessege = 0, allowsort = 0, alloworder = 1, allowupload = 0, allowdownload = 0, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 100,  uploadmoney = 0, mright = 'show	0\n1\nviewipfrom	0\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize	\n1\nmsggroup	0\n1\nmaxfavor	100\n1\nviewvote	0\n1\natccheck	0\n1\nmarkable	0\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb	|||', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = 'topped	0\n1\ntpctype	0\n1\ntpccheck	0';
INSERT INTO pw_usergroups SET gid = 7,  gptype = 'system', grouptitle = '{#level_7}', groupimg = '8', grouppost = 0,     maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 1, upload = 0, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 1, allowmember = 1, allowprofile = 0, allowreport = 0, allowmessege = 0, allowsort = 0, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 100,  uploadmoney = 0, mright = 'show	0\n1\nviewipfrom	0\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize	\n1\nmsggroup	0\n1\nmaxfavor	100\n1\nviewvote	0\n1\natccheck	0\n1\nmarkable	0\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb	|||', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = 'topped	0\n1\ntpctype	0\n1\ntpccheck	0';
INSERT INTO pw_usergroups SET gid = 8,  gptype = 'member', grouptitle = '{#level_8}', groupimg = '8', grouppost = 0,     maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 0, upload = 0, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 0, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 0, allowsell = 0, allowsearch = 1, allowmember = 0, allowprofile = 0, allowreport = 1, allowmessege = 1, allowsort = 0, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 0, searchtime = 10,  signnum = 30,   uploadmoney = 0, mright = 'show	0\n1\nviewipfrom	0\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize	3\n1\nmsggroup	0\n1\nmaxfavor	50\n1\nviewvote	0\n1\natccheck	1\n1\nmarkable	0\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb	|||', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 9,  gptype = 'member', grouptitle = '{#level_9}', groupimg = '9', grouppost = 100,   maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 0, upload = 0, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 0, allowsell = 0, allowsearch = 1, allowmember = 1, allowprofile = 0, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 0, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 10, searchtime = 10, signnum = 50,   uploadmoney = 0, mright = '0	\n1\nmarkable	0\n1\nmarkdb	|||\n1\nmaxfavor	100', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 10, gptype = 'member', grouptitle = '{#level_10}',groupimg = '10',grouppost = 300,   maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 0, upload = 0, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 1, allowmember = 1, allowprofile = 0, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 100,  uploadmoney = 0, mright = '0	\n1\nmarkable	0\n1\nmarkdb	|||\n1\nmaxfavor	100', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 11, gptype = 'member', grouptitle = '{#level_11}',groupimg = '11',grouppost = 600,   maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 1, allowmember = 1, allowprofile = 0, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 150,  uploadmoney = 0, mright = '0	\n1\nmarkable	0\n1\nmarkdb	|||\n1\nmaxfavor	100', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 12, gptype = 'member', grouptitle = '{#level_12}',groupimg = '12',grouppost = 1000,  maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 2, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 200,  uploadmoney = 0, mright = '0	\n1\nmarkable	0\n1\nmarkdb	|||\n1\nmaxfavor	100', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 13, gptype = 'member', grouptitle = '{#level_13}',groupimg = '13',grouppost = 5000,  maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 2, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 200,  uploadmoney = 0, mright = '0	\n1\nmarkable	0\n1\nmarkdb	|||\n1\nmaxfavor	100', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 14, gptype = 'member', grouptitle = '{#level_14}',groupimg = '14',grouppost = 10000, maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 0, allowencode = 0, allowsell = 0, allowsearch = 2, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 200,  uploadmoney = 0, mright = '0	\n1\nmarkable	0\n1\nmarkdb	|||\n1\nmaxfavor	100', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 15, gptype = 'member', grouptitle = '{#level_15}',groupimg = '14',grouppost = 50000, maxmsg = 10, allowhide = 0, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 0, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 2, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 200,  uploadmoney = 0, mright = '0	\n1\nmarkable	0\n1\nmarkdb	|||\n1\nmaxfavor	100', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = '';
INSERT INTO pw_usergroups SET gid = 16, gptype = 'special',grouptitle = '{#level_16}',groupimg = '5', grouppost = 0,     maxmsg = 20, allowhide = 0, allowread = 1, allowportait = 1, upload = 1, allowrp = 1, allowhonor = 1, allowdelatc = 1, allowpost = 1, allownewvote = 1, allowvote = 1, allowactive = 0, htmlcode = 0, wysiwyg = 0, allowhidden = 1, allowencode = 1, allowsell = 1, allowsearch = 1, allowmember = 1, allowprofile = 1, allowreport = 1, allowmessege = 1, allowsort = 1, alloworder = 1, allowupload = 1, allowdownload = 1, allowloadrvrc = 0, allownum = 50, edittime = 0, postpertime = 15, searchtime = 10, signnum = 200,  uploadmoney = 0, mright = 'show	1\n1\nviewipfrom	1\n1\nimgwidth	\n1\nimgheight	\n1\nfontsize	\n1\nmsggroup	1\n1\nmaxfavor	100\n1\nviewvote	0\n1\natccheck	1\n1\nmarkable	0\n1\npostlimit	\n1\nuploadmaxsize	0\n1\nuploadtype	\n1\nmarkdb	|||', ifdefault = 0, allowadmincp = 0, visithide = 0, delatc = 0, moveatc = 0, copyatc = 0, typeadmin = 0, viewcheck = 0, viewclose = 0, attachper = 0, delattach = 0, viewip = 0, markable = 0, maxcredit = 0, credittype = '', creditlimit = '', banuser = 0, bantype = 0, banmax = 0, viewhide = 0, postpers = 0, atccheck = 0, replylock = 0, modown = 0, modother = 0, deltpcs = 0, sright = 'topped	0\n1\ntpctype	0\n1\ntpccheck	0';

DROP TABLE IF EXISTS pw_wordfb;
CREATE TABLE pw_wordfb (
  id smallint(6) unsigned NOT NULL auto_increment,
  word varchar(100) NOT NULL default '',
  wordreplace varchar(100) NOT NULL default '',
  type TINYINT( 1 ) NOT NULL default '0',
  PRIMARY KEY  (id)
) TYPE=MyISAM;


INSERT INTO pw_config(db_name,db_value) VALUES('df_per','6');
INSERT INTO pw_config(db_name,db_value) VALUES('df_notice','1');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Nnum','5');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Nlen','35');
INSERT INTO pw_config(db_name,db_value) VALUES('df_allnew','1');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Anum','5');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Alen','35');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Tlen','35');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Llen','19');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Lnum','9');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Rlen','35');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Rnum','8');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Flen','35');
INSERT INTO pw_config(db_name,db_value) VALUES('df_Fnum','8');
INSERT INTO pw_config(db_name,db_value) VALUES('df_NEW', '0	4');
INSERT INTO pw_config(db_name,db_value) VALUES('df_CMS', '0	4');
INSERT INTO pw_config(db_name,db_value) VALUES('df_FID', '0	2');
INSERT INTO pw_config(db_name,db_value) VALUES('df_cache', 'postnum	0	1\n2\n1	9\n1\nrvrc	0	1\n2\n1	9\n1\nmoney	0	1\n2\n1	9\n1\ntodaypost	0	1\n2\n1	9\n1\ncredit	0	1\n2\n1	9\n1\ncustom	0	1\n2\n1	9\n1\nreply	0	1\n2\n1	5\n1\nhit	0	1\n2\n1	5\n1\ndigest	0	1\n2\n1	5');
INSERT INTO pw_config(db_name,db_value) VALUES('df_forumlogo', '');

INSERT INTO pw_config(db_name,db_value) VALUES('db_showcms','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_cmsifopen','0');
INSERT INTO pw_config(db_name,db_value) VALUES('db_whycmsclose','{#db_whycmsclose}');
INSERT INTO pw_config(db_name,db_value) VALUES('db_currencyname','{#db_currencyname}');
INSERT INTO pw_config(db_name,db_value) VALUES('db_rmbrate','10');
INSERT INTO pw_config(db_name,db_value) VALUES('db_rmblest','5');
INSERT INTO pw_config(db_name,db_value) VALUES('db_loadavg','3');
INSERT INTO pw_config(db_name,db_value) VALUES('db_anonymousname','{#anonymousname}');

DROP TABLE IF EXISTS pw_forumsextra;
CREATE TABLE pw_forumsextra (
fid SMALLINT NOT NULL default '0',
creditset TEXT NOT NULL default '',
forumset TEXT NOT NULL default '',
PRIMARY KEY ( fid )
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_tools;
CREATE TABLE pw_tools (
id SMALLINT NOT NULL auto_increment,
name VARCHAR( 20 ) NOT NULL default '',
descrip VARCHAR( 255 ) NOT NULL default '',
vieworder TINYINT( 3 ) NOT NULL default '0',
logo VARCHAR( 100 ) NOT NULL default '',
state TINYINT( 1 ) NOT NULL default '0',
price VARCHAR( 255 ) NOT NULL default '',
stock SMALLINT NOT NULL default '0',
conditions TEXT NOT NULL default '',
PRIMARY KEY ( id )
) TYPE=MyISAM;

INSERT INTO pw_tools VALUES (1, '{#tool_rvrc}', '{#tool_rvrc_inro}', 1, '1.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (2, '{#tool_credit}', '{#tool_credit_inro}', 2, '2.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (3, '{#tool_title}', '{#tool_title_inro}', 3, '3.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (4, '{#tool_top1}', '{#tool_top1_inro}', 4, '4.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (5, '{#tool_top2}', '{#tool_top2_inro}', 5, '5.gif', 0, '200', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (6, '{#tool_top3}', '{#tool_top3_inro}', 6, '6.gif', 0, '500', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (7, '{#tool_push}', '{#tool_push_inro}', 7, '7.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (8, '{#tool_username}', '{#tool_username_inro}', 8, '8.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (9, '{#tool_digest1}', '{#tool_digest1_inro}', 9, '9.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (10, '{#tool_digest2}', '{#tool_digest2_inro}', 10, '10.gif', 0, '200', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (11, '{#tool_lock}', '{#tool_lock_inro}', 11, '11.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');
INSERT INTO pw_tools VALUES (12, '{#tool_unlock}', '{#tool_unlock_inro}', 12, '12.gif', 0, '100', 100, 'a:1:{s:6:"credit";a:7:{s:7:"postnum";i:0;s:7:"digests";i:0;s:4:"rvrc";i:0;s:5:"money";i:0;s:6:"credit";i:0;i:1;i:0;i:2;i:0;}}');

DROP TABLE IF EXISTS pw_usertool;
CREATE TABLE pw_usertool (
uid mediumint(8) unsigned NOT NULL default '0',
toolid SMALLINT NOT NULL default '0',
nums SMALLINT NOT NULL default '0',
sellnums SMALLINT NOT NULL default '0',
sellprice VARCHAR( 255 ) NOT NULL default '',
KEY ( uid )
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_toollog;
CREATE TABLE pw_toollog (
id int(10) unsigned NOT NULL auto_increment,
type VARCHAR( 10 ) NOT NULL default '',
nums SMALLINT NOT NULL default '0',
money SMALLINT NOT NULL default '0',
descrip VARCHAR( 255 ) NOT NULL default '',
uid mediumint(8) unsigned NOT NULL default '0',
username varchar(15) NOT NULL default '', 
ip varchar(15) NOT NULL default '',
time int(10) NOT NULL default '0',
PRIMARY KEY ( id ),
KEY (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_recycle;
CREATE TABLE pw_recycle (
tid int(10) NOT NULL default '0',
fid smallint(6) unsigned NOT NULL default '0',
deltime int(10) unsigned NOT NULL default '0',
admin varchar(15) NOT NULL default '',
KEY (tid),
KEY (fid),
KEY (deltime)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_clientorder;
CREATE TABLE pw_clientorder (
  id int(11) NOT NULL auto_increment,
  order_no varchar(30) NOT NULL default '',
  uid mediumint(8) NOT NULL default '0',
  subject varchar(20) NOT NULL default '',
  body varchar(100) NOT NULL default '',
  price smallint(6) NOT NULL default '0',
  payemail varchar( 60 ) NOT NULL default '',
  number smallint(6) NOT NULL default '0',
  date int(10) NOT NULL default '0',
  state tinyint(1) NOT NULL default '0',
  descrip varchar(50) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY uid (uid),
  KEY order_no (order_no)
) TYPE=MyISAM;


DROP TABLE IF EXISTS pw_cnclass;
CREATE TABLE pw_cnclass (
  cid smallint(6) unsigned NOT NULL auto_increment,
  cname char(20) NOT NULL default '',
  PRIMARY KEY  (cid),
  KEY cname (cname)
) TYPE=MyISAM;

INSERT INTO pw_cnclass VALUES (1, '{#default_atc}');

DROP TABLE IF EXISTS pw_colonys;
CREATE TABLE pw_colonys (
  id smallint(6) unsigned NOT NULL auto_increment,
  classid smallint(6) NOT NULL default '0',
  cname varchar(20) NOT NULL default '',
  admin varchar(20) NOT NULL default '',
  members int(10) NOT NULL default '0',
  ifcheck tinyint(1) NOT NULL default '0',
  ifopen tinyint(1) NOT NULL default '0',
  albumopen tinyint(1) NOT NULL default '0',
  cmoney int(10) NOT NULL default '0',
  cnimg varchar(100) NOT NULL default '',
  createtime int(10) NOT NULL default '0',
  intomoney smallint(6) NOT NULL default '0',
  annouce varchar(255) NOT NULL default '',
  albumnum smallint(6) NOT NULL default '0',
  annoucesee smallint(6) NOT NULL default '0',
  descrip varchar(255) NOT NULL default '',
  level tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (id),
  UNIQUE KEY cname (cname),
  KEY admin (admin)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cmembers;
CREATE TABLE pw_cmembers (
  id mediumint(9) NOT NULL auto_increment,
  uid mediumint(9) unsigned NOT NULL default '0',
  username varchar(20) NOT NULL default '',
  realname varchar(20) NOT NULL default '',
  ifadmin tinyint(1) NOT NULL default '0',
  gender tinyint(1) NOT NULL default '0',
  tel varchar(15) NOT NULL default '',
  email varchar(50) NOT NULL default '',
  colonyid smallint(6) NOT NULL default '0',
  address varchar(255) NOT NULL default '',
  introduce varchar(255) NOT NULL default '',
  honor int(10) NOT NULL default '0',
  PRIMARY KEY  (id),
  KEY colonyid (colonyid),
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_argument;
CREATE TABLE pw_argument (
  tid smallint(6) unsigned NOT NULL auto_increment,
  tpcid smallint(6) NOT NULL default '0',
  gid smallint(6) unsigned NOT NULL default '0',
  author varchar(20) NOT NULL default '',
  authorid smallint(6) unsigned NOT NULL default '0',
  postdate int(10) unsigned NOT NULL default '0',
  lastpost int(10) NOT NULL default '0',
  subject varchar(50) NOT NULL default '',
  content text NOT NULL default '',
  PRIMARY KEY  (tid)
) TYPE=MyISAM;

INSERT INTO pw_hack VALUES('cn_open','1','');
INSERT INTO pw_hack VALUES('cn_remove','1','');
INSERT INTO pw_hack VALUES('cn_newcolony','1','');
INSERT INTO pw_hack VALUES('cn_createmoney','100','');
INSERT INTO pw_hack VALUES('cn_joinmoney','10','');
INSERT INTO pw_hack VALUES('cn_allowcreate','1','');
INSERT INTO pw_hack VALUES('cn_allowjoin','1','');
INSERT INTO pw_hack VALUES('cn_memberfull','50','');
INSERT INTO pw_hack VALUES('cn_imgsize','1048576','');
INSERT INTO pw_hack VALUES('cn_name','{#colony}','');
INSERT INTO pw_hack VALUES('cn_groups',',3,4,5,8,9,10,11,12,13,14,15,16,','');
INSERT INTO pw_hack VALUES('cn_imgwidth','200','');
INSERT INTO pw_hack VALUES('cn_imgheight','100','');
INSERT INTO pw_hack VALUES ('inv_open','0','');
INSERT INTO pw_hack VALUES ('inv_days','1','');
INSERT INTO pw_hack VALUES ('inv_limitdays','0','');
INSERT INTO pw_hack VALUES ('inv_costs','50','');
INSERT INTO pw_hack VALUES ('inv_credit','currency','');
INSERT INTO pw_hack VALUES ('inv_groups',',3,4,5,','');

DROP TABLE IF EXISTS pw_forumlog;
CREATE TABLE pw_forumlog (
  id int(11) NOT NULL auto_increment,
  type varchar(10) NOT NULL default '',
  username1 varchar(30) NOT NULL default '',
  username2 varchar(30) NOT NULL default '',
  field1 varchar(30) NOT NULL default '',
  field2 varchar(30) NOT NULL default '',
  field3 varchar(255) NOT NULL default '',
  descrip text NOT NULL default '',
  timestamp int(10) NOT NULL default '0',
  ip varchar(20) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY type (type),
  KEY username1 (username1),
  KEY username2 (username2)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_adminlog;
CREATE TABLE pw_adminlog(
  id int(11) NOT NULL auto_increment,
  type varchar(10) NOT NULL default '',
  username1 varchar(30) NOT NULL default '',
  username2 varchar(30) NOT NULL default '',
  field1 varchar(30) NOT NULL default '',
  field2 varchar(30) NOT NULL default '',
  field3 varchar(255) NOT NULL default '',
  descrip text NOT NULL default '',
  timestamp int(10) NOT NULL default '0',
  ip varchar(20) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY type (type),
  KEY username1 (username1),
  KEY username2 (username2)
) TYPE=MyISAM;

INSERT INTO pw_hack VALUES('md_groups',',3,','');
INSERT INTO pw_hack VALUES('md_ifmsg','1','');
INSERT INTO pw_hack VALUES('md_ifopen','0','');

DROP TABLE IF EXISTS pw_medalinfo;
CREATE TABLE pw_medalinfo (
  id tinyint(4) NOT NULL auto_increment,
  name varchar(40) NOT NULL default '',
  intro varchar(255) NOT NULL default '',
  picurl varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
) TYPE=MyISAM;

INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (1, '{#medalname_1}', '{#medaldesc_1}!','1.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (2, '{#medalname_2}', '{#medaldesc_2}', '2.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (3, '{#medalname_3}', '{#medaldesc_3}', '3.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (4, '{#medalname_4}', '{#medaldesc_4}', '4.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (5, '{#medalname_5}', '{#medaldesc_5}', '5.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (6, '{#medalname_6}', '{#medaldesc_6}', '6.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (7, '{#medalname_7}', '{#medaldesc_7}', '7.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (8, '{#medalname_8}', '{#medaldesc_8}', '8.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (9, '{#medalname_9}', '{#medaldesc_9}', '9.gif');
INSERT INTO pw_medalinfo(id,name,intro,picurl) VALUES (10,'{#medalname_10}','{#medaldesc_10}','10.gif');

DROP TABLE IF EXISTS pw_medalslogs;
CREATE TABLE pw_medalslogs (
  id int(10) NOT NULL auto_increment,
  awardee varchar(40) NOT NULL default '',
  awarder varchar(40) NOT NULL default '',
  awardtime int(10) NOT NULL default '0',
  timelimit tinyint(2) NOT NULL default '0',
  state tinyint(1) NOT NULL default '0',
  level tinyint(4) NOT NULL default '0',
  action tinyint(1) NOT NULL default '0',
  why varchar(255) NOT NULL default '',
  PRIMARY KEY (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_modules;
CREATE TABLE pw_modules (
  id int(10) unsigned NOT NULL auto_increment,
  type tinyint(4) NOT NULL default '0',
  targets varchar(50) NOT NULL default '',
  varname varchar(20) NOT NULL default '',
  state tinyint(1) NOT NULL default '0',
  vieworder tinyint(4) default '0',
  title varchar(255) NOT NULL default '',
  config text NOT NULL,
  PRIMARY KEY  (id),
  KEY type (type),
  KEY vieworder (vieworder),
  KEY state (state)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_activity;
CREATE TABLE pw_activity (
  tid mediumint(8) unsigned NOT NULL,
  subject varchar(80) NOT NULL default '',
  admin mediumint(8) NOT NULL,
  starttime int(10) NOT NULL default '0',
  endtime int(10) NOT NULL default '0',
  location varchar(20) NOT NULL default '',
  num smallint(6) NOT NULL,
  sexneed tinyint(1) NOT NULL default '0',
  costs int(10) NOT NULL default '0',
  deadline int(10) NOT NULL default '0',
  PRIMARY KEY (tid),
  KEY admin (admin)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_actmember;
CREATE TABLE pw_actmember (
  id mediumint(8) unsigned NOT NULL auto_increment,
  actid mediumint(8) NOT NULL,
  winduid mediumint(8) NOT NULL,
  state tinyint(1) NOT NULL default '0',
  applydate int(10) NOT NULL default '0',
  contact varchar(20) NOT NULL default '',
  message varchar(80) NOT NULL default '',
  PRIMARY KEY (id),
  KEY actid (actid),
  KEY winduid (winduid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_customfield;
CREATE TABLE pw_customfield (
  id smallint(6) NOT NULL auto_increment,
  title varchar(50) NOT NULL,
  maxlen smallint(6) NOT NULL default '0',
  vieworder smallint(6) NOT NULL default '0',
  type tinyint(1) NOT NULL default '0',
  state tinyint(1) NOT NULL default '0',
  required tinyint(1) NOT NULL default '0',
  viewinread tinyint(1) NOT NULL default '0',
  editable tinyint(1) NOT NULL default '0',
  descrip varchar(255) NOT NULL default '', 
  viewright varchar(255) NOT NULL default '',
  options text NOT NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_extragroups;
CREATE TABLE pw_extragroups (
  uid mediumint(9) NOT NULL,
  gid smallint(6) NOT NULL,
  togid smallint(6) NOT NULL default '0',
  startdate int(10) NOT NULL,
  days smallint(6) NOT NULL,
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_friends;
CREATE TABLE pw_friends (
  uid mediumint(8) NOT NULL default '0',
  friendid mediumint(8) NOT NULL default '0',
  joindate int(10) NOT NULL default '0',
  descrip varchar(255) NOT NULL default '',
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_help;
CREATE TABLE pw_help (
  id smallint(6) unsigned NOT NULL auto_increment,
  title varchar(80) NOT NULL default '',
  content text NOT NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_invitecode;
CREATE TABLE pw_invitecode (
  id mediumint(8) unsigned NOT NULL auto_increment,
  invcode varchar(40) NOT NULL default '',
  uid mediumint(8) NOT NULL,
  receiver varchar(20) NOT NULL default '',
  createtime int(10) NOT NULL default '0',
  usetime int(10) NOT NULL default '0',
  ifused tinyint(1) NOT NULL default '0',
  PRIMARY KEY (id),
  KEY invcode (invcode),
  KEY uid (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_plan;
CREATE TABLE pw_plan (
  id smallint(6) unsigned NOT NULL auto_increment,
  subject varchar(80) NOT NULL,
  month varchar(2) NOT NULL,
  week varchar(1) NOT NULL,
  day varchar(2) NOT NULL,
  hour varchar(80) NOT NULL,
  usetime int(10) NOT NULL,
  nexttime int(10) NOT NULL,
  ifsave tinyint(1) NOT NULL,
  ifopen tinyint(1) NOT NULL,
  filename varchar(80) NOT NULL,
  config text NOT NULL,
  PRIMARY KEY (id)
) TYPE=MyISAM;

INSERT INTO pw_plan VALUES (1, '{#plan_1}', '*', '6', '1', '*', 0, 0, 1, 0, 'cleardata', '');
INSERT INTO pw_plan VALUES (2, '{#plan_2}', '*', '*', '20', '30', 0, 0, 0, 0, 'freeban', '');
INSERT INTO pw_plan VALUES (3, '{#plan_3}', '*', '*', '0', '*', 0, 0, 1, 0, 'birthday', '');
INSERT INTO pw_plan VALUES (4, '{#plan_4}', '*', '1', '12', '30', 0, 0, 1, 0, 'rewardmsg', '');
INSERT INTO pw_plan VALUES (5, '{#plan_5}', '15', '*', '2', '*', 0, 0, 0, 0, 'team', 'a:3:{s:10:\"credittype\";s:6:\"credit\";s:6:\"credit\";a:3:{i:3;s:3:\"100\";i:4;s:2:\"60\";i:5;s:2:\"50\";}s:6:\"groups\";s:5:\"3,4,5\";}');
INSERT INTO pw_plan VALUES (6, '{#plan_6}', '16', '*', '18', '30', 0, 0, 0, 0, 'medal', '');
INSERT INTO pw_plan VALUES (7, '{#plan_7}', '*', '*', '22', '*', 0, 0, 0, 0, 'extragroup', '');

DROP TABLE IF EXISTS pw_advert;
CREATE TABLE pw_advert (
  id int(10) unsigned NOT NULL auto_increment,
  type tinyint(4) NOT NULL default '0',
  targets varchar(50) NOT NULL,
  varname varchar(20) default '1',
  state tinyint(1) NOT NULL,
  vieworder tinyint(4) default '0',
  title varchar(255) default NULL,
  config text NOT NULL,
  PRIMARY KEY  (id),
  KEY type (type),
  KEY vieworder (vieworder),
  KEY state (state)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cmsmodule;
CREATE TABLE pw_cmsmodule (
  id int(10) unsigned NOT NULL auto_increment,
  type tinyint(4) NOT NULL default '0',
  targets varchar(50) NOT NULL,
  varname varchar(20) default '1',
  state tinyint(1) NOT NULL,
  vieworder tinyint(4) default '0',
  title varchar(255) default NULL,
  config text NOT NULL,
  PRIMARY KEY  (id),
  KEY type (type),
  KEY vieworder (vieworder),
  KEY state (state)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_singleright;
CREATE TABLE pw_singleright (
  uid mediumint(8) unsigned NOT NULL,
  visit varchar(80) NOT NULL default '',
  post varchar(80) NOT NULL default '',
  reply varchar(80) NOT NULL default '',
  PRIMARY KEY  (uid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cnalbum;
CREATE TABLE pw_cnalbum ( 
  aid mediumint(8) NOT NULL auto_increment,
  aname varchar(50) NOT NULL,
  aintro varchar(200) NOT NULL,
  atype smallint(4) NOT NULL,
  cyid mediumint(8) NOT NULL,
  uid mediumint(8) NOT NULL,
  username varchar(50) NOT NULL,
  photonum smallint(6) NOT NULL,
  lastphoto varchar(100) NOT NULL,
  crtime int(10) NOT NULL,
  PRIMARY KEY (aid),
  KEY cyid (cyid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cnclass;
CREATE TABLE pw_cnclass (
  cid smallint(6) unsigned NOT NULL auto_increment,
  cname char(20) NOT NULL default '',
  PRIMARY KEY  (cid),
  KEY cname (cname)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_cnphoto;
CREATE TABLE pw_cnphoto (
  pid smallint(8) NOT NULL auto_increment,
  aid smallint(8) NOT NULL,
  pname varchar(50) NOT NULL,
  pintro varchar(200) NOT NULL,
  path varchar(200) NOT NULL,
  uploader varchar(50) NOT NULL,
  uptime int(10) NOT NULL,
  hits smallint(6) NOT NULL,
  PRIMARY KEY (pid),
  KEY aid (aid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_pidtmp;
CREATE TABLE pw_pidtmp (
  pid INT NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (pid)
) TYPE=MyISAM;

DROP TABLE IF EXISTS pw_nav;
CREATE TABLE pw_nav (
  nid smallint(4) NOT NULL auto_increment,
  title char(50) NOT NULL,
  style char(50) NOT NULL,
  link char(100) NOT NULL,
  alt char(50) NOT NULL,
  pos char(10) NOT NULL,
  target tinyint(1) NOT NULL,
  view smallint(4) NOT NULL,
  PRIMARY KEY (nid)
) TYPE=MyISAM;