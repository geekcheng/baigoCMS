<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

class AJAX_SECCODE {

	function __construct() { //构造函数
		$this->obj_base   = $GLOBALS["obj_base"]; //获取界面类型
		$this->config     = $this->obj_base->config;
		$this->alert      = include_once(BG_PATH_LANG . $this->config["lang"] . "/alert.php"); //载入提示代码
	}

	function ajax_check() {
		$seccode = fn_getSafe($_GET["seccode"], "txt", "");

		if (strtolower($seccode) == $_SESSION["seccode_" . BG_SITE_SSIN]) {
			$_str_alert = "ok";
		} else {
			$_str_alert = $this->alert["x030101"];
		}

		$arr_re = array(
			"re" => $_str_alert
		);

		echo json_encode($arr_re);
	}
}
?>