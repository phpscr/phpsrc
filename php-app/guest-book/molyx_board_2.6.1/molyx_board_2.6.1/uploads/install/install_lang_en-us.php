<?php
#**************************************************************************#
#   MolyX2
#   ------------------------------------------------------
#   copyright (c) 2004-2006 HOGE Software.
#   official forum : http://molyx.com
#   license : MolyX License, http://molyx.com/license
#   MolyX2 is free software. You can redistribute this file and/or modify
#   it under the terms of MolyX License. If you do not accept the Terms
#   and Conditions stated in MolyX License, please do not redistribute
#   this file.Please visit http://molyx.com/license periodically to review
#   the Terms and Conditions, or contact HOGE Software.
#**************************************************************************#
$a_lang = array(
	'install' => array()
);

$a_lang['install']['versiontoolow'] = 'MolyX Board can not be installed because the PHP version of your server is too old ( 4.0.6 or older ).<br>Please contact your host service provider for help.';
$a_lang['install']['script'] = 'Installer';
$a_lang['install']['studios'] = 'MolyX Magic Experience';
$a_lang['install']['customsupport'] = 'Powered by Nanjing HOGE Software Co., Ltd.';
$a_lang['install']['next'] = 'Next';
$a_lang['install']['back'] = 'Back';
$a_lang['install']['error'] = 'Error';
$a_lang['install']['login'] = 'Login';
$a_lang['install']['adminpwtooshort'] = 'Admin password is too short. Please make your password at lease 5 charactors!';
$a_lang['install']['passwordnowmatch'] = 'Passwords entered not match, please roll back and retry.';
$a_lang['install']['chmoderror'] = 'Config file writing failed!<br>Please modifiy the permission of file ../includes/config.php to CHMOD 0755, CHMOD 0775 or CHMOD 0777 (or 0777 if not sure) and refresh this page. Also, you can download a script generated config file and upload it to the includes directory on your server. Please click the back button of your browser to choose other options.';
$a_lang['install']['mysqlerror'] = 'Database "%s" creation failure: <b>%s</b>';
$a_lang['install']['connecterror'] = 'Can not connect to your database. Please confirm the username and password of MySQL user.<br>MySQL Returns: <b>%s</b>';
$a_lang['install']['queryerror'] = 'Query failed: <i>%s</i><br><br>MySQL Returns: <b>%s</b>';
$a_lang['install']['installation'] = 'Installer';
$a_lang['install']['licagreement'] = 'License Agreement';
$a_lang['install']['licread'] = 'I have completely READ and AGREED all terms above.';
$a_lang['install']['licaccept'] = 'You should read and agree to the terms of License Agreement to continue installation.';
$a_lang['install']['canwrite'] = 'Writable';
$a_lang['install']['cannotwrite'] = 'Unwritable';
$a_lang['install']['dircontinue'] = 'Install directory check finished successfully. You can go to the next step and continue installation.';
$a_lang['install']['dirnotcontinue'] = 'One or more directories required are unwritable. Please modify the permission of the directories mentioned to CHMOD 0777, or otherwise the installation can not continue. Please modify the permissions and refresh this page again.';
$a_lang['install']['directory'] = 'Directory ';
$a_lang['install']['file'] = 'File ';
$a_lang['install']['mysqldata'] = 'Database Options';
$a_lang['install']['entermysqldata'] = 'Please select the corresponding options to your database environment in the form below.';
$a_lang['install']['dbtype'] = 'Database Type';
$a_lang['install']['dbhost'] = 'Database Host/IP';
$a_lang['install']['dbuser'] = 'Username';
$a_lang['install']['dbpass'] = 'Password';
$a_lang['install']['dbcharset'] = 'Default Character Code';
$a_lang['install']['set_dbcharset'] = 'The version of your MySQL is later than 4.1, and not using UTF-8 as the default database character encoding. Please select the connection method to the character encoding corresponds to your database environment. ATTENTION: Incorrect settings may cause the installation failed!';
$a_lang['install']['current_char'] = 'Current Encoding';
$a_lang['install']['recommend_char'] = 'Recommand To Set';
$a_lang['install']['selectdb'] = 'Database Selection';
$a_lang['install']['choosedb'] = 'Please select one of the existing databases to install your forum, or enter a new database name at the bottom of the form below. If a new database name is entered, the installer will try to create it.';
$a_lang['install']['existingdb'] = 'Existing';
$a_lang['install']['usefield'] = 'Select one from below';
$a_lang['install']['orname'] = '<b>OR</b> enter a new database name:';
$a_lang['install']['chooseprefix'] = 'Prefix Of MySQL Tables';
$a_lang['install']['tablelist'] = 'Database <b>"%s"</b> contains existing tables below:';
$a_lang['install']['enterprefix'] = 'Please enter a prefix for the tables of your forum. Entering a prefix for tables allows you to install multiple copies of MolyX Board in one database.</p>Table Prefix:';
$a_lang['install']['dontchange'] = 'Do not modify if not sure';
$a_lang['install']['deleteexisting'] = 'Overwrite (delete) existing tables';
$a_lang['install']['importstyles'] = 'Ready to install default style settings. Please make sure the file MolyX-style.xml exists in your install directory.';
$a_lang['install']['styleexists'] = 'Style setting file MolyX-style.xml exists, and ready to continue.';
$a_lang['install']['stylenotexists'] = 'Style setting file MolyX-style.xml does not exist, installation can not continue.';
$a_lang['install']['createadmin'] = 'Create Administrator Account';
$a_lang['install']['sitesettings'] = 'Forum General Settings';
$a_lang['install']['hometitle'] = '<b>Homepage Title</b>';
$a_lang['install']['homeurl'] = '<b>Homepage URL</b>';
$a_lang['install']['bbtitle'] = '<b>Forum Title</b>';
$a_lang['install']['bburl'] = '<b>Forum URL</b>';
$a_lang['install']['emailreceived'] = '<b>Email address for receiving</b>';
$a_lang['install']['emailsend'] = '<b>Email address for sending</b>';
$a_lang['install']['cookiedomain'] = '<b>Cookie Domain</b><br>The domain on which you want the cookie to have effect. If you want this to affect all of "yourhost.com" rather than just "forums.yourhost.com", enter ".yourhost.com" here (note the 2 dots!). You can leave this setting blank.';
$a_lang['install']['cookieprefix'] = '<b>Cookie Prefix</b><br>Different prefixes allow you to install multiple copies of MolyX Board under one domain.';
$a_lang['install']['cookiepath'] = '<b>Cookie Path</b><br>The path that the cookie is saved to. If you run more than one board on the same domain, it will be necessary to set this to the individual directories of the forums. Otherwise, just leave it as /';
$a_lang['install']['cantexec'] = 'Upgrade failed. (Incorrect version)';
$a_lang['install']['updatenotes'] = 'Please check to make sure the version of your upgrade script is correct and retry later.<br />Incorrect upgrade script may damage your database.';
$a_lang['install']['defaultstyle'] = 'Default Style';
$a_lang['install']['xmlparseerror'] = 'XML Parse Error';
$a_lang['install']['atlines'] = 'at line ';
$a_lang['install']['styleerror'] = 'Invalid style file.';
$a_lang['install']['username'] = 'Username';
$a_lang['install']['email'] = 'Email';
$a_lang['install']['password'] = 'Password';
$a_lang['install']['twopassword'] = 'Confirm Password';
$a_lang['install']['completing'] = 'Installation Completed!';
$a_lang['install']['completingtxt'] = 'Installer will create a config file for future use on your server, and requires WRITE permission of <b>../includes/</b> directory. Please make sure you have the correct permission setting for this directory, then continue to the last step. If your using Linux or Unix OS on your server, CHMOD this directory to 0777.<br><br><font color="darkred"><b>NOTE:</b></font> You can also download a copy of config file that script generated, and upload it to the <b>../includes/</b> directory on your server. Once you\'ve done that, the installation is manually finished, and this script will no longer work.<br><br><a href="install.php?action=generate_config&amp;hostname=%s&amp;user=%s&amp;pass=%s&amp;db=%s&amp;prefix=%s&amp;usepconnect=%s&amp;lang=en-us&amp;superadmin=%s">Download Config File</a>';
$a_lang['install']['denied'] = 'Forum Already Installed';
$a_lang['install']['deniedtxt'] = 'A copy of MolyX Board is already install on your server! If you are trying to upgrade, please click <a href="%s">HERE</a> to continue.<br><br>If you are trying to install a new copy of MolyX Board, please delete the file <b>../includes/config.php</b> and retry.';
$a_lang['install']['selectupdate'] = 'Please select an option to upgrade.';
$a_lang['install']['updateinfo'] = 'Upgrade Info';
$a_lang['install']['enteradminname'] = 'Please entering correct administrator\'s info!!';
$a_lang['install']['reqver'] = 'Version Required';
$a_lang['install']['nowupdatetable'] = 'Upgrading table';
$a_lang['install']['newver'] = 'Upgraded Version';
$a_lang['install']['author'] = 'Author';
$a_lang['install']['date'] = 'Date';
$a_lang['install']['executable'] = 'Execute?';
$a_lang['install']['notes'] = 'NOTE';
$a_lang['install']['yes'] = 'YES';
$a_lang['install']['no'] = 'NO';
$a_lang['install']['na'] = 'N/A';
$a_lang['install']['infotxt'] = '
                      <br>
                      This script will guide you through the following procedures and help you to install a copy of MolyX Board on your server. Please check if you have the correct settings or required info below before conitnuing:</p>
                    <ul>
                      <li>MySQL Host / IP</li>
                      <li>MySQL Username & Password</li>
                      <li>MySQL Database Name (Required if unable to create a new one)</li>
					  <li>Permission of directory and sub directories of ./CACHE are 0777 (UNIX)</li>
					  <li>Permission of directory and sub directories of ./DATA are 0777 (UNIX)</li>
					  <li>Permission of file ./includes/config.php is 0777 (UNIX)</li>
                    </ul>
					You can write the above settings to file includes/config.php and upload the file to your server. To modify file and directory permissions, please login through FTP to do so. If you are not sure about the settings and requirements above, please contact your host service provider for instructions.
					<br>
                    <br>
					If any error occurs during installation, please visit <a href="http://www.molyx.com/" target="_blank">MolyX Support Forum</a> to report your problem and look for answers.
					<br>
                    <br>
					Click next to continue installation of MolyX Board.';
$a_lang['install']['finished'] = '
<b>Installation Completed!</b><br>
<br>
If you are using UNIX OS on your server, please modify the permission of the directory <b>../includes/</b> to CHMOD 0755.<br>
<br>
Installation completed successfully. You can visit your forum through the following links:<br>
<ul>
<li><a target="_blank" href="%s">MolyX Board Forum Home</a></li>
<li><a target="_blank" href="%s">MolyX Board Admin Control Pannel</a></li>
<li><a target="_blank" href="http://www.molyx.com/">MolyX Board Support Forum</a></li>
</ul>';

