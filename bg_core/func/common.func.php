<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if(!defined("IN_BAIGO")) {
	exit("Access Denied");
}

/**
 * fn_rand function.
 *
 * @access public
 * @param int $num_rand (default: 32)
 * @return void
 */
function fn_rand($num_rand = 32) {
	$_str_char = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
	while (strlen($_str_rnd) < $num_rand) {
		$_str_rnd .= substr($_str_char, (rand(0, strlen($_str_char))), 1);
	}
	return $_str_rnd;
}


/**
 * fn_getIp function.
 *
 * @access public
 * @return void
 */
function fn_getIp($str_ipTrue = true) {
	if ($_SERVER) {
		if ($str_ipTrue) {
			if ($_SERVER["HTTP_X_FORWARDED_FOR"]) {
				$_arr_ips = explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]);
				foreach ($_arr_ips as $_value) {
					$_value = trim($_value);
					if ($_value != "unknown") {
						$_str_ip = $_value;
						break;
					}
				}
			} elseif ($_SERVER["HTTP_CLIENT_IP"]) {
				$_str_ip = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				if ($_SERVER["REMOTE_ADDR"]) {
					$_str_ip = $_SERVER["REMOTE_ADDR"];
				} else {
					$_str_ip = "0.0.0.0";
				}
			}
		} else {
			$_str_ip = $_SERVER["REMOTE_ADDR"];
		}
	} else {
		if ($str_ipTrue) {
			if (getenv("HTTP_X_FORWARDED_FOR")) {
				$_str_ip = getenv("HTTP_X_FORWARDED_FOR");
			} elseif (getenv("HTTP_CLIENT_IP")) {
				$_str_ip = getenv("HTTP_CLIENT_IP");
			} else {
				$_str_ip = getenv("REMOTE_ADDR");
			}
		} else {
			$_str_ip = getenv("REMOTE_ADDR");
		}
	}
	return $_str_ip;
}


/**
 * fn_seccode function.
 *
 * @access public
 * @return void
 */
function fn_seccode() {
	$_str_seccode = strtolower($_POST["seccode"]);
	if ($_str_seccode != $_SESSION["seccode_" . BG_SITE_SSIN]) {
		return false;
	} else {
		return true;
	}
}


/**
 * fn_token function.
 *
 * @access public
 * @param string $token_action (default: "mk")
 * @param string $token_method (default: "post")
 * @param string $cookie_method (default: "post")
 * @return void
 */
function fn_token($token_action = "mk", $session_method = "post", $cookie_method = "cookie") {
	switch ($token_action) {
		case "chk":
			switch ($session_method) {
				case "get":
					$_str_tokenSession = fn_getSafe($_GET["token_session"], "txt", "");
				break;
				default:
					$_str_tokenSession = fn_getSafe($_POST["token_session"], "txt", "");
				break;
			}

			switch ($cookie_method) {
				case "get":
					$_str_tokenCookie = fn_getSafe($_GET["token_cookie"], "txt", "");
				break;
				case "post":
					$_str_tokenCookie = fn_getSafe($_POST["token_cookie"], "txt", "");
				break;
				default:
					$_str_tokenCookie = $_COOKIE["token_cookie_" . BG_SITE_SSIN];
				break;
			}

			if (BG_SWITCH_TOKEN == true) {
				 if ($_str_tokenSession != $_SESSION["token_session_" . BG_SITE_SSIN] || $_str_tokenCookie != $_SESSION["token_cookie_" . BG_SITE_SSIN]) {
					$_str_return = false;
				 } else {
					$_str_return = true;
				 }
			} else {
				$_str_return = true;
			}
		break;

		default:
			if (BG_SWITCH_TOKEN == true) {
				$_num_tokenSessionDiff = $_SESSION["token_sessionTime_" . BG_SITE_SSIN] + 300; //session有效期
				if (!$_SESSION["token_session_" . BG_SITE_SSIN] || !$_SESSION["token_sessionTime_" . BG_SITE_SSIN] || $_num_tokenSessionDiff < time()) {
					$_str_tokenSession = fn_rand();
					$_SESSION["token_session_" . BG_SITE_SSIN]     = $_str_tokenSession;
					$_SESSION["token_sessionTime_" . BG_SITE_SSIN] = time();
				} else {
					$_str_tokenSession = $_SESSION["token_session_" . BG_SITE_SSIN];
				}

				$_num_tokenCookieDiff = $_SESSION["token_cookieTime_" . BG_SITE_SSIN] + 300; //cookie有效期
				if (!$_SESSION["token_cookie_" . BG_SITE_SSIN] || !$_SESSION["token_cookieTime_" . BG_SITE_SSIN] || $_num_tokenCookieDiff < time()) {
					$_str_tokenCookie = fn_rand();
					$_SESSION["token_cookie_" . BG_SITE_SSIN]      = $_str_tokenCookie;
					$_SESSION["token_cookieTime_" . BG_SITE_SSIN]  = time();
				} else {
					$_str_tokenCookie = $_SESSION["token_cookie_" . BG_SITE_SSIN];
				}

				$_str_return = $_str_tokenSession;
				setcookie("token_cookie_" . BG_SITE_SSIN, $_str_tokenCookie, time()+300);
			}
		break;
	}

	return $_str_return;
}

