<?php
/*-----------------------------------------------------------------

！！！！警告！！！！
以下为系统文件，请勿修改

-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

include_once(BG_PATH_INC . "is_install.inc.php"); //验证是否已登录
include_once(BG_PATH_INC . "common_global.inc.php"); //载入全局通用
include_once(BG_PATH_CLASS . "mysql.class.php"); //载入数据库类
include_once(BG_PATH_CLASS . "base.class.php"); //载入基类
include_once(BG_PATH_CONTROL_ADMIN . "admin/session.class.php"); //载入商家控制器

$GLOBALS["obj_db"]      = new CLASS_MYSQL(); //设置数据库对象
$GLOBALS["obj_base"]    = new CLASS_BASE(); //初始化基类
$ctl_session            = new CONTROL_SESSION(); //初始化控制器
$GLOBALS["adminLogged"] = $ctl_session->ctl_session(); //验证 session, 并获取管理员信息
?>