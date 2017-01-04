<?php
/**
 * Useforum  Copyright (C) 2010-2013 权限模型
 * 添加日期 2011 GW
 */
class lib_acl extends spModel
{
  var $pk = "aclid"; // 每个留言唯一的标志，可以称为主键
  var $table = "acl"; // 数据表的名称
}