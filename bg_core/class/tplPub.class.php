<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

include_once(BG_PATH_SMARTY . "smarty.class.php"); //载入 Smarty 类

/*-------------前台模板类-------------*/
class CLASS_TPL {

	public $common; //通用
	public $obj_base;
	private $obj_smarty; //Smarty
	public $config; //配置
	public $alert; //语言 提示代码

	function __construct($str_pathTpl) { //构造函数
		$this->obj_base                   = $GLOBALS["obj_base"];
		$this->config                     = $this->obj_base->config;

		$this->obj_smarty                 = new Smarty(); //初始化 Smarty 对象
		$this->obj_smarty->template_dir   = $str_pathTpl;
		$this->obj_smarty->compile_dir    = BG_PATH_TPL_COMPILE;
		$this->obj_smarty->debugging      = BG_SWITCH_SMARTY_DEBUG; //调试模式

		$this->alert  = include_once(BG_PATH_LANG . $this->config["lang"] . "/alert.php"); //载入提示代码
	}


	/** 显示页面
	 * tplDisplay function.
	 *
	 * @access public
	 * @param mixed $str_tpl 模版文件
	 * @param string $arr_tplData (default: "") 模版数据
	 * @return void
	 */
	function tplDisplay($str_tpl, $arr_tplData = "") {
		switch (BG_VISIT_TYPE) {
			case "static":
			case "pstatic":
				$_str_tagUrl = BG_SITE_URL . BG_URL_ROOT . "tag/";
			break;
			default:
				$_str_tagUrl = BG_SITE_URL . BG_URL_ROOT . "index.php?mod=tag&act_get=list";
			break;
		}

		$this->common["tagUrl"]   = $_str_tagUrl;

		$this->obj_smarty->assign("alert", $this->alert);
		$this->obj_smarty->assign("common", $this->common);
		$this->obj_smarty->assign("config", $this->config);
		$this->obj_smarty->assign("tplData", $arr_tplData);

		$this->obj_smarty->registerPlugin("function", "call_display", "fn_callDisplay"); //注册自定义函数

		$this->obj_smarty->display($str_tpl); //显示
	}
}
?>