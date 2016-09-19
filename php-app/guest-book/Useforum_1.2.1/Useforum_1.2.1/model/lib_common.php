<?php
/**
 * Useforum  Copyright (C) 2010-2013 基本信息模型
 * 添加日期 13-6-18 GW
 */
class lib_common extends spModel
{
	var $pk = "name"; // 每个reply唯一的标志，可以称为主键
	var $table = "option"; // 数据表的名称
}