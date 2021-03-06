<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

include_once(BG_PATH_FUNC . "cate.func.php"); //载入模板类
include_once(BG_PATH_CLASS . "tpl.class.php"); //载入模板类
include_once(BG_PATH_MODEL . "article.class.php"); //载入文章模型类
include_once(BG_PATH_MODEL . "cate.class.php"); //载入文章模型类
include_once(BG_PATH_MODEL . "cateBelong.class.php"); //载入文章模型类
include_once(BG_PATH_MODEL . "mark.class.php"); //载入文章模型类
include_once(BG_PATH_MODEL . "admin.class.php"); //载入文章模型类

/*-------------文章控制器-------------*/
class CONTROL_ARTICLE {

	private $obj_base;
	private $config;
	private $adminLogged;
	private $obj_tpl;
	private $mdl_article;
	private $mdl_cate;
	private $mdl_cateBelong;
	private $mdl_mark;
	private $mdl_admin;

	function __construct() { //构造函数
		$this->obj_base       = $GLOBALS["obj_base"]; //获取界面类型
		$this->config         = $this->obj_base->config;
		$this->adminLogged    = $GLOBALS["adminLogged"]; //获取已登录信息
		$this->obj_tpl        = new CLASS_TPL(BG_PATH_SYSTPL_ADMIN . $this->config["ui"]);; //初始化视图对象
		$this->mdl_article    = new MODEL_ARTICLE(); //设置文章对象
		$this->mdl_cate       = new MODEL_CATE(); //设置栏目对象
		$this->mdl_cateBelong = new MODEL_CATE_BELONG(); //设置栏目从属对象
		$this->mdl_mark       = new MODEL_MARK(); //设置标记对象
		$this->mdl_admin      = new MODEL_ADMIN(); //设置管理员对象
		$this->tplData = array(
			"adminLogged" => $this->adminLogged
		);
	}


	/** 文章表单
	 * ctl_form function.
	 *
	 * @access public
	 * @return void
	 */
	function ctl_form() {
		$_num_articleId = fn_getSafe($_GET["article_id"], "int", 0);

		if ($_num_articleId > 0) {
			$_arr_articleRow = $this->mdl_article->mdl_read($_num_articleId); //读取文章
			if ($_arr_articleRow["str_alert"] != "y120102") {
				return $_arr_articleRow;
				exit;
			}

			if ($this->adminLogged["admin_allow_sys"]["article"]["edit"] != 1 && $this->adminLogged["admin_allow_cate"][$_arr_articleRow["article_cate_id"]]["edit"] != 1 && $_arr_articleRow["article_admin_id"] != $this->adminLogged["admin_id"]) { //判断权限
				return array(
					"str_alert" => "x120303"
				);
				exit;
			}
			$_arr_belongRow = $this->mdl_cateBelong->mdl_list($_arr_articleRow["article_id"]); //读取从属数据
			foreach ($_arr_belongRow as $_value) {
				$_arr_articleRow["cate_ids"][] = $_value["belong_cate_id"];
			}
			$_arr_articleRow["cate_ids"][] = $_arr_articleRow["article_cate_id"];
		} else {
			if ($this->adminLogged["admin_allow_sys"]["article"]["approve"] == 1) {
				$_str_status = "pub";
			} else {
				$_str_status = "wait";
			}
			$_arr_articleRow = array(
				"article_status"    => $_str_status,
				"article_box"       => "normal",
				"article_time_pub"  => time(),
				"cate_ids"          => array(),
			);
		}

		$_arr_cateRows = $this->mdl_cate->mdl_list(1000, 0, "show");
		$_arr_markRows = $this->mdl_mark->mdl_list(1000, 0);

		if (!$_arr_cateRows) {
			return array(
				"str_alert" => "x110401",
			);
			exit;
		}

		$_arr_articleRow["cate_ids"] = array_unique($_arr_articleRow["cate_ids"]);

		$_arr_tpl = array(
			"cateRows"   => $_arr_cateRows, //栏目列表
			"markRows"   => $_arr_markRows, //标记列表
			"articleRow" => $_arr_articleRow, //栏目信息
		);

		$_arr_tplData = array_merge($this->tplData, $_arr_tpl);

		$this->obj_tpl->tplDisplay("article_form.tpl", $_arr_tplData);

		return array(
			"str_alert" => "y120102"
		);
	}


