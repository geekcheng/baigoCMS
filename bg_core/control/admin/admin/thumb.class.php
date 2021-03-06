<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

include_once(BG_PATH_CLASS . "tpl.class.php"); //载入模板类
include_once(BG_PATH_MODEL . "thumb.class.php"); //载入上传模型

/*-------------缩略图类-------------*/
class CONTROL_THUMB {

	private $obj_tpl;
	private $mdl_thumb;
	private $adminLogged;

	function __construct() { //构造函数
		$this->obj_base       = $GLOBALS["obj_base"]; //获取界面类型
		$this->config         = $this->obj_base->config;
		$this->adminLogged    = $GLOBALS["adminLogged"];
		$this->mdl_thumb      = new MODEL_THUMB(); //设置上传信息对象
		$this->obj_tpl        = new CLASS_TPL(BG_PATH_SYSTPL_ADMIN . $this->config["ui"]);; //初始化视图对象
		$this->tplData = array(
			"adminLogged" => $this->adminLogged
		);
	}


	/**
	 * ctl_list function.
	 *
	 * @access public
	 * @return void
	 */
	function ctl_list() {
		if ($this->adminLogged["admin_allow_sys"]["upfile"]["thumb"] != 1) {
			return array(
				"str_alert" => "x090301",
			);
			exit;
		}

		$_num_page    = fn_getSafe($_GET["page"], "int", 1);
		$_act_get     = fn_getSafe($_GET["act_get"], "txt", "");
		$_str_type    = fn_getSafe($_GET["type"], "txt", "");

		$_arr_search = array(
			"page"       => $_num_page,
			"act_get"    => $_act_get,
			"type"       => $_str_type,
		);

		$_num_thumbCount  = $this->mdl_thumb->mdl_count($_str_type);
		$_arr_page        = fn_page($_num_adminCount); //取得分页数据
		$_str_query       = http_build_query($_arr_search);
		$_arr_thumbRows   = $this->mdl_thumb->mdl_list(BG_DEFAULT_PERPAGE, $_arr_page["except"], $_str_type);

		$_arr_tpl = array(
			"query"      => $_str_query,
			"pageRow"    => $_arr_page,
			"search"     => $_arr_search,
			"thumbRows"  => $_arr_thumbRows, //上传信息信息
		);

		$_arr_tplData = array_merge($this->tplData, $_arr_tpl);

		$this->obj_tpl->tplDisplay("thumb_list.tpl", $_arr_tplData);

		return array(
			"str_alert" => "y090301",
		);
	}
}
?>