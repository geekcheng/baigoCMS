<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

include_once(BG_PATH_FUNC . "http.func.php"); //载入模板类
include_once(BG_PATH_FUNC . "baigocode.func.php"); //载入模板类
include_once(BG_PATH_FUNC . "app.func.php"); //载入模板类
include_once(BG_PATH_CLASS . "ajax.class.php"); //载入模板类
include_once(BG_PATH_MODEL . "app.class.php"); //载入管理帐号模型
include_once(BG_PATH_MODEL . "log.class.php"); //载入管理帐号模型

/*-------------用户控制器-------------*/
class AJAX_APP {

	private $adminLogged;
	private $obj_ajax;
	private $log;
	private $mdl_app;
	private $mdl_log;

	function __construct() { //构造函数
		$this->adminLogged    = $GLOBALS["adminLogged"]; //已登录用户信息
		$this->obj_ajax       = new CLASS_AJAX(); //获取界面类型
		$this->log            = $this->obj_ajax->log; //初始化 AJAX 基对象
		$this->mdl_app        = new MODEL_APP(); //设置用户模型
		$this->mdl_log        = new MODEL_LOG(); //设置管理员模型
		if ($this->adminLogged["str_alert"] != "y020102") { //未登录，抛出错误信息
			$this->obj_ajax->halt_alert($this->adminLogged["str_alert"]);
		}
	}


	/**
	 * ajax_notice function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_notice() {
		$_num_appId = fn_getSafe($_POST["app_id_notice"], "int", 0);
		if ($_num_appId == 0) {
			$this->obj_ajax->halt_alert("x050203");
		}

		if ($this->adminLogged["admin_allow"]["app"]["browse"] != 1) {
			$this->obj_ajax->halt_alert("x050301");
		}

		$_arr_appRow = $this->mdl_app->mdl_read($_num_appId);
		if ($_arr_appRow["str_alert"] != "y050102") {
			$this->obj_ajax->halt_alert($_arr_appRow["str_alert"]);
		}

		$_tm_time    = time();
		$_str_rand   = fn_rand();
		$_str_sign   = fn_baigoSignMk($_tm_time, $_str_rand);
		$_str_echo   = fn_rand();

		$_arr_data = array(
			"timestamp"  => $_tm_time,
			"random"     => $_str_rand,
			"signature"  => $_str_sign,
			"echostr"    => $_str_echo,
			"app_id"     => $_arr_appRow["app_id"],
			"app_key"    => $_arr_appRow["app_key"],
		);

		$_arr_notice = fn_http($_arr_appRow["app_notice"], $_arr_data, "post");

		if ($_arr_notice["ret"] == $_str_echo) {
			$_str_alert = "y050401";
		} else {
			$_str_alert = "x050401";

			$_arr_targets[] = array(
				"app_id" => $_num_appId,
			);
			$_str_targets    = json_encode($_arr_targets);
			$_str_notice     = json_encode($_arr_notice);
			$this->mdl_log->mdl_submit($_str_targets, "app", $this->log["app"]["noticeTest"], $_str_notice, "admin", $this->adminLogged["admin_id"]);
		}

		$this->obj_ajax->halt_alert($_str_alert);
	}


	/**
	 * ajax_submit function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_submit() {
		$_arr_appPost = fn_appPost();

		if ($_arr_appPost["str_alert"] != "ok") {
			$this->obj_ajax->halt_alert($_arr_appPost["str_alert"]);
		}

		if ($_arr_appPost["app_id"] > 0) {
			if ($this->adminLogged["admin_allow"]["app"]["edit"] != 1) {
				$this->obj_ajax->halt_alert("x050303");
			}
			//检查用户是否存在
			$_arr_appRow = $this->mdl_app->mdl_read($_arr_appPost["app_id"]);
			if ($_arr_appRow["str_alert"] != "y050102") {
				$this->obj_ajax->halt_alert($_arr_appRow["str_alert"]);
			}
		} else {
			if ($this->adminLogged["admin_allow"]["app"]["add"] != 1) {
				$this->obj_ajax->halt_alert("x050302");
			}
		}

		$_arr_appRow = $this->mdl_app->mdl_submit($_arr_appPost["app_id"], $_arr_appPost["app_name"], $_arr_appPost["app_notice"], $_arr_appPost["app_note"], $_arr_appPost["app_status"], $_arr_appPost["app_ip_allow"], $_arr_appPost["app_ip_bad"], $_arr_appPost["app_sync"], $_arr_appPost["app_allow"]);

		if ($_arr_appRow["str_alert"] == "y050101" || $_arr_appRow["str_alert"] == "y050103") {
			$_arr_targets[] = array(
				"app_id" => $_arr_appRow["app_id"],
			);
			$_str_targets = json_encode($_arr_targets);
			if ($_arr_appRow["str_alert"] == "y050101") {
				$_type = "add";
			} else {
				$_type = "edit";
			}
			$_str_appRow = json_encode($_arr_appRow);
			$this->mdl_log->mdl_submit($_str_targets, "app", $this->log["app"][$_type], $_str_appRow, "admin", $this->adminLogged["admin_id"]);
		}

		$this->obj_ajax->halt_alert($_arr_appRow["str_alert"]);
	}


	/**
	 * ajax_status function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_status() {
		if ($this->adminLogged["admin_allow"]["app"]["edit"] != 1) {
			$this->obj_ajax->halt_alert("x050303");
		}

		$_str_status = fn_getSafe($_POST["act_post"], "txt", "");

		$_arr_appDo = fn_appDo();
		if ($_arr_appDo["str_alert"] != "ok") {
			$this->obj_ajax->halt_alert($_arr_appDo["str_alert"]);
		}

		$_arr_appRow = $this->mdl_app->mdl_status($_arr_appDo["app_ids"], $_str_status);

		if ($_arr_appRow["str_alert"] == "y050103") {
			foreach ($_arr_appDo["app_ids"] as $_value) {
				$_arr_targets[] = array(
					"app_id" => $_value,
				);
				$_str_targets = json_encode($_arr_targets);
			}
			$_str_appRow = json_encode($_arr_appRow);
			$this->mdl_log->mdl_submit($_str_targets, "app", $this->log["app"]["edit"], $_str_appRow, "admin", $this->adminLogged["admin_id"]);
		}

		$this->obj_ajax->halt_alert($_arr_appRow["str_alert"]);
	}


	/**
	 * ajax_del function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_del() {
		if ($this->adminLogged["admin_allow"]["app"]["del"] != 1) {
			$this->obj_ajax->halt_alert("x050304");
		}

		$_arr_appDo = fn_appDo();
		if ($_arr_appDo["str_alert"] != "ok") {
			$this->obj_ajax->halt_alert($_arr_appDo["str_alert"]);
		}

		$_arr_appRow = $this->mdl_app->mdl_del($_arr_appDo["app_ids"]);

		if ($_arr_appRow["str_alert"] == "y050104") {
			foreach ($_arr_appDo["app_ids"] as $_value) {
				$_arr_targets[] = array(
					"app_id" => $_value,
				);
				$_str_targets = json_encode($_arr_targets);
			}
			$_str_appRow = json_encode($_arr_appRow);
			$this->mdl_log->mdl_submit($_str_targets, "app", $this->log["app"]["del"], $_str_appRow, "admin", $this->adminLogged["admin_id"]);
		}

		$this->obj_ajax->halt_alert($_arr_appRow["str_alert"]);
	}
}
?>