/*============清除全部cookie============
无返回
*/
function fn_clearCookie() {
	setcookie("cookie_ui", "", time()-3600);
	setcookie("cookie_lang", "", time()-3600);
}


/**
 * fn_getSafe function.
 *
 * @access public
 * @param mixed $str_string
 * @param string $str_type (default: "txt")
 * @param string $str_default (default: "")
 * @return void
 */
function fn_getSafe($str_string = "", $str_type = "txt", $str_default = "") {

	if ($str_string) {
		$_str_string = $str_string;
	} else {
		$_str_string = $str_default;
	}

	$_str_string = trim($_str_string);

	switch ($str_type) {
		case "int": //数值型
			if (is_numeric($_str_string)) {
				$_str_return = intval($_str_string); //如果是数值型则赋值
			} else {
				$_str_return = 0; //如果默认值为空则赋值为0
			}
		break;

		default: //默认
			$_str_return = htmlentities($_str_string, ENT_QUOTES, "UTF-8");
		break;

	}

	return $_str_return;
}


/**
 * fn_strlen_utf8 function.
 *
 * @access public
 * @param mixed $str
 * @return void
 */
function fn_strlen_utf8($str) {
	$i = 0;
	$count = 0;
	$len = strlen($str);
	while ($i < $len) {
		$chr = ord($str[$i]);
		$count++;
		$i++;
		if ($i >= $len) {
			break;
		}
		if ($chr & 0x80) {
			$chr <<= 1;
			while ($chr & 0x80) {
				$i++;
				$chr <<= 1;
			}
		}
	}
	return $count;
}


/**
 * fn_substr_utf8 function.
 *
 * @access public
 * @param mixed $str_string
 * @param mixed $begin
 * @param mixed $length
 * @return void
 */
function fn_substr_utf8($str_string, $begin, $length) {
	//對字串做URL Eecode
	$str_string = mb_substr($str_string, $begin, mb_strlen($str_string));
	$iString = urlencode($str_string);
	$lstrResult = "";
	$ilength = 0;
	$k = 0;
	do {
		$lstrChar = substr($iString, $k, 1);
		if ($lstrChar == "%") {
			$ThisChr = hexdec(substr($iString, $k+1, 2));
			if ($ThisChr >= 128) {
				if ($ilength + 3 < $length) {
					$lstrResult .= urldecode(substr($iString, $k, 9));
					$k = $k + 9;
					$ilength += 3;
				} else {
					$k = $k + 9;
					$ilength += 3;
				}
			} else {
				$lstrResult .= urldecode(substr($iString, $k, 3));
				$k = $k + 3;
				$ilength += 2;
			}
		} else {
			$lstrResult .= urldecode(substr($iString, $k, 1));
			$k = $k + 1;
			$ilength++;
		}
	}
	while ($k < strlen($iString) && $ilength < $length);
	return $lstrResult;
}

/**
 * fn_page function.
 *
 * @access public
 * @param mixed $num_total
 * @param mixed $num_per (default: BG_DEFAULT_PERPAGE)
 * @return void
 */
function fn_page($num_total, $num_per = BG_DEFAULT_PERPAGE) {

	$_num_pageThis = fn_getSafe($_GET["page"], "int", 1);

	if ($_num_pageThis < 1) {
		$_num_pageThis = 1;
	} else {
		$_num_pageThis = $_num_pageThis;
	}

	$_num_pageTotal = $num_total / $num_per;

	if (intval($_num_pageTotal) < $_num_pageTotal) {
		$_num_pageTotal = intval($_num_pageTotal) + 1;
	} elseif ($_num_pageTotal < 1) {
		$_num_pageTotal = 1;
	} else {
		$_num_pageTotal = intval($_num_pageTotal);
	}

	if ($_num_pageThis > $_num_pageTotal) {
		$_num_pageThis = $_num_pageTotal;
	}

	if ($_num_pageThis <= 1) {
		$_num_except = 0;
	} else {
		$_num_except = ($_num_pageThis - 1) * $num_per;
	}

	$_p = intval(($_num_pageThis - 1) / 10); //是否存在上十页、下十页参数
	$_begin = $_p * 10 + 1; //列表起始页
	$_end = $_p * 10 + 10; //列表结束页

	if ($_end >= $_num_pageTotal) {
		$_end = $_num_pageTotal;
	}

	return array(
		"page"    => $_num_pageThis,
		"p"       => $_p,
		"begin"   => $_begin,
		"end"     => $_end,
		"total"   => $_num_pageTotal,
		"except"  => $_num_except,
	);
}


