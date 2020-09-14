<?php
declare (strict_types = 1);

// +----------------------------------------------------------------------
// | 验证类
// +----------------------------------------------------------------------
// | Date: 2020-08-14
// +----------------------------------------------------------------------
// | Author: xiaobai
// +----------------------------------------------------------------------

namespace xiaobai6;

class Verify
{
    /**
     * 验证手机号格式
     * @param string $mobile  手机号
     * @return bool
     */
    static public function isMobile(string $mobile = '')
    {
        if (empty($mobile)) {
            return false;
        }
        if (!preg_match("/1[123456789]{1}\d{9}$/", $mobile)) {
            return false;
        }
        return true;
    }

    /**
     * 验证座机号码格式
     * @param string $tel  座机号
     * @return bool
     */
    static public function isTel(string $tel = '')
    {
        if (empty($tel)) {
            return false;
        }
        if (!preg_match("/^([0-9]{3,4}-)?[0-9]{7,8}$/", $tel)) {
            return false;
        }
        return true;
    }

    /**
     * 验证邮箱
     * @param string $email   邮箱地址
     * @return bool
     */
    static public function isEmail(string $email = '')
    {
        if (empty($email)) {
            return false;
        }
        if (!preg_match("/[a-z0-9]([a-z0-9]*[-_\.]?[a-z0-9]+)*@[a-z0-9]*([-_\.]?[a-z0-9]+)+[\.][a-z0-9]{2,3}([\.][a-z0-9]{2})?$/i",$email)) {
            return false;
        }
        return true;
    }

    /**
     * 验证字符串
     * @param string $str     字符串
     * @param int    $type    验证类型
     * @return bool
     */
    static public function isString(string $str = '', int $type = 1)
    {
        if (empty($str)) {
            return false;
        }
        //验证字母数字
        if ($type == 1) {
            if(preg_match("/^[a-zA-Z0-9\/\+]+$/",$str)){
                return true;
            }
        }
        return false;
    }

    /**
     * 验证字符串长度，一个字母和汉字都是一个长度计算
     * @param string $str       字符串
     * @param int $max_length   最大长度
     * @param int $min_length   最小长度
     * @param string $unicode   字符编码，默认utf-8
     * @return bool
     */
    static public function validateZhLength(string $str = '', int $max_length = 10, int $min_length = 0, string $unicode = 'utf-8')
    {
        if (!isset($str) || $str == '') {
            return false;
        }
        if (mb_strlen($str, $unicode) > $max_length || mb_strlen($str, $unicode) < $min_length) {
            return false;
        }
        return true;
    }

    /**
     * 验证身份证号
     * @param string $idcard  身份证号码
     * @return bool
     */
    static public function checkIdCard(string $idCard = '')
    {
        if (empty($idCard)) {
            return false;
        }
        $City         = array(11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");
        $iSum         = 0;
        $idCardLength = strlen($idCard);
        // 长度验证
        if (!preg_match('/^\d{17}(\d|x)$/i', $idCard) and !preg_match('/^\d{15}$/i', $idCard)) {
            return false;
        }
        // 地区验证
        if (!array_key_exists(intval(substr($idCard, 0, 2)), $City)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15) {
            $sBirthday = '19' . substr($idCard, 6, 2) . '-' . substr($idCard, 8, 2) . '-' . substr($idCard, 10, 2);
            //判断年月日的合法性
            if (checkdate(substr($idCard, 8, 2), substr($idCard, 10, 2), '19' . substr($idCard, 6, 2)) == false) {
                return false;
            }
            $d  = new \DateTime($sBirthday);
            $dd = $d->format('Y-m-d');
            if ($sBirthday != $dd) {
                return false;
            }
            $idCard = substr($idCard, 0, 6) . "19" . substr($idCard, 6, 9);     // 15to18
            $Bit18  = self::getVerifyBit($idCard);                              // 算出第18位校验码
            $idCard = $idCard . $Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idCard, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return false;
        }
        //判断年月日的合法性
        if (checkdate(substr($idCard, 10, 2), substr($idCard, 12, 2), substr($idCard, 6, 4)) == false) {
            return false;
        }

        //18位身份证处理
        $sBirthday = substr($idCard, 6, 4) . '-' . substr($idCard, 10, 2) . '-' . substr($idCard, 12, 2);
        $d         = new \DateTime($sBirthday);
        $dd        = $d->format('Y-m-d');
        if ($sBirthday != $dd) {
            return false;
        }
        //身份证编码规范验证
        $idcard_base = substr($idCard, 0, 17);
        if (strtoupper(substr($idCard, 17, 1)) != self::getVerifyBit($idcard_base)) {
            return false;
        }
        return true;
    }

    /**
     * 计算身份证校验码，根据国家标准GB 11643-1999
     * @param string $idcard_base   身份证号
     * @return bool|mixed
     */
    static protected function getVerifyBit(string $idcard_base = '')
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum           = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod           = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }

    /**
     * 根据生日返回当前年龄
     * @param string $birthday  生日，格式Y-m-d
     * @return bool|false|int
     */
    static public function birthday(string $birthday = '')
    {
        $age = strtotime($birthday);
        if ($age === false) {
            return false;
        }
        list($y1, $m1, $d1) = explode("-", date("Y-m-d", $age));
        $now = strtotime("now");
        list($y2, $m2, $d2) = explode("-", date("Y-m-d", $now));
        $age = $y2 - $y1;
        if ((int)($m2 . $d2) < (int)($m1 . $d1)) {
            $age -= 1;
        }
        return $age; 
    }

    /**
     * 过滤字符串
     * @param string $string    待验证字符串
     * @param string $verify    验证内容，字符串
     * @return string
     */
    static public function verifyString(string $string = '', string $verify = 'trim,htmlspecialchars')
    {
        $verify = explode(',', $verify);
        if (in_array('trim', $verify)) {
            $string = trim($string);
        }
        if (in_array('strip_tags', $verify)) {
            $string = strip_tags($string);
        }
        if (in_array('htmlspecialchars', $verify)) {
            $string = htmlspecialchars($string);
        }
        if (in_array('remove_xss', $verify)) {
            $string = self::removeXss($string);
        }
        return $string;
    }

    /**
     * 移除xss攻击
     * @param string $val       字符串
     * @return mixed|string
     */
    static public function removeXss(string $val = '')
    {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
        // this prevents some character re-spacing such as <java\0script>
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);

        // straight replacements, the user should never need these since they're normal characters
        // this prevents like <IMG SRC=@avascript:alert('XSS')>
        $search  = 'abcdefghijklmnopqrstuvwxyz';
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $search .= '1234567890!@#$%^&*()';
        $search .= '~`";:?+/={}[]-_|\'\\';
        for ($i = 0; $i < strlen($search); $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
        }

        // now the only remaining whitespace attacks are \t, \n, and \r
        $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
        $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
        $ra  = array_merge($ra1, $ra2);

        $found = true; // keep replacing as long as the previous round replaced something
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < sizeof($ra); $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen($ra[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= '(';
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)';
                        $pattern .= '|';
                        $pattern .= '|(&#0{0,8}([9|10|13]);)';
                        $pattern .= ')*';
                    }
                    $pattern .= $ra[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }
}