$a_lang['install']['updatefinished'] = '
<b>Upgrade Completed!</b><br>
<br>
<b>Please rebuild all the caches and styles in admin control pannel after upgrading, or otherwise your forum might not work properly.<br><br>Please delete the install folder for security consideration!</b><br>
<br>
You can visit your forum through the following links:<br>
<ul>
<li><a target="_blank" href="%s">MolyX Board Forum Home</a></li>
<li><a target="_blank" href="%s">MolyX Board Admin Control Pannel</a></li>
<li><a target="_blank" href="http://www.molyx.com/">MolyX Board Support Forum</a></li>
</ul>';
$a_lang['install']['noupdates'] = '
<b>Upgrade Info</b><br>
<br>
No script avaliable<br>
Please visit <a href="http://www.molyx.com/">MolyX Official</a> for more scripts.';
$a_lang['mysql']['award']['bbsbulid'] = "Miracle Of Construction";
$a_lang['mysql']['award']['bbsbulidexplain'] = "Our forum wouldn\\'t be as much pleased as it is now without the great work you\\'ve done. We appreciate your construction for this forum with medal and respect!";
$a_lang['mysql']['award']['goodlove'] = "Best Couples";
$a_lang['mysql']['award']['goodlovexplain'] = "What can we say, may god bless your love for ever!";
$a_lang['mysql']['award']['excellencemod'] = "Outstanding Moderator";
$a_lang['mysql']['award']['excellencemodepl'] = "This medal is an award for those who worked hard in their forum, created a passionate environment for forum members. Please keep your work, every one appreciates that!";
$a_lang['mysql']['award']['bestproduce'] = "Power Of Inpiration";
$a_lang['mysql']['award']['bestproducepl'] = "Inspirational, creative, constructive are the best words ever to descript the wisdom within your mind. We appreciate your willing of sharing your wisdom!";
$a_lang['mysql']['award']['pourgenius'] = "The Pumper";
$a_lang['mysql']['award']['pourgeniusepl'] = "You gave Noah\\'s Ark a chance to save the world! Greet pumpers never run out of words, and that\\'s this medal awarded for.";
$a_lang['mysql']['award']['pasteimgmaster'] = "The Color Of Mystique";
$a_lang['mysql']['award']['pasteimgmasterepl'] = "The graphics you\\'ve shared with people in this forum are highly welcomed and respected. We appreciate the pictures you\\'ve brought and wishing to see more in the future!";
$a_lang['mysql']['award']['bbsencourge'] = "Energy Of Pioneer";
$a_lang['mysql']['award']['bbsencourgepl'] = "Everybody saw your informing work, willing to try their best and follow your past. We appreciate your braveness and passion!";
$a_lang['mysql']['award']['bbskavass'] = "Knight Of Order";
$a_lang['mysql']['award']['bbskavassepl'] = "Because of your carefulness and justice, this forum is running in a good order. We appreciate your work fighting with evil, and wish you can cover our forum under your wings for ever.";
$a_lang['mysql']['award']['chatgenius'] = "Supersonic Tougue";
$a_lang['mysql']['award']['chatgeniusepl'] = "Yeah, yeah, that\\'s what a forum is built for, right? Go chat to everyone, know more about each other, and hoping you could be friends.";
$a_lang['mysql']['award']['beautyuse'] = "Crystal Of Beauty";
$a_lang['mysql']['award']['beautyusepl'] = "Oh! Hot girls are always what a male wants day by day, dream by dream. Let\\'s go, boys, show her what you got!";
$a_lang['mysql']['award']['handsomeuse'] = "Spark Of Grace";
$a_lang['mysql']['award']['handsomeusepl'] = "Yeah, that\\'s a handsome guy, really! Ladies, please behave yourselves...";
$a_lang['mysql']['award']['bestflack'] = "Best Advertiser";
$a_lang['mysql']['award']['bestflackepl'] = "Our forums can\\'t be bustling with activities with out you, the great advertiser. We appreciate your work on telling more people about this forum, and wishing you will bring more crowd to this place!";
$a_lang['mysql']['award']['humormaster'] = "Master Of Gladness";
$a_lang['mysql']['award']['humormasterepl'] = "Because of you, people laughs and having happy time always. We appreciate your sense of humor, and whish to not have too many wrinkles on our face!";
$a_lang['mysql']['banfilter']['admin'] = 'Administrator';
$a_lang['mysql']['banfilter']['mod'] = 'Moderator';
$a_lang['mysql']['bbcode']['tagforthread'] = 'This tag makes a thread easy to locate.';
$a_lang['mysql']['bbcode']['tagforpost'] = 'This tag makes a post easy to locate.';
$a_lang['mysql']['bbcode']['viewthread'] = 'View Thread';
$a_lang['mysql']['bbcode']['viewpost'] = 'View Post';
$a_lang['mysql']['bbcode']['tagforallmovie'] = 'tag allows to post several formats of video files.';
$a_lang['mysql']['bbcode']['tagforallrmmovie'] = 'tag allows to post RM format files.';
$a_lang['mysql']['bbcode']['clickdown'] = 'Click To Download';
$a_lang['mysql']['bbcode']['tagforallmusic'] = 'tag allows to post serveral formats of audio files.';
$a_lang['mysql']['cron']['cleanout'] = 'Hourly Cleanup';
$a_lang['mysql']['cron']['cleanoutdesc'] = 'Cleans expired activations, sessions and search index.';
$a_lang['mysql']['cron']['rebuildstats'] = 'Daily Forum Stat';
$a_lang['mysql']['cron']['rebuildstatsdesc'] = 'Rebuild stats of your forum daily.';
$a_lang['mysql']['cron']['dailycleanout'] = 'Daily Cleanup';
$a_lang['mysql']['cron']['dailycleanoutdesc'] = 'Clean invalid subscriptions.';
$a_lang['mysql']['cron']['birthdays'] = 'Birthday List';
$a_lang['mysql']['cron']['birthdaysdesc'] = 'Built daily cache of members having birthday today.';
$a_lang['mysql']['cron']['announcements'] = 'Update Announcement';
$a_lang['mysql']['cron']['announcementsdesc'] = 'Clean expired announcements.';
$a_lang['mysql']['cron']['renameupload'] = 'Rename Upload Directory';
$a_lang['mysql']['cron']['renameuploaddesc'] = 'Rename the upload diretory periodically to avoid pirate linking.';
$a_lang['mysql']['cron']['promotion'] = 'User Promotion';
$a_lang['mysql']['cron']['promotiondesc'] = 'Promote users according to the strategies hourly.';
$a_lang['mysql']['cron']['bankloancheck'] = 'Loan Check & Execution';
$a_lang['mysql']['cron']['bankloancheckdesc'] = 'Check the users to see if their refund time expire. Also make the necessary actions such as execution, item sales or personal bankruptcy according to the situation.';
$a_lang['mysql']['cron']['bankpayinterest'] = 'Pay Deposit Interest';
$a_lang['mysql']['cron']['bankpayinterestdesc'] = 'Pay the interest of healty deposit daily.';

