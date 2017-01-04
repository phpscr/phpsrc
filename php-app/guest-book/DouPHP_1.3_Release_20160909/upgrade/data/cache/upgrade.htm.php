<?php /* Smarty version 2.6.26, created on 2015-07-27 20:59:40
         compiled from upgrade.htm */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $this->_tpl_vars['title']; ?>
</title>
<link href="template/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div id="wrapper">
 <div class="logo"><a href="http://www.douco.com" target="_blank"><img src="template/logo.gif" alt="DouPHP" title="DouPHP" /></a></div>
 <div class="upgrade"> 
  <?php if ($this->_tpl_vars['up_info']['wrong']): ?>
  <p class="wrong"><strong><?php echo $this->_tpl_vars['lang']['wrong']; ?>
：</strong><?php echo $this->_tpl_vars['up_info']['wrong']; ?>
</p>
  <?php else: ?>
  <h3><?php echo $this->_tpl_vars['lang']['upgrade_up_list']; ?>
</h3>
  <ul>
   <?php $_from = $this->_tpl_vars['up_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['item']):
?>
   <li><?php echo $this->_tpl_vars['item']; ?>
</li>
   <?php endforeach; endif; unset($_from); ?>
  </ul>
  <form action="index.php?step=upgrade" method="POST">
   <p class="action">
    <input type="submit" class="btn" name="upgrade" value="<?php echo $this->_tpl_vars['lang']['upgrade_submit']; ?>
">
   </p>
  </form>
  <?php endif; ?> 
 </div>
</div>
</body>
</html>