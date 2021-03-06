<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

include_once(BG_PATH_FUNC . "admin.func.php"); //载入 http
include_once(BG_PATH_FUNC . "http.func.php"); //载入 http
include_once(BG_PATH_CLASS . "ajax.class.php"); //载入 AJAX 基类
include_once(BG_PATH_CLASS . "sso.class.php"); //载入模板类
include_once(BG_PATH_MODEL . "admin.class.php"); //载入后台用户类
include_once(BG_PATH_MODEL . "group.class.php"); //载入管理帐号模型

/*-------------UC 类-------------*/
class AJAX_ADMIN {

	private $adminLogged;
	private $obj_ajax;
	private $obj_sso;
	private $mdl_admin;
	private $mdl_group;

	function __construct() { //构造函数
		$this->adminLogged    = $GLOBALS["adminLogged"]; //获取已登录信息
		$this->obj_ajax       = new CLASS_AJAX(); //获取界面类型
		$this->obj_sso        = new CLASS_SSO(); //获取界面类型
		$this->mdl_admin      = new MODEL_ADMIN(); //设置管理员对象
		$this->mdl_group      = new MODEL_GROUP(); //设置管理员对象
		if ($this->adminLogged["str_alert"] != "y020102") { //未登录，抛出错误信息
			$this->obj_ajax->halt_alert($this->adminLogged["str_alert"]);
		}
	}