$a_lang['mysql']['cron']['award_promotion'] = 'Honor Award';
$a_lang['mysql']['cron']['award_promotiondesc'] = 'Automatically award users if they met the requirements of certain honors.';
$a_lang['mysql']['cron']['rebuildglobalstick'] = "Rebuilt Global Sticky";
$a_lang['mysql']['cron']['rebuildglobalstickdesc'] = 'Rebuilt cache of global sticky threads and related info.';
$a_lang['mysql']['cron']['cleantoday'] = 'Post Count Reset';
$a_lang['mysql']['cron']['cleantodaydesc'] = "Rest the post counter at 0 o\\'clock daily.";
$a_lang['mysql']['cron']['refreshjs'] = 'Update JS Code';
$a_lang['mysql']['cron']['refreshjsdesc'] = 'Update the cache of JavaScript settings and info.';
$a_lang['mysql']['faq']['faqtitle_1'] = "Profile Maintenance";
$a_lang['mysql']['faq']['faqdesc_1'] = "This section contains lots of information about maintaining your own user profile, avatars and browsing options etc.";
$a_lang['mysql']['faq']['faqtitle_2'] = "General Forum Usage";
$a_lang['mysql']['faq']['faqtitle_3'] = "Reading and Posting Messages";
$a_lang['mysql']['faq']['faqtitle_4'] = "Why should I register?";
$a_lang['mysql']['faq']['faqtext_4'] = "In order to fully utilize the abilities of this forum, the administrator will probably require that you register as a member. Registration is free, and allows you to do the following:<br /><br /><ul><br />	<li>Post new threads</li><br /><li>Reply to other peoples\\' threads</li><br />	<li>Edit your posts</li><br />	<li>Send private messages to other members</li><br />	<li>Download attachments in posts</li><br /></ul><br /><br />To register, you will need to specify a username and password, and a valid email address. Entering your email address will not leave you open to \\'spam\\', as you can choose to hide your email address, and messages sent to you via email do not reveal your address to the sender in any case. (To verify this, you can try sending an email message to another user.) The administrator may have configured the forum to send you the final part of the registration process by email, so ensure that the email address you provide is valid and working.";
$a_lang['mysql']['faq']['faqtitle_5'] = "Does this forum use cookies?";
$a_lang['mysql']['faq']['faqtext_5'] = "The use of cookies on this forum is optional, but may enhance your experience of the site. Cookies are used to enable functions such as showing whether or not a thread or forum you are viewing has new posts since your last visit, or to automatically log you back in when you return to the site after being away for a while.<br /><p>When you register, you will be given the option to \\'Automatically login when you return to the site\\'. This will store your username and password in a cookie, stored on your computer. If you are using a shared computer, such as in a library, school or internet cafe, or if you have reason to not trust any other users that might use this computer, we do not recommend that you enable this.</p><br /><p>This forum also gives you the option to use a cookie to track your current session, ensuring that you remain logged-on while browsing the site. If you choose not to store this information in a cookie, the session data will be sent through each link you click. Choosing not to use a cookie may cause problems if your computer\\'s connection to the Internet uses a proxy-server and you share links with other users behind the same proxy. If in doubt, we recommend that you select \\'yes\\' to enable the cookie. After you have registered, you may alter your cookie options at any time by changing the settings on this page.</p>";
$a_lang['mysql']['faq']['faqtitle_6'] = "What are smilies?";
$a_lang['mysql']['faq']['faqtext_6'] = "<p>Smilies are small graphical icons which you can insert into your messages to convey an emotion or feeling, such as a joke or embarrassment. For example, if you entered a sarcastic comment, rather than type \\'that was a joke\\', you could simply insert the \\'wink\\' smilie.</p><br /><p>If you have used email or internet chat systems, you will probably be familiar with the concept of smilies already. Certain combinations of text characters are converted into smilies. For example, <strong>:)</strong> is converted into a smiling face. To understand smilie codes, tilt your head to the left and look at the text: you will see that <strong>:)</strong> represents two eyes and a smiling mouth.</p><br /><p>For a list of the smilies currently used by this forum, click <a href=\"misc.php?do=icon\"><b>HERE</b></a>.</p><br /><p>On occasions, you may want to prevent the text in your message being converted into smilies. You will see a checkbox which you can select when you make a new post, which will allow you to \\'Disable Smilies\\'.</p>";
$a_lang['mysql']['faq']['faqtitle_7'] = "How do I clear my cookies?";
$a_lang['mysql']['faq']['faqtext_7'] = "You may clear all your cookies by clicking <a href=\"login.php?do=logout\">HERE</a>. If you return to the main index page via the link provided and you are still logged in, you may have to remove your cookies manually.<br /><br /><br /><br />Here are the instructions for Internet Explorer 5 on Windows:<br /><br /><ol><br />	<li>Close all Internet Explorer windows.</li><br />	<li>Click the \"Start\" button.</li><br />	<li>Move up to \"Find\" and click \"Files or Folders\" from the menu that appears.</li><br />	<li>In the new window that appears, in the \"containing text\" field, type in the domain name of the forum without the \"http://\" or \"www.\" part. For example, if the forums\\' address was \"http://www.molyx.com/forum/index.php\", I would type in \"molyx.com\" (without the quotes)</li><br />	<li>In the \"look in\" box, type in \"C:\\Windows\\Cookies\\\" (without the quotes) and press \"Find Now\"</li><br />	<li>After it has finished searching, highlight all files (click a file, then press CTRL+A) and delete them (press the \"delete\" key or SHIFT+DEL)</li><br /></ol><br /><br />Your cookies should now be removed. You may want to restart the computer and revisit the forums to be sure.";
$a_lang['mysql']['faq']['faqtitle_8'] = "How can I change the information in my profile?";
$a_lang['mysql']['faq']['faqtext_8'] = "It is your responsibility to keep the information in your profile up-to-date. You should especially ensure that your email address is kept current. You can alter any of the fields in your profile, except your username. Once you have registered your username, it becomes yours for life. In extreme circumstances, you may request that the administrator change your username, but he or she will require a very good reason to do so.<br /><br /><br /><br />Edit your profile <a href=\"usercp.php?do=editprofile\">HERE</a>.";
$a_lang['mysql']['faq']['faqtitle_9'] = "What is the signature for?";
$a_lang['mysql']['faq']['faqtext_9'] = "After you have registered, you may set your signature. This is a piece of text that you would like to be inserted at the end of all your posts, a little like having headed note paper.<br /><br /><br /><br />If the administrator has enabled signatures, then you will have the option to include your signature on any posts you make. If you have specified a signature, then the forum will automatically append your signature to any messages you post. You can disable signatures on a per-post basis by un-ticking the \\'Show Signature\\' checkbox when you compose your message.<br /><br /><br /><br />You may turn the signature on and off on posts you have already made by editing the post and altering the state of the \\'Show Signature\\' option.<br /><br /><br /><br />You can set your signature by editing your profile.";
$a_lang['mysql']['faq']['faqtitle_10'] = "I lost my password, what can I do?";
$a_lang['mysql']['faq']['faqtext_10'] = "If you forget your password, you can click on the \\'Forgotten Your Password\\' link on any page that requires you to fill in your password.<br /><br /><br />This will bring up a page where you should enter your registered email address, and an email will be sent to that address instantly, with instructions for resetting your password.";
$a_lang['mysql']['faq']['faqtitle_11'] = "How do I add a custom status to my profile?";
$a_lang['mysql']['faq']['faqtext_11'] = "If the administrator has enabled custom status titles, then you can specify the text you want to display by editing your profile and entering the text you want in the Custom User Text field.";
$a_lang['mysql']['faq']['faqtitle_12'] = "How do I get a picture under my username?";
$a_lang['mysql']['faq']['faqtext_12'] = "These small images are called Avatars. They are displayed below your username on all posts that you make. There are two kinds of avatars: those provided by the administrator and those that you upload yourself.<br /><br /><br /><br />If the administrator has provided a set of avatars, and avatars are enabled, you may select an avatar that best describes your personality.<br /><br /><br /><br />The administrator may also have enabled custom avatars, which allows you to upload an avatar image from your computer.";
$a_lang['mysql']['faq']['faqtitle_13'] = "Can I search the forum?";
$a_lang['mysql']['faq']['faqtext_13'] = "<p>You can search for posts based on username, word(s) in the post or just in the subject, by date, and only in particular forums.</p><br /><p>To access the search feature, click on the \"search\" link at the top of most pages.</p><br /><p>You can search any forum that you have permission to search - you will not be allowed to search through private forums unless the administrator has given you the necessary security rights to do so.</p>";
$a_lang['mysql']['faq']['faqtitle_14'] = "Can I send email to other members?";
$a_lang['mysql']['faq']['faqtext_14'] = "<p>Yes! To send an email to another member, you can either locate the member you want to contact on the <a href=\"memberlist.php?\"><b>Member List</b></a>, or click the \"Email\" button on any post made by that member.</p><br /><p>This will usually open a page that contains a form where you can enter your message. When you have finished typing your message, press the [send email] button and your message will be sent instantly. Note that for privacy reasons, the email address of the recipient is not revealed to you during this process.</p><br /><p>Note that if you can not find an email button or link for a member, it either means that the administrator has disabled email functions for this forum, or that the member in question has specified that they do not wish to receive email from other members of the forum.</p><br /><p>Another useful email function is the ability to send a friend a link to a thread you think they may find interesting. Whenever you view a thread, you will find a link somewhere on the page which will allow you to send a brief message to anyone you like. Your referrerid is added to the link you send, so if your friend registers on this forum as a result of viewing the link you sent to them, your referrals total will automatically be credited!</p><br /><p>Registered members may also be able to send messages to other members of this forum using the <a href=\"private.php?\">private messaging</a> system.</p>";
$a_lang['mysql']['faq']['faqtitle_15'] = "What is Private Messaging?";
$a_lang['mysql']['faq']['faqtext_15'] = "<p>If the administrator has enabled the <a href=\"private.php?\"><b>Private Messaging</b></a> system, registered members may send each other private messages.</p><br /><p><b>Sending Private Messages</b></p><br /><p>Private messages work a little like email, but are limited to registered members of this forum. You may be able to include BBCode, smilies and images in private messages that you send.</p><br /><p>You may send a private message to a member of this forum by clicking the \\'<a href=\"private.php?do=newpm\"><b>Send A Private Message</b></a>\\' link in the private messaging section of your user control panel, or by clicking the \"PM\" button in a member\\'s posts.</p><br /><p>When you send a message, you have the option to save a copy of the message in your Outbox Items folder.</p><br /><p><b>Private Message Folders</b></p><br /><p>By default, you will have two folders in your private message area. These are the Inbox and the Outbox Items folders.</p><br /><p>The <a href=\"private.php?\"><b>Inbox</b></a> folder contains any new messages you receive, and allows you to view all the messages you have received, along with the name of the person who sent the message to you, and the date and time the message was sent.</p><br /><p>The <a href=\"private.php?folderid=-1\"><b>Sent Items</b></a> folder contains a copy of any messages that you have sent, where you have specified that you wish to keep a copy of the message for future reference.</p><br /><p>You may create additional folders for your messages by clicking the \\'<a href=\"private.php?do=editfolders\"><b>Edit Folders</b></a>\\' link.</p><br /><p>Each folder has a set of message controls which allow you to select messages, then either move them to one of your custom folders, or delete them completely. You may also have a button which allows you to forward multiple messages from a folder to another member of the forum.</p><br /><p>You will need to periodically delete old messages, as the administrator has probably set a limit to the number of private messages you can have in your folders. If you exceed the number of messages specified by the administrator, you will not be able to send or receive new messages until you delete some old messages.</p><br /><p>Whenever you read a message, you will have the option to reply to the message, or forward that message to another member of the forum. You may also have the ability to forward that message to multiple members of your buddy list.</p><br /><p><b>Message Tracking</b></p><br /><p>When you send a new private message, you may have the option to request a read-receipt for that message. This will allow you to check whether or not a message has been read by its recipient by viewing the Private <a href=\"private.php?do=showtrack\"><b>Message Tracking</b></a> page.</p><br /><p>This page is divided into two sections: unread messages and read messages.</p><br /><p>The <strong>unread messages</strong> section shows all messages that you have sent with a read-receipt request, that have not yet been read by their recipient. The time of the last activity of the message recipient is also shown. Messages in this section can be cancelled if you decide that their contents are no longer relevant, or for any other reason. Cancelled messages can also be restored to active status if the administrator has enabled this feature.</p><br /><p>The <strong>read messages</strong> section shows all messages you have sent with a receipt request that have been read and acknowledged by their recipient. The time that the message was read is also shown.</p><br /><p>You may choose to end the tracking on any message you choose by selecting the message and clicking the [end tracking] button.</p>";
$a_lang['mysql']['faq']['faqtitle_16'] = "How do I use the Member List?";
$a_lang['mysql']['faq']['faqtext_16'] = "<p>The <a href=\"memberlist.php?\"><b>member list</b></a> contains a complete list of all the registered members of this forum. You can view the member list ordered alphabetically by username, by the date that the member joined the forum, or by the number of posts the members have made.</p><br /><p>The member list also has a <a href=\"memberlist.php?do=search\"><b>search function</b></a>, which allows you to quickly locate members according to categories you specify, such as searching for all members who have joined in the past week etc.</p>";
$a_lang['mysql']['faq']['faqtitle_17'] = "What are announcements?";
$a_lang['mysql']['faq']['faqtext_17'] = "<p>Announcements are special messages posted by the administrator or moderators. They are designed to be a simple one-way communication with the users. If you wish to discuss announcements, you will have to create a new thread in the forum, since you cannot reply to announcements.</p>";
$a_lang['mysql']['faq']['faqtitle_18'] = "Are there any special codes/tags I can use to markup my posts?";
$a_lang['mysql']['faq']['faqtext_18'] = "<p>For the most part, your posts will contain plain text, but on occasions, you may want to emphasize certain words or phrases by making them (for example) bold or italic.</p><br /><p>Depending on the rules of the forum, you may use HTML code to produce these effects. However, more often than not, the administrator will have disabled HTML code, and opted instead to use BBCode: a special set of tags which you can use to produce the most popular text-effects. BBCode has the advantage that it is very simple to use, and is immune to malicious javascripts and page layout disruption.</p><br /><p>You may also find that the administrator has enabled <strong>smilies</strong>, which allow you to use small icons to convey emotion, and the <strong>[img]</strong> code, which allows you to add pictures to your message.</p><br /><p>For more information about BBCode, click <a href=\"misc.php?do=bbcode\"><b>HERE</b></a>.</p>";
$a_lang['mysql']['faq']['faqtitle_19'] = "How do I create and vote in polls?";
$a_lang['mysql']['faq']['faqtext_19'] = "<p>You may notice that some threads on this forum also include a section where you can vote on an issue or question. These threads are called \\'polls\\' and this is how to create them:</p><br /><p><b>Creating a new poll</b></p><br /><p>When you post a new thread, you may have the option to also create a poll.</p><br /><p>This function allows you to ask a question and specify a number of possible responses. Other members will then be able to vote for the response they wish, and the results of the voting will be displayed in the thread.</p><br /><p>An example poll might be:</p><br /><blockquote><br />  <p>What is your favorite color?</p><br />  <ol><br />    <li>Red</li><br />    <li>Blue</li><br />    <li>Yellow</li><br />    <li>Green</li><br />    <li>Sky-blue pink with yellow spots</li><br />  </ol><br /></blockquote><br /><p>To create a poll when you post a new thread, simply click the \\'Yes! post a poll\\' checkbox at the bottom of the page, and set the number of possible responses you want to include.</p><br /><p>When you click the submit button, you will be taken to the poll creation page, where you can specify the question and the list of responses you want to include.</p><br /><p>You may also want to specify a time limit for the poll, so that (for example) it only stays open for voting for a week.</p><br /><b>Voting in and viewing a poll</b><br /><p>To vote in a poll, simply select which option you want to vote for, and click the [Vote!] button. You may view the current results for a poll before you vote by clicking the \\'View Results\\' link. Voting in a poll is entirely optional. You may vote for any of the available options, or cast no vote at all.</p><br /><p>Generally, once you have voted in a poll, you will not be able to change your vote later, so place your vote carefully!</p>";
$a_lang['mysql']['faq']['faqtitle_20'] = "What is Multi-Quote?";
$a_lang['mysql']['faq']['faqtext_20'] = "<p>Multi-Quote is a quotation system designed to quote several different posts in a reply.</p><br /><p>Simple press the \\'Multi-Quote\\' button to enable the quotation to a certain post, and you can quote more than one post as you desired and reply them at once..</p>";
$a_lang['mysql']['faq']['faqtitle_21'] = "What are Attachments?";
$a_lang['mysql']['faq']['faqtext_21'] = "<p>The administrator may allow you to use the attachment feature of this forum, which gives you the ability to attach files of certain types to your posts. This could be an image, a text document, a zip file etc. There will be a limit to the file size of any attachments you make, as the forums should not be used as an extension of your hard disk!</p><br /><p>To attach a file to a new post, simply click the [Browse] button at the bottom of the post composition page, and locate the file that you want to attach from your local hard drive.</p><br /><p>After posting, the attachment will show up in the body of your message. To view the contents of the attachment (if it is not already displayed) simply click the filename link that appears next to the attachment icon.</p>";
$a_lang['mysql']['faq']['faqtitle_22'] = "Can I edit my own posts?";
$a_lang['mysql']['faq']['faqtext_22'] = "<p>If you have registered, you will be able to edit and delete your posts. Note that the administrator can disable this ability as he desires. Your ability to edit your posts may also be time-limited, depending on how the administrator has set up the forum.</p><br /><p>To edit or delete your posts, click the \"Edit\" button by the post you want to edit. If your post was the first in the thread, then deleting the post may result in the removal of the entire thread.</p><br /><p>After you have made your modifications, a note may appear, which notifies other users that you have edited your post. Administrators and moderators may also edit your messages but this note may not appear when they do so.</p>";
$a_lang['mysql']['faq']['faqtitle_23'] = "What are message icons?";
$a_lang['mysql']['faq']['faqtext_23'] = "<p>The administrator may have enabled message icons for new threads, posts and private messages. Message icons allow you to specify a small icon to accompany your posting, which is used to convey the emotion or content of a post at a glance. If you do not see a list of message icons when you are composing your message or post, the administrator has disabled the option.</p>";
$a_lang['mysql']['faq']['faqtitle_24'] = "What are Moderators?";
$a_lang['mysql']['faq']['faqtext_24'] = "<p>Moderators oversee specific forums. They generally have the ability to edit and delete posts, move threads, and perform other manipulations. Becoming a moderator for a specific forum is usually rewarded to users who are particularly helpful and knowledgeable in the subject of the forum they are moderating.</p>";
$a_lang['mysql']['faq']['faqtitle_25'] = "Why have some of the words in my post been blanked";
$a_lang['mysql']['faq']['faqtext_25'] = "<p>Certain words may have been censored by the administrator. If your posts contain any censored words, they will be blanked-out like this: *****.</p><br /><p>The same words are censored for all users, and censoring is done by a computer simply searching and replacing words. It is in no way \\'intelligent\\'.</p>";
$a_lang['mysql']['faq']['faqtitle_26'] = "Extended Credit System";
$a_lang['mysql']['faq']['faqtitle_27'] = "What is Extended Credit?";
$a_lang['mysql']['faq']['faqtext_27'] = "Extended Credit is a set of status defined by administrator to show how much work or distribution a member has done to the forum and other members. The credit point will be automatically adjusted while making \\'New Thread\\', \\'New Reply\\', \\'Upload/Download Attachments\\', \\'Having Quintessences\\', \\'Honor Awarded\\' or \\'Sending PM\\' operations. The amount of points to adjust depends on how the administrator set the strategy, which means it might be different with forums to forums. This is not only an honor for members of this forum, also limits some certain operations to be allowed or not.";
$a_lang['mysql']['faq']['faqtitle_28'] = "What credits do we have currently?";
$a_lang['mysql']['faq']['faqtext_28'] = "The administrator has set the following credits:<br /><br /><#show_credit#><br /><br />NOTE<br />: If the number of credits are positive, the members will get some credit INCREASED after the corresponding operations are done. Otherwise, if the numbers are negative, members will get their credit DECREASED by finishing those operations. If the number does not exists, then it won\\'t make any change after the operations.<br /><br />If the credit point of some certain credit are low enough compare with the minimum set of this credit, then the corresponding operation will be forbidden to the member.";
$a_lang['mysql']['faq']['faqtitle_29'] = "Why can\\'t I make some of the operations while others can?";
$a_lang['mysql']['faq']['faqtext_29'] = "If one or more of your credit points are lower than the requirement of a certain operation, you may be forbidden to make those actions due to the extended credit system settings. To avoid this happens, try to raise (or lower) your credit points according to the credit strategies defined by administrators until they reach the minimum requirements of the operations you desired.";

