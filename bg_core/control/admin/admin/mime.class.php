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
include_once(BG_PATH_MODEL . "mime.class.php"); //载入上传模型

/*-------------允许类-------------*/
class CONTROL_MIME {

	public $obj_tpl;
	public $mdl_mime;
	public $adminLogged;

	function __construct() { //构造函数
		$this->obj_base       = $GLOBALS["obj_base"]; //获取界面类型
		$this->config         = $this->obj_base->config;
		$this->adminLogged    = $GLOBALS["adminLogged"];
		$this->mdl_mime       = new MODEL_MIME(); //设置上传信息对象
		$this->obj_tpl        = new CLASS_TPL(BG_PATH_SYSTPL_ADMIN . $this->config["ui"]);; //初始化视图对象
		$this->mime           = include_once(BG_PATH_LANG . $this->config["lang"] . "/mime.php"); //载入类型文件
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
		if ($this->adminLogged["admin_allow_sys"]["upfile"]["mime"] != 1) {
			return array(
				"str_alert" => "x080301",
			);
			exit;
		}

		$_num_page    = fn_getSafe($_GET["page"], "int", 1);
		$_act_get     = fn_getSafe($_GET["act_get"], "txt", "");

		$_arr_search = array(
			"page"       => $_num_page,
			"act_get"    => $_act_get,
		);

		$_num_mimeCount   = $this->mdl_mime->mdl_count();
		$_arr_page        = fn_page($_num_mimeCount); //取得分页数据
		$_str_query       = http_build_query($_arr_search);
		$_arr_mimeRows    = $this->mdl_mime->mdl_list(BG_DEFAULT_PERPAGE, $_arr_page["except"]);

		foreach ($_arr_mimeRows as $_key=>$_value) {
			unset($this->mime[$_value["mime_name"]]);
		}

		$_arr_tpl = array(
			"query"      => $_str_query,
			"search"     => $_arr_search,
			"mimeJson"   => json_encode($this->mime),
			"mimeRow"    => $this->mime,
			"pageRow"    => $_arr_page,
			"mimeRows"   => $_arr_mimeRows, //上传信息信息
		);

		$_arr_tplData = array_merge($this->tplData, $_arr_tpl);

		$this->obj_tpl->tplDisplay("mime_list.tpl", $_arr_tplData);

		return array(
			"str_alert" => "y080301",
		);
	}

}
?>