	/**
	 * ajax_submit function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_submit() {
		$_arr_adminPost = fn_adminPost();
		$_str_adminRand = fn_rand(6);

		if ($_arr_adminPost["str_alert"] != "ok") {
			$this->obj_ajax->halt_alert($_arr_adminPost["str_alert"]);
		}

		if ($_arr_adminPost["admin_id"] > 0) {
			if ($this->adminLogged["admin_allow_sys"]["admin"]["edit"] != 1) {
				$this->obj_ajax->halt_alert("x020303");
			}

			//检验用户是否存在
			$_arr_adminRow = $this->mdl_admin->mdl_read($_arr_adminPost["admin_id"]);
			if ($_arr_adminRow["str_alert"] != "y020102") {
				$this->obj_ajax->halt_alert($_arr_adminRow["str_alert"]);
			}

			$_str_adminPass  = $_POST["admin_pass"];
			$_arr_ssoEdit    = $this->obj_sso->sso_edit($_arr_adminPost["admin_name"], "", $_str_adminPass, $_arr_adminPost["admin_mail"]);
			$_num_adminId    = $_arr_adminPost["admin_id"];
		} else {
			if ($this->adminLogged["admin_allow_sys"]["admin"]["add"] != 1) {
				$this->obj_ajax->halt_alert("x020302");
			}

			$_arr_adminPass = validateStr($_POST["admin_pass"], 1, 0);
			switch ($_arr_adminPass["status"]) {
				case "too_short":
					$this->obj_ajax->halt_alert("x020210");
				break;

				case "ok":
					$_str_adminPass = $_arr_adminPass["str"];
				break;
			}
			$_arr_ssoReg = $this->obj_sso->sso_reg($_arr_adminPost["admin_name"], $_str_adminPass, $_arr_adminPost["admin_mail"], $_arr_adminPost["admin_note"]);
			if ($_arr_ssoReg["str_alert"] != "y010101") {
				$this->obj_ajax->halt_alert($_arr_ssoReg["str_alert"]);
			}
			$_num_adminId = $_arr_ssoReg["user_id"];
		}

		$_arr_adminRow = $this->mdl_admin->mdl_submit($_num_adminId, $_arr_adminPost["admin_name"], $_arr_adminPost["admin_note"], $_str_adminRand, $_arr_adminPost["admin_status"], $_arr_adminPost["admin_allow_cate"]);

		$this->obj_ajax->halt_alert($_arr_adminRow["str_alert"]);
	}


	/**
	 * ajax_my function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_my() {
		$_arr_adminMy = fn_adminMy();

		if ($_arr_adminMy["str_alert"] != "ok") {
			$this->obj_ajax->halt_alert($_arr_adminMy["str_alert"]);
		}

		//检验MAIL是否重复
		$_arr_ssoChk = $this->obj_sso->sso_chkmail($_arr_adminMy["admin_mail"], $_arr_adminMy["admin_id"]);
		if ($_arr_ssoChk["str_alert"] != "y010211") {
			$this->obj_ajax->halt_alert($_arr_ssoChk["str_alert"]);
		}

		$_arr_ssoEdit     = $this->obj_sso->sso_edit($this->adminLogged["admin_id"], $_arr_adminMy["admin_pass"], $_arr_adminMy["admin_pass_new"], $_arr_adminMy["admin_mail"], $this->adminLogged["admin_note"], "user_id", true);

		$this->obj_ajax->halt_alert($_arr_ssoEdit["str_alert"]);
	}


	/**
	 * ajax_auth function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_auth() {
		$_arr_adminPost = fn_adminPost();
		$_str_adminRand = fn_rand(6);

		if ($_arr_adminPost["str_alert"] != "ok") {
			$this->obj_ajax->halt_alert($_arr_adminPost["str_alert"]);
		}

		if ($this->adminLogged["admin_allow_sys"]["admin"]["add"] != 1) {
			$this->obj_ajax->halt_alert("x020302");
		}

		$_arr_ssoGet = $this->obj_sso->sso_get($_arr_adminPost["admin_name"], "user_name");
		if ($_arr_ssoGet["str_alert"] != "y010102") {
			$this->obj_ajax->halt_alert($_arr_ssoGet["str_alert"]);
		}

		//检验用户是否存在
		$_arr_adminRow = $this->mdl_admin->mdl_read($_arr_ssoGet["user_id"]);
		if ($_arr_adminRow["str_alert"] == "y020102") {
			$this->obj_ajax->halt_alert("x020206");
		}

		$_arr_adminRow = $this->mdl_admin->mdl_submit($_arr_ssoGet["user_id"], $_arr_adminPost["admin_name"], $_arr_adminPost["admin_note"], $_str_adminRand, $_arr_adminPost["admin_status"], $_arr_adminPost["admin_allow_cate"]);
		if ($_arr_adminRow["str_alert"] == "x020101") {
			$_str_alert = "y020101";
		} else {
			$_str_alert = $_arr_adminRow["str_alert"];
		}

		$this->obj_ajax->halt_alert($_str_alert);
	}


	/**
	 * ajax_toGroup function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_toGroup() {
		if ($this->adminLogged["admin_allow_sys"]["admin"]["toGroup"] != 1) {
			$this->obj_ajax->halt_alert("x020305");
		}

		$_num_adminId = fn_getSafe($_POST["admin_id"], "int", 0);
		$_num_groupId = fn_getSafe($_POST["group_id"], "int", 0);

		//检验用户是否存在
		$_arr_adminRow = $this->mdl_admin->mdl_read($_num_adminId);
		if ($_arr_adminRow["str_alert"] != "y020102") {
			$this->obj_ajax->halt_alert($_arr_adminRow["str_alert"]);
		}

		if ($_num_groupId > 0) {
			$_arr_groupRow = $this->mdl_group->mdl_read($_num_groupId);
			if ($_arr_groupRow["str_alert"] != "y040102") {
				$this->obj_ajax->halt_alert($_arr_groupRow["str_alert"]);
			}
		}

		$_arr_adminRow = $this->mdl_admin->mdl_toGroup($_num_adminId, $_num_groupId);

		$this->obj_ajax->halt_alert($_arr_adminRow["str_alert"]);
	}


	/**
	 * ajax_del function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_del() {
		if ($this->adminLogged["admin_allow_sys"]["admin"]["del"] != 1) {
			$this->obj_ajax->halt_alert("x020304");
		}

		$_arr_adminDo = fn_adminDo();
		if ($_arr_adminDo["str_alert"] != "ok") {
			$this->obj_ajax->halt_alert($_arr_adminDo["str_alert"]);
		}

		$_arr_adminRow = $this->mdl_admin->mdl_del($_arr_adminDo["admin_ids"]);

		$this->obj_ajax->halt_alert($_arr_adminRow["str_alert"]);
	}


	/**
	 * ajax_status function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_status() {
		if ($this->adminLogged["admin_allow_sys"]["admin"]["edit"] != 1) {
			$this->obj_ajax->halt_alert("x020303");
		}

		$_arr_adminDo = fn_adminDo();
		if ($_arr_adminDo["str_alert"] != "ok") {
			$this->obj_ajax->halt_alert($_arr_adminDo["str_alert"]);
		}

		$_str_adminStatus = fn_getSafe($_POST["act_post"], "txt", "");
		if (!$_str_adminStatus) {
			$this->obj_ajax->halt_alert("x020213");
		}

		$_arr_adminRow = $this->mdl_admin->mdl_status($_arr_adminDo["admin_ids"], $_str_adminStatus);

		$this->obj_ajax->halt_alert($_arr_adminRow["str_alert"]);
	}

	/**
	 * ajax_chkname function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_chkname() {
		$_str_adminName   = fn_getSafe($_GET["admin_name"], "txt", "");
		$_arr_ssoChk      = $this->obj_sso->sso_chkname($_str_adminName);

		if ($_arr_ssoChk["str_alert"] != "y010205") {
			if ($_arr_ssoChk["str_alert"] == "x010205") {
				$_arr_ssoGet = $this->obj_sso->sso_get($_str_adminName, "user_name");
				//检验用户是否存在
				$_arr_adminRow = $this->mdl_admin->mdl_read($_arr_ssoGet["user_id"]);
				if ($_arr_adminRow["str_alert"] == "y020102") {
					$this->obj_ajax->halt_re("x020206");
				} else {
					$this->obj_ajax->halt_re("x020204");
				}
			} else {
				$this->obj_ajax->halt_re($_arr_ssoChk["str_alert"]);
			}
		}

		$arr_re = array(
			"re" => "ok"
		);

		exit(json_encode($arr_re));
	}


	function ajax_chkauth() {
		$_str_adminName   = fn_getSafe($_GET["admin_name"], "txt", "");
		$_arr_ssoGet      = $this->obj_sso->sso_get($_str_adminName, "user_name");

		if ($_arr_ssoGet["str_alert"] != "y010102") {
			if ($_arr_ssoGet["str_alert"] == "x010102") {
				$this->obj_ajax->halt_re("x020205");
			} else {
				$this->obj_ajax->halt_re($_arr_ssoGet["str_alert"]);
			}
		} else {
			//检验用户是否存在
			$_arr_adminRow = $this->mdl_admin->mdl_read($_arr_ssoGet["user_id"]);
			if ($_arr_adminRow["str_alert"] == "y020102") {
				$this->obj_ajax->halt_re("x020206");
			}
		}

		$arr_re = array(
			"re" => "ok"
		);

		exit(json_encode($arr_re));
	}


	/**
	 * ajax_chkmail function.
	 *
	 * @access public
	 * @return void
	 */
	function ajax_chkmail() {
		$_str_adminMail   = fn_getSafe($_GET["admin_mail"], "txt", "");
		$_num_adminId     = fn_getSafe($_GET["admin_id"], "int", 0);
		$_arr_ssoChk      = $this->obj_sso->sso_chkmail($_str_adminMail, $_num_adminId);
		//print_r($_arr_ssoChk);

		if ($_arr_ssoChk["str_alert"] != "y010211") {
			$this->obj_ajax->halt_re($_arr_ssoChk["str_alert"]);
		}

		$arr_re = array(
			"re" => "ok"
		);

		exit(json_encode($arr_re));
	}
}
?>