$a_lang['mysql']['forum']['testsort'] = "Test Category";
$a_lang['mysql']['forum']['testforum'] = "Test Forum";
$a_lang['mysql']['forum']['testdesc'] = "Default testing forum.";

$a_lang['mysql']['league']['molyxteam'] = "魔力论坛 MolyX Board";
$a_lang['mysql']['league']['molyxdesc'] = "魔力论坛魔力体验 - MolyX Magic Experience";
$a_lang['mysql']['league']['hogesoftitle'] = "厚建软件 HOGE Software";
$a_lang['mysql']['league']['hogesofdesc'] = "创新凸显实力 - 我们的产品体系 LivCMS / MolyX Board / LivSearch / LivBlog";

$a_lang['mysql']['setting']['uploadurl'] = "Upload URL";
$a_lang['mysql']['setting']['uploadurldesc'] = "The URL where uploaded files are stored.";
$a_lang['mysql']['setting']['uploadfolder'] = "Upload Path";
$a_lang['mysql']['setting']['uploadfolderdesc'] = "The path on your server where uploaded files are stored.";
$a_lang['mysql']['setting']['remoteattach'] = "Remote Attachment URL";
$a_lang['mysql']['setting']['remoteattachdesc'] = "If this options is set, this forum will work as a MIRROR site of your master website. NOTE: Turn off the scheduled task which renames the upload directory name of the upload directory on your master forum if this feature is turned on.";
$a_lang['mysql']['setting']['headerredirect'] = "Method Of Page Redirection";
$a_lang['mysql']['setting']['headerredirectdesc'] = "This options is used to determine how the redirect page works. Please select the proper option according to your OS.";
$a_lang['mysql']['setting']['headerredirectextra'] = "location=location (*nix OS)\nrefresh=Refresh (Windows OS)\nhtml=HTML META (If neither of above worked...)";
$a_lang['mysql']['setting']['removeredirect'] = "Remove Redirection Message Pages?";
$a_lang['mysql']['setting']['removeredirectdesc'] = "Enabling this option will remove the update pages that are displayed after a user makes a post, starts a search, etc. These pages provide assurance to the user that their information has been processed by the forum. Disabling these pages will save you bandwidth and may lessen the load of the forum on your server.";
$a_lang['mysql']['setting']['numberformat'] = "Number Format";
$a_lang['mysql']['setting']['numberformatdesc'] = "You can specify a thousand-separator to format long numbers.";
$a_lang['mysql']['setting']['numberformatextra'] = "none=Unformatted\n,=,\n.=.";
$a_lang['mysql']['setting']['gdversion'] = "GD Version";
$a_lang['mysql']['setting']['gdversiondesc'] = "Version of GD installed on your server. You can find the version by searching for \\'GD\\' on your phpinfo() output. GD is required for generating attachment thumbnails and image verification.";
$a_lang['mysql']['setting']['isajax'] = "Use AJAX For Global Environment?";
$a_lang['mysql']['setting']['isajaxdesc'] = "Enabling AJAX feature will reduce your server pressure effectively due to the less page refreshing and server transfers.";
$a_lang['mysql']['setting']['default_lang'] = "Default Language Setting";
$a_lang['mysql']['setting']['default_langdesc'] = "The default language package your forum system will use as the UI for visiters.";
$a_lang['mysql']['setting']['showtoday'] = "Display Daily Post Count?";
$a_lang['mysql']['setting']['showtodaydesc'] = "A counter of daily post will be displayed in the forum pages if this option is enabled.";
$a_lang['mysql']['setting']['miibeian'] = "MII Record";
$a_lang['mysql']['setting']['miibeiandesc'] = "This is the record ID of the registration in MII (Ministry of Information Industry) China.<br />Visit: <a href=\\'http://www.mii.gov.cn\\' target=\\'_blank\\'>MII China</a> for details";
$a_lang['mysql']['setting']['cookietimeout'] = "Time-Out for Cookie [Minutes]";
$a_lang['mysql']['setting']['loadlimit'] = "*NIX Server Load Limit";
$a_lang['mysql']['setting']['loadlimitdesc'] = "MolyX Board can read the overall load of the server on certain *NIX setups (including Linux). This allows your forum to determine the load on the server and processor, and to turn away further users if the load becomes too high.<br />Leaving it blank for no limit.";
$a_lang['mysql']['setting']['bbtitle'] = "Forum Title";
$a_lang['mysql']['setting']['bbtitledesc'] = "Name of your forum. This appears in the title of every page.";
$a_lang['mysql']['setting']['bburl'] = "Forum URL";
$a_lang['mysql']['setting']['bburldesc'] = "URL of your forum.";
$a_lang['mysql']['setting']['hometitle'] = "Homepage Title";
$a_lang['mysql']['setting']['hometitledesc'] = "Name of your homepage. This appears at the bottom of every page.";
$a_lang['mysql']['setting']['homeurl'] = "Homepage URL";
$a_lang['mysql']['setting']['homeurldesc'] = "URL of your home page. This appears at the bottom of every page.";
$a_lang['mysql']['setting']['adminurl'] = "Directory Of AdminCP";
$a_lang['mysql']['setting']['adminurldesc'] = "The directory name of your AdminCP. Some times you might want to modify this due to some security consideration. Do NOT add trailing slash at the end (\\'/\\'). Once this option is modified, you need also to rename the folder through FTP";
$a_lang['mysql']['setting']['redirecturl'] = "Default Redirect Page";
$a_lang['mysql']['setting']['redirecturldesc'] = "The forum system will redirect to this page if an URL for redirecting is not defined.";
$a_lang['mysql']['setting']['redirecturlextra'] = "1=Forum Home\n2=Homepage";
$a_lang['mysql']['setting']['cookiedomain'] = "Cookie Domain";
$a_lang['mysql']['setting']['cookiedomaindesc'] = "This option sets the domain on which the cookie is active. The most common reason to change this setting is that you have two different urls to your forum, i.e. domain.com and forums.domain.com. To allow users to stay logged into the forum if they visit via either url, you would set this to .domain.com (note the domain begins with a dot).<br /><br />You most likely want to leave this setting blank as entering an invalid setting can leave you unable to login to your forum.";
$a_lang['mysql']['setting']['cookieprefix'] = "Cookie Prefix";
$a_lang['mysql']['setting']['cookieprefixdesc'] = "Different prefixes allow you to install multiple copies of MolyX Board under one domain.";
$a_lang['mysql']['setting']['cookiepath'] = "Path to Save Cookies";
$a_lang['mysql']['setting']['cookiepathdesc'] = "The path to which the cookie is saved. If you run more than one forum on the same domain, it will be necessary to set this to the individual directories of the forums. Otherwise, just leave it as / .<br /><br />Please note that your path should always end in a forward-slash; for example \\'/forums/\\', \\'/mxb/\\' etc.<br /><br />Entering an invalid setting can leave you unable to login to your forum.";
$a_lang['mysql']['setting']['gzipoutput'] = "GZIP HTML Output";
$a_lang['mysql']['setting']['gzipoutputdesc'] = "Selecting yes will enable MolyX Board to GZIP compress the HTML output of pages, thus reducing bandwidth requirements. This will be only used on clients that support it, and are HTTP 1.1 compliant. There will be a small performance overhead.<br /><br />This feature requires the ZLIB library.<br /><br />If you are already using mod_gzip on your server, do not enable this option.";
$a_lang['mysql']['setting']['timezoneoffset'] = "Default Time Zone Offset";
$a_lang['mysql']['setting']['timezoneoffsetdesc'] = "<span style=\\'color:red\\'>Time zone offset for guests and new users. If the time of your forum is incorrect with 1 hour later or earlier, it might be caused by using DST on your server. Please enter a number of minutes for Time Fixing in the option below manually.</span>";
$a_lang['mysql']['setting']['timeadjust'] = "Time Zone Fix (Minute)";
$a_lang['mysql']['setting']['timeadjustdesc'] = "You can modify the time zone offset of your server by entering a number of minute to correct the mistake caused by DST. Numbers starting with \\'-\\' will minus the time instead of adding.";
$a_lang['mysql']['setting']['standardtimeformat'] = "Format For Date";
$a_lang['mysql']['setting']['standardtimeformatdesc'] = "See: <a href=\\'http://www.php.net/manual-lookup.php?function=date\\' target=\\'_blank\\'>http://www.php.net/manual-lookup.php?function=date</a>";
$a_lang['mysql']['setting']['longtimeformat'] = "Long Format For Date扩充的时间格式";
$a_lang['mysql']['setting']['longtimeformatdesc'] = "See: <a href=\\'http://www.php.net/manual-lookup.php?function=date\\' target=\\'_blank\\'>http://www.php.net/manual-lookup.php?function=date</a>";
$a_lang['mysql']['setting']['registereddateformat'] = "Format For Registration Date";
$a_lang['mysql']['setting']['registereddateformatdesc'] = "See: <a href=\\'http://www.php.net/manual-lookup.php?function=date\\' target=\\'_blank\\'>http://www.php.net/manual-lookup.php?function=date</a>";
$a_lang['mysql']['setting']['allowselectstyles'] = "Allow users to change styles?";
$a_lang['mysql']['setting']['titlechangeposts'] = "Minimum posts required for custom titles";
$a_lang['mysql']['setting']['titlechangepostsdesc'] = "Leaving blank for no limit.";
$a_lang['mysql']['setting']['locationmaxlength'] = "Maximum length of From";
$a_lang['mysql']['setting']['signaturemaxlength'] = "Maximum Length of Signature";
$a_lang['mysql']['setting']['signatureallowhtml'] = "Allow HTML in signatures?";
$a_lang['mysql']['setting']['signatureallowhtmldesc'] = "Strongly not recommended due to security considerations.";
$a_lang['mysql']['setting']['signatureallowbbcode'] = "Allow BBCode in signatures";
$a_lang['mysql']['setting']['allowuploadsigimg'] = "Allow image uploading for signatures?";
$a_lang['mysql']['setting']['sigimgdimension'] = "Maximum dimensions of uploaded signature images";
$a_lang['mysql']['setting']['sigimgdimensiondesc'] = "(Width <b>x</b> Height)<br />Image with dimensions greater than this setting will be resized automatically.";
$a_lang['mysql']['setting']['removesubscibe'] = "Unsubscribe threads having no reply for x days";
$a_lang['mysql']['setting']['removesubscibedesc'] = "Leaving blank or 0 will disable this option.";
$a_lang['mysql']['setting']['avatarsenabled'] = "Allow custom avatars?";
$a_lang['mysql']['setting']['defaultavatar'] = "Default avatar image";
$a_lang['mysql']['setting']['defaultavatardesc'] = "This image should be uploaded to ./images/avatars/ directory. Leaving it blank will ignore default avatar setting.";
$a_lang['mysql']['setting']['avatarextension'] = "Allowed formats for avatars";
$a_lang['mysql']['setting']['avatarextensiondesc'] = "Separate by commas.";
$a_lang['mysql']['setting']['avatarurl'] = "Allow remote URL for avatars?";
$a_lang['mysql']['setting']['avatamaxsize'] = "Maximum size of avatar files (KB)";
$a_lang['mysql']['setting']['avatardimension'] = "Maximum dimensions of avatar images";
$a_lang['mysql']['setting']['avatardimensiondesc'] = "(Width <b>x</b> Height)";
$a_lang['mysql']['setting']['avatardimensiondefault'] = "Default dimension of avatar gallery";
$a_lang['mysql']['setting']['avatardimensiondefaultdesc'] = "(Width <b>x</b> Height)";
$a_lang['mysql']['setting']['avatarcolspannumbers'] = "Colums number of avatar gallery";
$a_lang['mysql']['setting']['disableavatarsize'] = "Disable avatar auto resizing?";
$a_lang['mysql']['setting']['disableavatarsizedesc'] = "Disable this feature if you don\\'t want user avatars to be resized automatically.";
$a_lang['mysql']['setting']['guestviewsignature'] = "Allow guests to view signatures?";
$a_lang['mysql']['setting']['guestviewimg'] = "Allow guests to view attached images?";
$a_lang['mysql']['setting']['guestviewavatar'] = "Allow guests to view user avatars?";
$a_lang['mysql']['setting']['award_deduct'] = "Application fee for a medal of honor";
$a_lang['mysql']['setting']['award_deductdesc'] = "The money and reputation cost of applying an honor. Format as (money|reputation).";
$a_lang['mysql']['setting']['maxpostchars'] = "Maximum characters per post";
$a_lang['mysql']['setting']['minpostchars'] = "Minimum characters per post";
$a_lang['mysql']['setting']['maxflashwidth'] = "Default width of FLASH (Pixels)";
$a_lang['mysql']['setting']['maxflashwidthdesc'] = "Enabled when [flash] tag activated.";
$a_lang['mysql']['setting']['maxflashheight'] = "Default height of FLASH (Pixels)";
$a_lang['mysql']['setting']['maxflashheightdesc'] = "Enabled when [flash] tag activated.";
$a_lang['mysql']['setting']['perlineicons'] = "Number of post icons display per row";
$a_lang['mysql']['setting']['rowsmiles'] = "Number of rows smilies display post page";
$a_lang['mysql']['setting']['rowsmilesdesc'] = "This is the list display in post pages.";
$a_lang['mysql']['setting']['perlinesmiles'] = "Number of colums smilies display post page";
$a_lang['mysql']['setting']['perlinesmilesdesc'] = "This is the list display in post pages.";
$a_lang['mysql']['setting']['stripquotes'] = "Remove quotations?";
$a_lang['mysql']['setting']['stripquotesdesc'] = "Remove all quoted contents in posts?";
$a_lang['mysql']['setting']['imageextension'] = "Allowed formats for images";
$a_lang['mysql']['setting']['imageextensiondesc'] = "Image formats permitted to use in [img][/img] tags.<br />Separate by commas, like \\'gif,jpeg,jpg\\'";
$a_lang['mysql']['setting']['guesttag'] = "Guest Mark";
$a_lang['mysql']['setting']['guesttagdesc'] = "This is a mark to show that the author of a certain post is unregistered.";
$a_lang['mysql']['setting']['enablepolltags'] = "Allow [IMG] & [URL] tags in polls?";
$a_lang['mysql']['setting']['maxpolloptions'] = "Maximum number of options per poll";
$a_lang['mysql']['setting']['addpolltimeout'] = "Time limit of adding a poll after thead is made";
$a_lang['mysql']['setting']['addpolltimeoutdesc'] = "Will not affect admins and moderators.";
$a_lang['mysql']['setting']['disablenoreplypoll'] = "Forbid \\'Polls Only\\' threads?";
$a_lang['mysql']['setting']['floodchecktime'] = "FLOOD Control (Seconds)";
$a_lang['mysql']['setting']['floodchecktimedesc'] = "The time limit between 2 posts.<br />Leaving blank will disable this option.";
$a_lang['mysql']['setting']['watermark'] = "Enable image watermark?";
$a_lang['mysql']['setting']['watermarkdesc'] = "The forum system will a watermark on image files uploaded when posting if this option is enabled. If a image file named watermark.png exists in the image file on your server, then the IMAGE watermark will be considered as the first mark method.";
$a_lang['mysql']['setting']['watermarkextra'] = "1=Top Left Corner\n2=Bottom Left Corner左下\n3=Top Right Corner\n4=Bottom Right Corner右下\n5=Center";
$a_lang['mysql']['setting']['markposition'] = "Watermark position";
$a_lang['mysql']['setting']['markpositiondesc'] = "Please specify the position where watermark should be possessed if the above options are enabled.";
$a_lang['mysql']['setting']['useantispam'] = "Enable Anti-Spam validation?";
$a_lang['mysql']['setting']['useantispamdesc'] = "Users have to enter the validation code displayed on an image for posting to avoid FLOOD.";
$a_lang['mysql']['setting']['mxemode'] = "Default editor mode";
$a_lang['mysql']['setting']['mxemodedesc'] = "This is to determine which editor mode to use if user logins the first time or has no record in cookies.";
$a_lang['mysql']['setting']['matchbrowser'] = "Verify user browser?";
$a_lang['mysql']['setting']['matchbrowserdesc'] = "Enable this feature to avoid possible attacks using session control. However, the user will not be able to login to forum using other browsers at the same time.";
$a_lang['mysql']['setting']['allowdynimg'] = "Enable dynamic links in [IMG] tags?";
$a_lang['mysql']['setting']['allowdynimgdesc'] = "Disable this option will not parse the [IMG] tag if it contains ? or & in the URL for security considerations.";
$a_lang['mysql']['setting']['allowimages'] = "Allow [IMG] tags?";
$a_lang['mysql']['setting']['allowimagesdesc'] = "User posted images using [IMG] tags will only display a link instead of the actual image if this options is disabled.";
$a_lang['mysql']['setting']['allowflash'] = "Allow FLASH for avatars?";
$a_lang['mysql']['setting']['allowflashdesc'] = "Due to the security policy of Flash medias, this option can be enabled on your own risk.";
$a_lang['mysql']['setting']['forcelogin'] = "Requires login to view forum?";
$a_lang['mysql']['setting']['forcelogindesc'] = "Guest must login first to view the forum if this option is enabled.";
$a_lang['mysql']['setting']['WOLenable'] = "Allow users to view Who Is Online?";
$a_lang['mysql']['setting']['enablesearches'] = "Allow users to search?";
$a_lang['mysql']['setting']['minsearchlength'] = "Minimum length of search keyword";
$a_lang['mysql']['setting']['postsearchlength'] = "The maximum length of post previews";
$a_lang['mysql']['setting']['postsearchlengthdesc'] = "This is the length of post preview displayed by search result list. Leaving it blank will no longer trim the text.";
$a_lang['mysql']['setting']['forumindex'] = "Forum home file";
$a_lang['mysql']['setting']['forumindexdesc'] = "You can define a specific file name as your forum home page instead of the default name index.php. Please make sure you have the correct file name set through FTP on your server.";
$a_lang['mysql']['setting']['shownewslink'] = "Display News on forum home page?";
$a_lang['mysql']['setting']['newsforumid'] = "Forum used as News board";
$a_lang['mysql']['setting']['showloggedin'] = "Display online uers on forum home page?";
$a_lang['mysql']['setting']['showstatus'] = "Display forum stats on forum home page?";
$a_lang['mysql']['setting']['showbirthday'] = "Display birthday members on froum home page?";
$a_lang['mysql']['setting']['showbirthdaydesc'] = "The list of members having birthday today will be displayed on the forum home page if this option is enabled.";
$a_lang['mysql']['setting']['autohidebirthday'] = "Hide birthday section if no birthday members today?";
$a_lang['mysql']['setting']['autohidebirthdaydesc'] = "The birthday list will be hidden when no member found having birthday today if this option is enabled.";
$a_lang['mysql']['setting']['maxonlineusers'] = "Maximum number of online users displayed";
$a_lang['mysql']['setting']['maxonlineusersdesc'] = "If the number of online users are greater than this setting, the online user list will be hidden by default to save server resource.";
$a_lang['mysql']['setting']['showguest'] = "Display guests in online user list?";
$a_lang['mysql']['setting']['showguestdesc'] = "Guests will be displayed in the online user list if this option is enabled.";
$a_lang['mysql']['setting']['birthday_send'] = "Send congratulation to birthday users by email?";
$a_lang['mysql']['setting']['birthday_senddesc'] = "You can enter the letter content in the text box on the right. Enabling this feature may cause your server work in a high pressure for a period of time, so it is not recommanded for forums with 100,000 registered users or more.<br />Tags allowed in content:<br />{name}:Username; {money=xxx}:Money; {reputation=xxx}:Reputation; <br />If extended credits are defined, use {UniqueID=xxx} to add corresponding content.<br />In which xxx stands for the amount of money or credit points you want to give.";
$a_lang['mysql']['setting']['birthday_send_type'] = "Type of congratulations";
$a_lang['mysql']['setting']['birthday_send_typedesc'] = "What type of message you want the congratulations to be sent.";
$a_lang['mysql']['setting']['perpagepost'] = "Options for number of threads displayed per page in thread list";
$a_lang['mysql']['setting']['perpagepostdesc'] = "Separate by commas.<br />Sample: 5,15,20,25,30";
$a_lang['mysql']['setting']['maxposts'] = "Number of posts displayed per page in threads";
$a_lang['mysql']['setting']['viewattachedimages'] = "Display uploaded images in posts?";
$a_lang['mysql']['setting']['viewattachedimagesdesc'] = "Images will be directly displayed in posts if this option enabled.";
$a_lang['mysql']['setting']['viewattachedthumbs'] = "Display uploaded images as thumbnails?";
$a_lang['mysql']['setting']['viewattachedthumbsdesc'] = "If the above options are enabled, the uploaded images in posts will be displayed as thumbnails instead of the entire image.";
$a_lang['mysql']['setting']['thumbswidth'] = "Thumbnail width [Pixels]";
$a_lang['mysql']['setting']['thumbswidthdesc'] = "The width of thumbnail images. If the dimesion of uploaded images are smaller than this limit, the image will not be generated as thumbnails.";
$a_lang['mysql']['setting']['thumbsheight'] = "Thumbnail height [Pixels]";
$a_lang['mysql']['setting']['thumbsheightdesc'] = "The height of thumbnail images. If the dimesion of uploaded images are smaller than this limit, the image will not be generated as thumbnails.";
$a_lang['mysql']['setting']['allowviewresults'] = "Allow users to view poll result with out votes?";
$a_lang['mysql']['setting']['allowviewresultsdesc'] = "Users are allowed to view the result of a certain poll before they vote if this option is enabled.";
$a_lang['mysql']['setting']['onlyonesignatures'] = "Display signature of every user once per thread?";
$a_lang['mysql']['setting']['onlyonesignaturesdesc'] = "The signature of users posted in a thread will be displayed only once if this option is enabled.";
$a_lang['mysql']['setting']['maxthreads'] = "Number of threads per page in list";
$a_lang['mysql']['setting']['gstickyprefix'] = "Prefix of \\'Global Sticky\\'";
$a_lang['mysql']['setting']['stickyprefix'] = "Prefix of \\'Sticky\\'";
$a_lang['mysql']['setting']['movedprefix'] = "Prefix of \\'Moved\\'";
$a_lang['mysql']['setting']['pollprefix'] = "Prefix of \\'Poll\\'";
$a_lang['mysql']['setting']['hotnumberposts'] = "The minimum posts required as a \\'Hot Thread\\'";
$a_lang['mysql']['setting']['showforumusers'] = "Display a user list of \\'who is viewing this forum\\'?";
$a_lang['mysql']['setting']['showforumusersdesc'] = "Enabling this option will increase database query by one.";
$a_lang['mysql']['setting']['perpagethread'] = "Options for number of threads per page";
$a_lang['mysql']['setting']['perpagethreaddesc'] = "Separate by commas.<br />Sample: 5,15,20,25,30";
$a_lang['mysql']['setting']['showsubforums'] = "Display links for sub forums?";
$a_lang['mysql']['setting']['showsubforumsdesc'] = "Display links of sub forums in the forum list page if this option is enabled.";
$a_lang['mysql']['setting']['showmoderatorcolumn'] = "Show moderator colum?";
$a_lang['mysql']['setting']['showmoderatorcolumndesc'] = "Show a colum to display moderators in the forum home, forum list and user control pannel page if this option is enabled.";
$a_lang['mysql']['setting']['stickopentag'] = "HTML prefix of sticky threads";
$a_lang['mysql']['setting']['stickopentagdesc'] = "The HTML prefix of sticky threads.";
$a_lang['mysql']['setting']['stickclosetag'] = "HTML postfix of sticky threads";
$a_lang['mysql']['setting']['stickclosetagdesc'] = "The HTML postfix of sticky threads.";
$a_lang['mysql']['setting']['threadpreview'] = "Thread preview method";
$a_lang['mysql']['setting']['threadpreviewdesc'] = "Display a quick view of the thread and post when mouse is on the thread title? Not recommanded for forums with high page views.";
$a_lang['mysql']['setting']['showdescription'] = "Enable thread descriptions?";
$a_lang['mysql']['setting']['showdescriptiondesc'] = "The description of thread will not be displayed in the thread list if this option is disabled.";
$a_lang['mysql']['setting']['pmallowbbcode'] = "Allow BBCode in Private Messages?";
$a_lang['mysql']['setting']['pmallowhtml'] = "Allow HTML Code in Private Messages?";
$a_lang['mysql']['setting']['pmallowhtmldesc'] = "Users are allowed to use HTML code in private messages if this option is enabled. Strongly not recommanded for security considerations.";
$a_lang['mysql']['setting']['emailreceived'] = "Email address for receiving";
$a_lang['mysql']['setting']['emailreceiveddesc'] = "This email address will be used to receive emails from your forum.";
$a_lang['mysql']['setting']['emailsend'] = "Email address for sending";
$a_lang['mysql']['setting']['emailsenddesc'] = "All emails sent to the users will be displayed from this email address.";
$a_lang['mysql']['setting']['sesureemail'] = "Send emails using Secured Forms?";
$a_lang['mysql']['setting']['sesureemaildesc'] = "Users are only allowed to send emails to each other through the email function provided by forum system to avoid junk mail problems.";
$a_lang['mysql']['setting']['emailtype'] = "Mailing method";
$a_lang['mysql']['setting']['emailtypedesc'] = "If the mail() function of PHP does not work properly, try to use a SMTP server that does not require authorization. If you are not sure about the settings of your sever, please contact the host service provider.";
$a_lang['mysql']['setting']['smtphost'] = "SMTP Host";
$a_lang['mysql']['setting']['smtphostdesc'] = "Default: \\'localhost\\'";
$a_lang['mysql']['setting']['smtpport'] = "SMTP Port";
$a_lang['mysql']['setting']['smtpportdesc'] = "Default: 25";
$a_lang['mysql']['setting']['smtpuser'] = "SMTP Username";
$a_lang['mysql']['setting']['smtpuserdesc'] = "Generally, username is not required by \\'localhost\\'.";
$a_lang['mysql']['setting']['smtppassword'] = "SMTP Password";
$a_lang['mysql']['setting']['smtppassworddesc'] = "Generally, password is not required by \\'localhost\\'.";
$a_lang['mysql']['setting']['emailwrapbracket'] = "Add \\'&lt;\\' and \\'&gt;\\' in \\'from\\' and \\'to\\' mark?";
$a_lang['mysql']['setting']['emailwrapbracketdesc'] = "Some of the SMTP servers require the format like \\'<\\' address \\'>\\' to ensure the address is valid. If you encounter problems sending emails, try turning this option on.";
$a_lang['mysql']['setting']['disablereport'] = "Disable \\'Report To Moderator\\' feature?";
$a_lang['mysql']['setting']['disablereportdesc'] = "Forbid users to report posts to moderators if this option is enabled.";
$a_lang['mysql']['setting']['reporttype'] = "Report method";
$a_lang['mysql']['setting']['enablerecyclebin'] = "Enable Forum Recycle Bin?";
$a_lang['mysql']['setting']['enablerecyclebindesc'] = "All the options below will be disabled if this option is disabled.";
$a_lang['mysql']['setting']['recycleforumid'] = "Select a forum as Recycle Bin";
$a_lang['mysql']['setting']['recycleforumiddesc'] = "Please modify the permissions of the forum you are going to make as a recycle bin.";
$a_lang['mysql']['setting']['recycleforadmin'] = "Recycle Administrator deleted posts?";
$a_lang['mysql']['setting']['recycleforadmindesc'] = "The threads and posts deleted by Administrators will not be recycled if this option is disabled.";
$a_lang['mysql']['setting']['recycleforsuper'] = "Recycle SuperMod deleted posts?";
$a_lang['mysql']['setting']['recycleforsuperdesc'] = "The threads and posts deleted by SuperMods will not be recycled if this option is disabled.";
$a_lang['mysql']['setting']['recycleformod'] = "Recycle Moderator deleted posts?";
$a_lang['mysql']['setting']['recycleformoddesc'] = "The threads and posts deleted by Moderators will not be recycled if this option is disabled.";
$a_lang['mysql']['setting']['allowregistration'] = "Allow new user registration?";
$a_lang['mysql']['setting']['allowregistrationdesc'] = "Any attempt of registering will be notified fail if this option is disabled.";
$a_lang['mysql']['setting']['enableantispam'] = "Enable Anti-Spam registration feature?";
$a_lang['mysql']['setting']['enableantispamdesc'] = "Users are required to enter the validation code displayed on images when they are trying to register.";
$a_lang['mysql']['setting']['moderatememberstype'] = "User validation method";
$a_lang['mysql']['setting']['moderatememberstypedesc'] = "The method of validation for new registered users.";
$a_lang['mysql']['setting']['removemoderate'] = "Purge In-Activated users after x days of registration?";
$a_lang['mysql']['setting']['removemoderatedesc'] = "The users registered without validation will be deleted automatically after x days of their registration. Leaving it 0 will disable this option";
$a_lang['mysql']['setting']['newregisterpost'] = "New posts forbidden after registration";
$a_lang['mysql']['setting']['newregisterpostdesc'] = "Users will not be permitted to make new posts in x minutes after their registration.";
$a_lang['mysql']['setting']['registerrule'] = "Terms of Registration";
$a_lang['mysql']['setting']['newuser_give'] = "Money and reputations of new users";
$a_lang['mysql']['setting']['newuser_givedesc'] = "The initial money and reputation points for newly registered users. Formatted as (money|reputation)";
$a_lang['mysql']['setting']['newuser_pm'] = "PM welcome message for new users";
$a_lang['mysql']['setting']['newuser_pmdesc'] = "HTML & BBCode allowed";
$a_lang['mysql']['setting']['reg_ip_time'] = "Time interval of registration using the same IP";
$a_lang['mysql']['setting']['reg_ip_timedesc'] = "The time interval for users to register new accounts using the same IP address in minutes.";
$a_lang['mysql']['setting']['showprivacy'] = "Display link of Privacy Statements?";
$a_lang['mysql']['setting']['privacyurl'] = "URL of the privacy statements page.";
$a_lang['mysql']['setting']['privacyurldesc'] = "If you have an independent page to display privacy statements, please enter the URL of the page.";
$a_lang['mysql']['setting']['privacytitle'] = "Title of Privacy Statements";
$a_lang['mysql']['setting']['privacytext'] = "If the privacy statements are not stored in an independent page, please enter the content in the following input box.";
$a_lang['mysql']['setting']['privacytextdesc'] = "HTML allowed";
$a_lang['mysql']['setting']['bbactive'] = "Forum Open?";
$a_lang['mysql']['setting']['bbactivedesc'] = "If disabled, the forum will be displayed as \\'Closed\\', and only those who have proper permissions can view your forum.";
$a_lang['mysql']['setting']['bbclosedreason'] = "Statement of forum close";
$a_lang['mysql']['setting']['openbank'] = "Bank Open/Close";
$a_lang['mysql']['setting']['openbankdesc'] = "Is the forum bank openned or closed?";
$a_lang['mysql']['setting']['bankcurrency'] = "Currency unit";
$a_lang['mysql']['setting']['bankcurrencydesc'] = "The unit used to calculate currency.";
$a_lang['mysql']['setting']['bankcost'] = "Account creation fee";
$a_lang['mysql']['setting']['bankcostdesc'] = "The cost of creating a bank account. User with cash less than this requirement will not be able to create a bank account.";
$a_lang['mysql']['setting']['bankinterest'] = "Daily interest";
$a_lang['mysql']['setting']['bankinterestdesc'] = "The daily interest of deposit in the forum bank. Interest will not be calculated when user requested or having a loan. Unit: 1 in a thousand";
$a_lang['mysql']['setting']['bankexcost'] = "Bank transfer fee";
$a_lang['mysql']['setting']['bankexcostdesc'] = "The cost of transfer. Unit: 1 in a thousand";
$a_lang['mysql']['setting']['bankexcostskip'] = "Transfer fee override";
$a_lang['mysql']['setting']['bankexcostskipdesc'] = "The transfer will be free of charge if the amount of money transfered less than x.";
$a_lang['mysql']['setting']['banknewthread'] = "Money award of new threads";
$a_lang['mysql']['setting']['banknewthreaddesc'] = "Amount of money awarded for a new thread.";
$a_lang['mysql']['setting']['bankreplythread'] = "Money award of new replies";
$a_lang['mysql']['setting']['bankreplythreaddesc'] = "Amount of money awarded for a new reply.";
$a_lang['mysql']['setting']['bankquint'] = "Money award of quintessences";
$a_lang['mysql']['setting']['bankquintdesc'] = "Amount of money awarded when a user\\'s thread ranked as quintessence.";
$a_lang['mysql']['setting']['bankrepprice'] = "Reputation Buy-In price";
$a_lang['mysql']['setting']['bankreppricedesc'] = "The price of 1 point of reputation for users to buy.";
$a_lang['mysql']['setting']['bankrepsellprice'] = "Reputation Sell-Out price";
$a_lang['mysql']['setting']['bankrepsellpricedesc'] = "The price of 1 point of reputation for users to sell.";
$a_lang['mysql']['setting']['bankloanonoff'] = "Loan Functions";
$a_lang['mysql']['setting']['bankloanonoffdesc'] = "Enable loan features?";
$a_lang['mysql']['setting']['bankloanreglimit'] = "Minimum time after registration";
$a_lang['mysql']['setting']['bankloanreglimitdesc'] = "The mininum time after registration of an user to aply for a loan in hours.";
$a_lang['mysql']['setting']['bankloanpostlimit'] = "Minimum number of posts";
$a_lang['mysql']['setting']['bankloanpostlimitdesc'] = "The minimum number of posts required for a loan.";
$a_lang['mysql']['setting']['bankloanreplimit'] = "Minimum reputation";
$a_lang['mysql']['setting']['bankloanreplimitdesc'] = "Users with repuation points less than this will not allowed to apply for a loan.";
$a_lang['mysql']['setting']['bankloanusegroup'] = "Enable Usergroup-Based Credit?";
$a_lang['mysql']['setting']['bankloanusegroupdesc'] = "The amount of money an user can loan will be determined by their usergroup settings instead of this general setting.";
$a_lang['mysql']['setting']['bankloanamount'] = "Maximum amount to loan";
$a_lang['mysql']['setting']['bankloanamountdesc'] = "The maximum amount of money a user can loan. With the following 2 situations, this options will be disabled: 1) Usergroup-Based Credit is enabled; 2) Guests and In-Activated users are not permitted to loan.";
$a_lang['mysql']['setting']['bankloantimelimit'] = "Refund expiration";
$a_lang['mysql']['setting']['bankloantimelimitdesc'] = "The time started from the loan is applied successfully until when it should be returned in days. You can provide different loan plans by entering custom loan time limits.";
$a_lang['mysql']['setting']['bankloaninterest'] = "Loan interest";
$a_lang['mysql']['setting']['bankloaninterestdesc'] = "The interest required for loans according to which loan plan users have chosen. This option should be corresponding to the option above, which mean you should enter different interest for different loan plans. If the numbers of interest options and loan plans are not match, all loan interest will be calculated using the first option. Unit: 1 in a thousand";
$a_lang['mysql']['setting']['bankpbban'] = "Days of user frozen if bankrupted";
$a_lang['mysql']['setting']['bankpbbandesc'] = "The days for users\\' account to be frozen if they get bankrupted for any reason.";
$a_lang['mysql']['setting']['bankpbrerate'] = "Estate evaluation for execution";
$a_lang['mysql']['setting']['bankpbreratedesc'] = "If users do not have enough of money stored in bank to refund their loans before the expiration of the plans, the forum bank will try to sell their estates to refill the loan. This is the level of evaluation of their estates, which should be lower than normal price sold in market calculated by %.";
$a_lang['mysql']['setting']['bankpbreppanish'] = "Reputation penalty";
$a_lang['mysql']['setting']['bankpbreppanishdesc'] = "If users\\' estates are executed to refund their loans, the amount of reputation points to take away as penalty.";
$a_lang['mysql']['setting']['bankpbclean'] = "Bank record cleanup fee";
$a_lang['mysql']['setting']['bankpbcleandesc'] = "The cost of record cleanup after personal bankruptcy.";
$a_lang['mysql']['setting']['reducetmoney'] = "Penalty for thread deletion";
$a_lang['mysql']['setting']['reducetmoneydesc'] = "Amount of money to be decreased if a thread is deleted.";
$a_lang['mysql']['setting']['reducepmoney'] = "Penalty for post deletion";
$a_lang['mysql']['setting']['reducepmoneydesc'] = "Amount of money to be decreased if a post is deleted.";
$a_lang['mysql']['setting']['version'] = "";
$a_lang['mysql']['setting']['openolrank'] = "Enable online time ranks?";
$a_lang['mysql']['setting']['openolrankdesc'] = "All features depends on this feature will be disabled if this feature is disabled.";
$a_lang['mysql']['setting']['olrankstart'] = "First level requirement";
$a_lang['mysql']['setting']['olrankstartdesc'] = "Number of hours required to reach the first rank.";
$a_lang['mysql']['setting']['olrankstep'] = "Stepping time";
$a_lang['mysql']['setting']['olrankstepdesc'] = "Time increased for the next level compare with this level. For example, if the stepping time is 5 hours, users will need 10 hours of online time to reach level 1, and 10+5=15 hours for level 2, 20 hours for level 3 and etc.";
$a_lang['mysql']['setting']['isopeninvite'] = "Enable Invitations?";
$a_lang['mysql']['setting']['nolimitinviteusergroup'] = "Select the usergroups having unlimited invitations";
$a_lang['mysql']['setting']['nolimitinviteusergroupdesc'] = "Press CTRL + Click to select multiple usergroups.";
$a_lang['mysql']['setting']['limitinvitegroup'] = "Select the usergroups having limited invitations";
$a_lang['mysql']['setting']['limitinvitegroupdesc'] = "Press CTRL + Click to select multiple usergroups.";
$a_lang['mysql']['setting']['limitregtime'] = "Days since registration";
$a_lang['mysql']['setting']['limitregtimedesc'] = "Leave it 0 for no limit.";
$a_lang['mysql']['setting']['limitposttitlenum'] = "Minimum number of posts required";
$a_lang['mysql']['setting']['inviteminnum'] = "Minimum number of invitations";
$a_lang['mysql']['setting']['invitemaxnum'] = "Maximum number of invitations";
$a_lang['mysql']['setting']['invitexpiry'] = "Expiration of invitations since granted";
$a_lang['mysql']['setting']['inviteduserexpiry'] = "Expiration of invitations since sent";
$a_lang['mysql']['setting']['timenotlogin'] = "Last login time required";
$a_lang['mysql']['setting']['timenotlogindesc'] = "Only users logined during the past x days will get new invitations.";
$a_lang['mysql']['setting']['modreptype'] = "Rank type";
$a_lang['mysql']['setting']['modreptypedesc'] = "Some of the users are permitted to award or punish other users, add and reduce their money or reputation.";
$a_lang['mysql']['setting']['modrepmax'] = "Maximum amount of reward";
$a_lang['mysql']['setting']['modrepmaxdesc'] = "The maximum amount of money or reputation point an user can award others.";
$a_lang['mysql']['setting']['spider_roup'] = "Usergroup for Spider from search engines";
$a_lang['mysql']['setting']['spider_roupdesc'] = "If your forum does not allow guest access, setting a member usergroup for Spiders will make the search engine permitted to grab contents from your forum.";
$a_lang['mysql']['setting']['spiderid'] = "Search BOTs";
$a_lang['mysql']['setting']['spideriddesc'] = "Separate different BOTs by | symbol.";
$a_lang['mysql']['setting']['mxemodeextra'] = "0=Standard Mode (BBCode)\r\n1=Advanced Mode (WYSIWYG)";
$a_lang['mysql']['setting']['birthday_send_typeextra'] = "1=Email & PM\r\n2=Private Message\r\n3=Email";
$a_lang['mysql']['setting']['gstickyprefixdval'] = "Global Sticky: ";
$a_lang['mysql']['setting']['stickyprefixdval'] = "Sticky: ";
$a_lang['mysql']['setting']['movedprefixdval'] = "Moved: ";
$a_lang['mysql']['setting']['pollprefixdval'] = "Poll: ";
$a_lang['mysql']['setting']['threadpreviewextra'] = "0=Disable Preview\r\n1=First Post Content\r\n2=Last Post Content";
$a_lang['mysql']['setting']['reporttypeextra'] = "email=Email\r\npm=Private Message";
$a_lang['mysql']['setting']['enableantispamextra'] = "0=Disabled\r\ngd=Advanced (Requires GD)\r\ngif=Standard (No Requirements)";
$a_lang['mysql']['setting']['moderatememberstypextra'] = "user=Email Activation\r\nadmin=Admin Validation\r\n0=Disabled";
$a_lang['mysql']['setting']['registerruledval'] = "Terms of Registration\r\n\r\nRegistration to this forum is free! We do insist that you abide by the rules and policies detailed below. If you agree to the terms, please check the \\'I agree\\' checkbox and press the \\'Continue\\' button below.\r\n\r\nAlthough the administrators and moderators of {bbtitle} will attempt to keep all objectionable messages off this forum, it is impossible for us to review all messages. All messages express the views of the author, and neither the owners of {bbtitle}, nor MolyX Studio (developers of MolyX Board) will be held responsible for the content of any message.\r\n\r\nBy agreeing to these rules, you warrant that you will not post any messages that are obscene, vulgar, sexually-orientated, hateful, threatening, or otherwise violative of any laws.\r\n\r\nThe owners of {bbtitle} reserve the right to remove, edit, move or close any thread for any reason.\r\n";
$a_lang['mysql']['setting']['bbclosedreasondval'] = "Forum is currently closed...";
$a_lang['mysql']['setting']['bankcurrencydval'] = "G";
$a_lang['mysql']['setting']['modreptypextra'] = "1=Reputation\r\n2=Currency";