/**
 * fn_jsonEncode function.
 *
 * @access public
 * @param string $arr_json (default: "")
 * @param string $method (default: "")
 * @return void
 */
function fn_jsonEncode($arr_json = "", $method = "") {
	if ($arr_json) {
		$arr_json = fn_eachArray($arr_json, $method);
		//print_r($method);
		$str_json = json_encode($arr_json); //json编码
	} else {
		$str_json = "";
	}
	return $str_json;
}


/**
 * fn_jsonDecode function.
 *
 * @access public
 * @param string $str_json (default: "")
 * @param string $method (default: "")
 * @return void
 */
function fn_jsonDecode($str_json = "", $method = "") {
	if ($str_json) {
		$arr_json = json_decode($str_json, true); //json解码
		$arr_json = fn_eachArray($arr_json, $method);
	} else {
		$arr_json = array();
	}
	return $arr_json;
}



/**
 * fn_eachArray function.
 *
 * @access public
 * @param mixed $arr
 * @param string $method (default: "encode")
 * @return void
 */
function fn_eachArray($arr, $method = "encode") {
	if (is_array($arr)) {
		foreach ($arr as $_key=>$_value) {
			if (is_array($_value)) {
				$arr[$_key] = fn_eachArray($_value, $method);
			}
		}
	} else {
		$arr = array();
	}
	return $arr;
}


/**
 * fn_baigoEncrypt function.
 *
 * @access public
 * @param mixed $str
 * @param mixed $rand
 * @return void
 */
function fn_baigoEncrypt($str, $rand, $is_md5 = false) {
	if ($is_md5) {
		$_str = $str;
	} else {
		$_str = md5($str);
	}
	$_str_return = md5($_str . $rand);
	return $_str_return;
}


/**
 * fn_regChk function.
 *
 * @access public
 * @param mixed $str_chk
 * @param mixed $str_reg
 * @param bool $str_wild (default: false)
 * @return void
 */
function fn_regChk($str_chk, $str_reg, $str_wild = false) {
	$_str_reg = trim($str_reg);
	$_str_reg = preg_quote($_str_reg, "/");

	if ($str_wild == true) {
		$_str_reg = str_replace("\\*", ".*", $_str_reg);
		$_str_reg = str_replace(" ", "", $_str_reg);
		$_str_reg = "/^(" . $_str_reg . ")$/i";
	} else {
		$_str_reg = "/(" . $_str_reg . ")$/i";
	}

	$_str_reg = str_replace("\|", "|", $_str_reg);
	$_str_reg = str_replace("|)", ")", $_str_reg);

	/*print_r($_str_reg . "<br>");
	preg_match($_str_reg, $str_chk, $aaaa);
	print_r($aaaa);*/

	return preg_match($_str_reg, $str_chk);
}


/**
 * fn_getPic function.
 *
 * @access public
 * @param mixed $_str_content
 * @return void
 */
function fn_getUpfile($_str_content) {
	//print_r($_str_content);

	$_pattern_1        = "/<img.*?src=[\"|']?__baigo__\/" . BG_NAME_UPFILE . "\/.*?[\"|']?\s.*?>/i";
	$_pattern_2        = "/\sid=[\"|']?baigo_.*?[\"|']?\s/i";
	$_str_upfileTemp   = str_replace(BG_UPFILE_URL, "__baigo__", $_str_content);
	$_str_upfileTemp   = str_replace("\\", "", $_str_upfileTemp);

	//print_r($_pattern_1);
	//print_r($_pattern_2);
	//print_r($_str_upfileTemp);

	preg_match($_pattern_1, $_str_upfileTemp, $_match_1);

	//print_r($_match_1);

	if ($_match_1[0]) {
		preg_match($_pattern_2, $_match_1[0], $_match_2);
		$_str_upfile      = trim($_match_2[0]);
		$_str_upfile      = str_replace("id=", "", $_match_2[0]);
		$_str_upfile      = str_replace("baigo_", "", $_str_upfile);
		$_str_upfile      = str_replace("\"", "", $_str_upfile);
		$_str_upfile      = trim($_str_upfile);
		$_num_upfileId    = str_replace("'", "", $_str_upfile);
	}

	//print_r($_match_2);

	return $_num_upfileId;
}

?>