	/** 显示文章
	 * ctl_show function.
	 *
	 * @access public
	 * @param mixed $num_articleId
	 * @param int $num_ucId (default: 0)
	 * @return void
	 */
	function ctl_show() {
		$_num_articleId = fn_getSafe($_GET["article_id"], "int", 0);
		if ($_num_articleId == 0) {
			return array(
				"str_alert" => "x120212"
			);
			exit;
		}

		$_arr_articleRow = $this->mdl_article->mdl_read($_num_articleId); //读取文章
		if ($_arr_articleRow["str_alert"] != "y120102") {
			return $_arr_articleRow;
			exit;
		}

		if ($this->adminLogged["admin_allow_sys"]["article"]["browse"] != 1 && $this->adminLogged["admin_allow_cate"][$_arr_articleRow["article_cate_id"]]["browse"] != 1 && $_arr_articleRow["article_admin_id"] != $this->adminLogged["admin_id"]) { //判断权限
			return array(
				"str_alert" => "x120301"
			);
			exit;
		}

		$_arr_belongRow = $this->mdl_cateBelong->mdl_list($_arr_articleRow["article_id"]);
		foreach ($_arr_belongRow as $_value) {
			$_arr_articleRow["cate_ids"][] = $_value["belong_cate_id"];
		}
		$_arr_articleRow["cate_ids"][] = $_arr_articleRow["article_cate_id"];

		$_arr_cateRows = $this->mdl_cate->mdl_list(1000, 0, "show");
		$_arr_markRows = $this->mdl_mark->mdl_list(1000, 0);

		$_arr_articleRow["cate_ids"] = array_unique($_arr_articleRow["cate_ids"]);

		$_arr_tpl = array(
			"cateRows"   => $_arr_cateRows, //栏目列表
			"markRows"   => $_arr_markRows, //标记列表
			"articleRow" => $_arr_articleRow, //栏目信息
		);

		$_arr_tplData = array_merge($this->tplData, $_arr_tpl);

		$this->obj_tpl->tplDisplay("article_show.tpl", $_arr_tplData);

		return array(
			"str_alert" => "y120102"
		);
	}