$a_lang['mysql']['setting']['adcolumns'] = "Number of ads display per row";
$a_lang['mysql']['setting']['adcolumnsdesc'] = "If number of ads are greater than this option, a line-break will be inserted.";
$a_lang['mysql']['setting']['adinpost'] = "Ads per page in thread display";
$a_lang['mysql']['setting']['adinpostdesc'] = "The maximum number of ads displayed in show thread page. Leaving this option 0 will insert ads in every post.";

$a_lang['mysql']['settinggroup']['generalsetting'] = "General Settings";
$a_lang['mysql']['settinggroup']['generalsettingdesc'] = "General settings about server environment and global features.";
$a_lang['mysql']['settinggroup']['forumoptimize'] = "Server Settings and Optimization Options";
$a_lang['mysql']['settinggroup']['forumoptimizedesc'] = "The settings for performance optimizations.";
$a_lang['mysql']['settinggroup']['sitenameurl'] = "Site Name / URL / Contact Details";
$a_lang['mysql']['settinggroup']['sitenameurldesc'] = "Basic information about your forum.";
$a_lang['mysql']['settinggroup']['cookieoption'] = "Cookies and HTTP Header Options";
$a_lang['mysql']['settinggroup']['cookieoptiondesc'] = "Adjust cookies related settings and page output options.";
$a_lang['mysql']['settinggroup']['datetimeoption'] = "Date and Time Options";
$a_lang['mysql']['settinggroup']['datetimeoptiondesc'] = "Global date and time options for your forum.";
$a_lang['mysql']['settinggroup']['userpara'] = "User Settings";
$a_lang['mysql']['settinggroup']['userparadesc'] = "Permissions and settings for users of your forum.";
$a_lang['mysql']['settinggroup']['postoption'] = "Thread & Post Settings";
$a_lang['mysql']['settinggroup']['postoptiondesc'] = "Settings about posting, viewing and polls.";
$a_lang['mysql']['settinggroup']['securityctrl'] = "Security & Privacy";
$a_lang['mysql']['settinggroup']['securityctrldesc'] = "Settings for security and privacy considerations.";
$a_lang['mysql']['settinggroup']['searchoption'] = "Search Options";
$a_lang['mysql']['settinggroup']['indexsetting'] = "Forum Home Settings";
$a_lang['mysql']['settinggroup']['indexsettingdesc'] = "Settings about everything displayed in forum home page, such as news and etc.";
$a_lang['mysql']['settinggroup']['showthread'] = "Thread Display Options (showthread)";
$a_lang['mysql']['settinggroup']['forumdisplay'] = "Forum Display Options (forumdisplay)";
$a_lang['mysql']['settinggroup']['emailpmsetting'] = "Email & Private Message Settings";
$a_lang['mysql']['settinggroup']['emailpmsettingdesc'] = "Settings about mail and message features.";
$a_lang['mysql']['settinggroup']['recyclesetting'] = "Recycle Bin Settings";
$a_lang['mysql']['settinggroup']['recyclesettingdesc'] = "Define forum as recycle bin for deleted posts.";
$a_lang['mysql']['settinggroup']['userregoption'] = "Registration Options";
$a_lang['mysql']['settinggroup']['privacyance'] = "Privacy Statements";
$a_lang['mysql']['settinggroup']['privacyancedesc'] = "Settings about privacy statements.";
$a_lang['mysql']['settinggroup']['opencloseforum'] = "Turn Your Forum On / Off";
$a_lang['mysql']['settinggroup']['opencloseforumdesc'] = "Turn your forum on or off.";
$a_lang['mysql']['settinggroup']['olranksetting'] = "Online Rank Settings";
$a_lang['mysql']['settinggroup']['olranksettingdesc'] = "Settings for user online time and ranks.";
$a_lang['mysql']['settinggroup']['invitesetting'] = "Invitation Settings";
$a_lang['mysql']['settinggroup']['invitesettingdesc'] = "Set permissions and grant invitations for users.";
$a_lang['mysql']['settinggroup']['searchenginesetting'] = "Search Engine Friendly Settings";
$a_lang['mysql']['settinggroup']['searchenginesettingdesc'] = "Settings to optimize search reasult for Spider from search engines.";
$a_lang['mysql']['settinggroup']['adsetting'] = "Ad Settings";
$a_lang['mysql']['settinggroup']['adsettingdesc'] = "Settings for ads displayed in your forum.";

$a_lang['mysql']['usergroup']['waittingvalidate'] = "In-Activated";
$a_lang['mysql']['usergroup']['guest'] = "Guest";
$a_lang['mysql']['usergroup']['regmember'] = "Member";
$a_lang['mysql']['usergroup']['admin'] = "Administrator";
$a_lang['mysql']['usergroup']['banuser'] = "Banned";
$a_lang['mysql']['usergroup']['supermod'] = "SuperMod";
$a_lang['mysql']['usergroup']['mod'] = "Moderator";

$a_lang['mysql']['usertitle']['newuser'] = "Newbie";
$a_lang['mysql']['usertitle']['mediatemember'] = "Junior Member";
$a_lang['mysql']['usertitle']['highermember'] = "Senior Member";

// add 2.6.0
$a_lang['mysql']['setting']['usernameminlength'] = 'Maximum length of Username';
$a_lang['mysql']['setting']['usernameminlengthdesc'] = 'The minimum length of user name';
$a_lang['mysql']['setting']['usernamemaxlength'] = 'Maximum length of Username';
$a_lang['mysql']['setting']['usernamemaxlengthdesc'] = 'The maximum length of user name (20 characters or less) ';
$a_lang['mysql']['setting']['moderatorlist'] = 'Moderator Display Method';
$a_lang['mysql']['setting']['moderatorlistd'] = 'Dropdown Menu';
$a_lang['mysql']['setting']['moderatorlisth'] = 'Horizontal Display';
$a_lang['mysql']['setting']['quickeditorloadmode'] = 'Quick Reply Editor Loading Method';
$a_lang['mysql']['setting']['quickeditorloadmodedesc'] = 'How will the quick reply editor be loaded.';
$a_lang['mysql']['setting']['quickeditorloadmodeextra'] = "1=Load automatically\r\n2=Load after click";
$a_lang['mysql']['setting']['quickeditordisplaymenu'] = 'Show Menu for Quick Reply Editor';
$a_lang['mysql']['setting']['quickeditordisplaymenudesc'] = 'Show control buttons for Quick Eeply Editor? The editor will load faster when this option is disabled.';
$a_lang['install']['selectlanguage'] = "Please Select Language";
$a_lang['mysql']['setting']['guest'] = 'Guest';
$a_lang['install']['welcome'] = 'Welcome to MolyX Board installer!';