	/** 列出文章
	 * ctl_list function.
	 *
	 * @access public
	 * @return void
	 */
	function ctl_list() {
		$_num_page    = fn_getSafe($_GET["page"], "int", 1);
		$_act_get     = fn_getSafe($_GET["act_get"], "txt", "");
		$_str_key     = fn_getSafe($_GET["key"], "txt", "");
		$_str_tag     = fn_getSafe($_GET["tag"], "txt", "");
		$_str_year    = fn_getSafe($_GET["year"], "txt", "");
		$_str_month   = fn_getSafe($_GET["month"], "txt", "");
		$_str_status  = fn_getSafe($_GET["status"], "txt", "");
		$_str_box     = fn_getSafe($_GET["box"], "txt", "");
		$_num_cateId  = fn_getSafe($_GET["cate_id"], "int", 0);
		$_num_markId  = fn_getSafe($_GET["mark_id"], "int", 0);
		$_num_adminId = fn_getSafe($_GET["admin_id"], "int", 0);

		$_arr_search = array(
			"page"       => $_num_page,
			"act_get"    => $_act_get,
			"key"        => $_str_key,
			"tag"        => $_str_tag,
			"year"       => $_str_year,
			"month"      => $_str_month,
			"status"     => $_str_status,
			"box"        => $_str_box,
			"cate_id"    => $_num_cateId,
			"mark_id"    => $_num_markId,
			"admin_id"   => $_num_adminId,
		);

		if ($_num_cateId != 0) {
			$_arr_cate       = $this->mdl_cate->mdl_list(1000, 0, "show", "", $_num_cateId);
			$_arr_cateIds    = fn_cateIds($_arr_cate);
			$_arr_cateIds[]  = $_num_cateId;
		} else {
			$_arr_cateIds = false;
		}

		if ($_arr_search["admin_id"] == 0) {
			if ($this->adminLogged["admin_allow_sys"]["article"]["del"] == 1) {
				$_num_adminId = 0;
			} else {
				$_num_adminId = $this->adminLogged["admin_id"];
			}
		}

		if ($_str_box == "draft") {
			$_num_adminId = $this->adminLogged["admin_id"];
		}

		if ($_num_markId > 0) {
			$_arr_markIds = array($_num_markId);
		} else {
			$_arr_markIds = false;
		}

		$_num_articleCount            = $this->mdl_article->mdl_count($_str_key, $_str_year, $_str_month, $_str_status, $_str_box, $_arr_cateIds, $_arr_markIds, false, $_num_adminId);
		$_arr_page                    = fn_page($_num_articleCount); //取得分页数据
		$_str_query                   = http_build_query($_arr_search);
		$_arr_articleRows             = $this->mdl_article->mdl_list(BG_DEFAULT_PERPAGE, $_arr_page["except"], $_str_key, $_str_year, $_str_month, $_str_status, $_str_box, $_arr_cateIds, $_arr_markIds, false, $_num_adminId);

		$_arr_articleCount["all"]     = $this->mdl_article->mdl_count();
		$_arr_articleCount["draft"]   = $this->mdl_article->mdl_count("", "", "", "", "draft", false, false, false, $this->adminLogged["admin_id"]);

		if ($this->adminLogged["admin_allow_sys"]["article"]["del"] == 1) {
			$_num_adminId = 0;
		} else {
			$_num_adminId = $this->adminLogged["admin_id"];
		}

		$_arr_articleCount["recycle"] = $this->mdl_article->mdl_count("", "", "", "", "recycle", false, false, false, $_num_adminId);

		$_arr_articleYear             = $this->mdl_article->mdl_year();
		$_arr_cateRows                = $this->mdl_cate->mdl_list(1000, 0, "show");
		$_arr_markRows                = $this->mdl_mark->mdl_list(1000, 0);

		foreach ($_arr_articleRows as $_key=>$_value) {
			$_arr_cateRow = $this->mdl_cate->mdl_read($_value["article_cate_id"]);
			if ($_arr_cateRow["str_alert"] != "y110102" && $_value["article_cate_id"] > 0) {
				$_arr_unknowCate[] = $_value["article_id"];
			} else {
				$_arr_articleRows[$_key]["article_cate_name"] = $_arr_cateRow["cate_name"];
			}
			$_arr_adminRow                                   = $this->mdl_admin->mdl_read($_value["article_admin_id"]);
			$_arr_articleRows[$_key]["article_admin_name"]   = $_arr_adminRow["admin_name"];
			$_arr_articleRows[$_key]["article_admin_note"]   = $_arr_adminRow["admin_note"];
		}

		if ($_arr_unknowCate) {
			$this->mdl_article->mdl_unknowCate($_arr_unknowCate);
		}

		$_arr_tpl = array(
			"query"          => $_str_query,
			"pageRow"        => $_arr_page,
			"search"         => $_arr_search,
			"cateRows"       => $_arr_cateRows,
			"markRows"       => $_arr_markRows, //标记列表
			"articleRows"    => $_arr_articleRows,
			"articleCount"   => $_arr_articleCount,
			"articleYear"    => $_arr_articleYear,
		);

		$_arr_tplData = array_merge($this->tplData, $_arr_tpl);

		$this->obj_tpl->tplDisplay("article_list.tpl", $_arr_tplData);

		return array(
			"str_alert" => "y120301"
		);
	}
}
?>