// add 2.6.1
$a_lang['mysql']['setting']['rewritestatus'] = 'URLRewrite';
$a_lang['mysql']['setting']['rewritestatusdesc'] = 'The common URL of the forum will be converted into static address for easier search engine indexing optimization when this option is set to "Yes". This feature requires the web server compiled and loaded with UrlRewrite module, which MIGHT needs to be set manually by using server administrator account. Please view the detailed rules described in "<a href="http://www.molyx.cn/index.php/%E8%AF%A6%E7%BB%86%E5%B8%B8%E8%A7%84%E8%AE%BE%E7%BD%AE#.E6.90.9C.E7.B4.A2.E5.BC.95.E6.93.8E.E8.AE.BE.E5.AE.9A" target="_blank">User\\\'s Manual</a>". CAUTION: Turning this feature on may slightly increase the server load when heavy traffic occurs. ';
$a_lang['mysql']['setting']['threadviewsdelay'] = 'Thread view counter asynchronous update';
$a_lang['mysql']['setting']['threadviewsdelaydesc'] = 'Turning this feature ON will make the thread view counter being updated hourly instead of instantly after click. The exact interval between updates is set by the scheduled task named "Thread View Counter Update".<br />It is recommended to turn this feature ON in order to save server recource if your forum handles heavy traffic.';
$a_lang['mysql']['setting']['attachmentviewsdelay'] = 'Attachment view counter asynchronous update';
$a_lang['mysql']['setting']['attachmentviewsdelaydesc'] = 'Turning this feature ON will make the attachment view counter being updated hourly instead of instantly after click. The exact interval between updates is set by the scheduled task named "Attachment View Counter Update".<br />It is recommended to turn this feature ON in order to save server recource if your forum contains large quantity of attachments, especially those images attach and display in posts.';
$a_lang['mysql']['cron']['threadviews'] = 'Tread View Counter Update';
$a_lang['mysql']['cron']['threadviewsdesc'] = 'Hourly thread view counter update, executes only when "Thread view counter asynchronous update" option is turned on. ';
$a_lang['mysql']['cron']['attachmentviews'] = 'Attachment View Counter Update';
$a_lang['mysql']['cron']['attachmentviewsdesc'] = 'Hourly attachment view counter update, executes only when "Attachment view counter asynchronous update" option is turned on. ';
?>