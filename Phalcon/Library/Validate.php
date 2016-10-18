<?php
/**
 * @Author: Peak
 * @Date:   2015-12-30 20:06:46
 * @example
     *     $params = array(
     *         'weight'=>100.00,
     *         'productDeclaredValue'=>''
     *     );
     *     $fields = array(     
     *         'weight' =>array('name'=>'产品毛重(kg)',  '_validate'=>'require|numeric|maxLength:8|default:xxx|fixed:aaa'),
     *         'price'  =>array('name'=>'申报价格',      '_validate'=>'require|numeric|maxLength:8|require_if:price:elt:0',
     *             '_regexp'=>array(
     *                 '/\S+/'=>'不能为空',
     *                 '/^[0-9]{1,10}(\.[0-9]{1,9})?$/'=>'不符合价格格式规范',
     *             ),
     *         ),
     *     );
     *     $r = self::validParams($params,$fields);
     *     if($r!==true) {
     *         return $r; //错误信息
     *     }
 */
namespace Phalcon\Library;

class Validate {
    public static $vFuncNames = array(
        'isEmail'=>'Email地址',
        'isMobilePhone'=>'手机号',
        'isTelephone'=>'电话号码、手机号或400号电话格式',
        'isMemberAccount'=>'手机号或邮箱格式',
        'isUserName'=> '由字母、数字、空格、横线和下划线组成的字符串',
        'isChinese'=>'由中文、字母、数字、空格、横线和下划线组成的字符串',
        'isJson'=>'Json数据格式参数',
        'isMd5'=>'MD5字符串',
        'isSha1'=>'SHA1字符串',
        'isToken'=>'TOKEN字符串（字母、数字、等号）',
        'isLetter'=>'字母',
        'isUpper'=>'大写字母',
        'isLower'=>'小写字母',
        'isFloat'=>'浮点型',
        'isUnsignedFloat'=>'无符号浮点型',
        'isOptFloat'=>'为空或是浮点型',
        'isName'=>'不含标点符号',
        'isPrice'=>'价格格式',
        'isCleanHtml'=>'不含js代码',
        'isDateTime'=>'日期时间格式（YYYY-MM-DD HH:II:SS）',
        'isDate'=>'日期格式（YYYY-MM-DD）',
        'isYear'=>'4位整数',
        'isInt'=>'整形',
        'isUnsignedInt'=>'无符号整形',
        'isUrl'=>'URL格式',
        'isUrls'=>'URL格式',
        'isIp4'=>'IP4地址格式',
        'isAbsoluteUrl'=>'绝对URL格式',
        'isFileName'=>'文件名格式（字母、数字、横线、下划线、点号）',
        'isPath'=>'由字母、数字、横线、下划线、点号、斜杠组成',
        'isPaths'=>'由字母、数字、横线、下划线、点号、斜杠组成，多个由分号";"隔开',
        'isBirthDate'=>'规范的生日格式',
        'isTimestamp'=>'UNIX时间戳',
        'isIMEI'=>'IMEI格式字符串',
        'isISBN'=>'ISBN格式字符串',
        'isIdCard'=>'规范的身份证号',
        'isNickname'=>'由中文、字母和下划线组成的字符串',
        'isPasswd'=>'由6~32个字母、数字、标点符[ ~!@#$%^&*_- ]号组成的字符串',
        'isColor'=>'3个不大于255的数字，由逗号分隔',
        'isIntId'=>'数字',
        'isIntIds'=>'数字，多个用逗号“,”隔开',
        'isMixId'=>'由字母、数字、下划线组成',
        'isMixIds'=>'由字母、数字、下划线组成，多个用逗号“,”隔开',
        'unEmpty'=>'非空',
        'isFields'=>'由字母、数字、下划线、“`”、圆括号、空格或星号组成，多个用逗号“,”隔开',
        'isSortRule'=>'符合排序规则，格式如“id desc”，多个用逗号“,”隔开',
        'isEname'=>'由字母、空格、“`”组成',
        'isArray'=>'数组',
    );

    public static $vConditions = array(
        'eq' =>'等于',
        '==' =>'等于',
        'neq'=>'不等于',
        '!=' =>'不等于',
        'heq'=>'恒等于',
        '==='=>'恒等于',
        'gt' =>'大于',
        '>'  =>'大于',
        'elg'=>'大于等于',
        '>=' =>'大于等于',
        'lt' =>'小于',
        '<'  =>'小于',
        'elt'=>'小于等于',
        '<=' =>'小于等于',
    );

    public static function unEmpty($str) {
        return is_string($str) ? preg_match("/\S+/", $str) : !empty($str);
    }

    public static function isEmail($email) {
        return !empty($email) && preg_match(Tools::cleanNonUnicodeSupport('/^[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z0-9]+$/ui'), $email);
    }

    public static function isMobilePhone($mobilePhone) {
        return preg_match("/^1[34578][0-9]{9}$/", $mobilePhone);
    }

    public static function isMemberAccount($data) {
        return self::isMobilePhone($data) || self::isEmail($data);
    }

    public static function isUserName($data) {
        return preg_match("/^\w+[\s\w-]*\w+$/", $data);
    }

    public static function isTelephone($tel) {
        $regxArr = array(
            'sj'  =>  '/^(\+?86-?)?1[34578][0-9]{9}$/',
            'tel' =>  '/^(010|02\d{1}|0[3-9]\d{2})-\d{7,9}(-\d+)?$/',
            '400' =>  '/^400(-\d{3,4}){2}$/',
        );
        foreach($regxArr as $regx) {
            if(preg_match($regx, $tel ))  return true;
        }
        return false;
    }

    public static function isJson($str) {
        json_decode($str);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function isChinese($data) {
        return preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_-]+[\x{4e00}-\x{9fa5}a-zA-Z0-9_\s-]*$/us", $data);
    }

    public static function isMd5($md5) {
        return preg_match('/^[a-f0-9A-F]{32}$/', $md5);
    }

    public static function isSha1($sha1) {
        return preg_match('/^[a-fA-F0-9]{40}$/', $sha1);
    }

    public static function isToken($token) {
        return preg_match('/^[a-zA-Z0-9=]+$/', $token);
    }

    public static function isLetter($str) {
        return preg_match('/^[a-zA-Z]+$/', $str);
    }

    public static function isUpper($str) {
        return preg_match('/^[A-Z]+$/', $str);
    }

    public static function isLower($str) {
        return preg_match('/^[a-z]+$/', $str);
    }

    public static function isFloat($float) {
        return strval((float)$float) == strval($float);
    }

    public static function isUnsignedFloat($float) {
        return strval((float)$float) == strval($float) && $float >= 0;
    }

    public static function isOptFloat($float) {
        return empty($float) || Validate::isFloat($float);
    }

    public static function isName($name) {
        return preg_match(Tools::cleanNonUnicodeSupport('/^[^!<>,;?=+()@#"°{}$%:]+$/u'), stripslashes($name));
    }

    public static function isAlias($alias) {
        return preg_match('/^[a-zA-Z-]{4-12}$/u', $alias);
    }

    public static function isPrice($price) {
        return preg_match('/^[0-9]{1,10}(\.[0-9]{1,9})?$/', $price);
    }

    public static function isNegativePrice($price) {
        return preg_match('/^[-]?[0-9]{1,10}(\.[0-9]{1,9})?$/', $price);
    }

    public static function isSearch($search) {
        return preg_match('/^[^<>;=#{}]{1,64}$/u', $search);
    }

    public static function isGenericName($name) {
        return preg_match(Tools::cleanNonUnicodeSupport('/^[^<>;=+@#"°{}$%:]+$/u'), stripslashes($name));
    }

    public static function isMessage($message) {
        return !empty($message) && !preg_match('/[<>{}]/i', $message);
    }

    public static function isCleanHtml($html) {
        $events = 'onmousedown|onmousemove|onmmouseup|onmouseover|onmouseout|onload|onunload|onfocus|onblur|onchange';
        $events .= '|onsubmit|ondblclick|onclick|onkeydown|onkeyup|onkeypress|onmouseenter|onmouseleave|onerror|onselect|onreset|onabort|ondragdrop|onresize|onactivate|onafterprint|onmoveend';
        $events .= '|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onmove';
        $events .= '|onbounce|oncellchange|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondeactivate|ondrag|ondragend|ondragenter|onmousewheel';
        $events .= '|ondragleave|ondragover|ondragstart|ondrop|onerrorupdate|onfilterchange|onfinish|onfocusin|onfocusout|onhashchange|onhelp|oninput|onlosecapture|onmessage|onmouseup|onmovestart';
        $events .= '|onoffline|ononline|onpaste|onpropertychange|onreadystatechange|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onsearch|onselectionchange';
        $events .= '|onselectstart|onstart|onstop';

        return (!preg_match('/<[ \t\n]*script/ims', $html) && !preg_match('/(' . $events . ')[ \t\n]*=/ims', $html) && !preg_match('/.*script\:/ims', $html) && !preg_match('/<[ \t\n]*i?frame/ims', $html));
    }

    public static function isPasswd($passwd, $size = 6) {
        return preg_match('/^[\w~!@#$%^&*_-]{' . $size . ',32}$/ui', $passwd);
    }

    public static function isDateTime($date) {
        return (bool)preg_match('/^([0-9]{4})-((0?[0-9])|(1[0-2]))-((0?[0-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})$/', $date);
    }

    public static function isDate($date) {
        if (!preg_match('/^([0-9]{4})-((0?[1-9])|(1[0-2]))-((0?[1-9])|([1-2][0-9])|(3[01]))$/ui', $date, $matches))
            return false;

        return checkdate(intval($matches[2]), intval($matches[5]), intval($matches[0]));
    }

    public static function isYear($year) {
        return preg_match("/^\d{4}$/",$year);
    }

    public static function isMonth($month) {
        return preg_match("/^\d{1,2}$/",$month) && $month>0 && $month<13;
    }

    public static function isTimestamp($time) {
        //return ctype_digit($time) && $time <= 2147483647;
        return (int)$time >= 0 && strtotime(date('Y-m-d H:i:s', $time)) === (int)$time;
    }

    public static function isBirthDate($date) {
        if (empty($date) || $date == '0000-00-00')
            return true;
        if (preg_match('/^([0-9]{4})-((?:0?[1-9])|(?:1[0-2]))-((?:0?[1-9])|(?:[1-2][0-9])|(?:3[01]))([0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $birth_date))
        {
            if ($birth_date[1] > date('Y') && $birth_date[2] > date('m') && $birth_date[3] > date('d'))
                return false;

            return true;
        }

        return false;
    }

    public static function isBool($bool) {
        return $bool === null || is_bool($bool) || preg_match('/^0|1$/', $bool);
    }

    public static function isOrderWay($way) {
        return ($way === 'ASC' | $way === 'DESC' | $way === 'asc' | $way === 'desc');
    }

    public static function isInt($value) {
        return ((string)(int)$value === (string)$value || $value === false);
    }

    public static function isUnsignedInt($value) {
        return (preg_match('#^[0-9]+$#', (string)$value) && $value < 4294967296 && $value >= 0);
    }

    public static function isPercentage($value) {
        return (Validate::isFloat($value) && $value >= 0 && $value <= 100);
    }

    public static function isUnsignedId($id) {
        return Validate::isUnsignedInt($id); /* Because an id could be equal to zero when there is no association */
    }

    public static function isNullOrUnsignedId($id) {
        return $id === null || Validate::isUnsignedId($id);
    }

    public static function isLoadedObject($object) {
        return is_object($object) && $object->id;
    }

    public static function isUrl($url) {
        return preg_match('/^[~:#,%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
    }

    public static function isUrls($url) {
        return preg_match('/^[~:#,%&_=\(\)\.\? \+\-@\/a-zA-Z0-9;]+$/', $url);
    }

    public static function isUrlOrEmpty($url) {
        return empty($url) || self::isUrl($url);
    }

    public static function isAbsoluteUrl($url) {
        return preg_match('/^https?:\/\/[!,:#%&_=\(\)\.\? \+\-@\/a-zA-Z0-9]+$/', $url);
    }

    public static function isMySQLEngine($engine) {
        return (in_array($engine, array('InnoDB', 'MyISAM')));
    }

    public static function isUnixName($data) {
        return preg_match('/^[a-z0-9\._-]+$/ui', $data);
    }

    public static function isFileName($name) {
        return preg_match('/^[a-zA-Z0-9_.-]+$/', $name);
    }

    /**
     * [isPaths 判断文件路径（含文件名）]
     * @param  [type]  $path [description]
     * @return boolean       [description]
     */
    public static function isPath($path) {
        return preg_match('/^[a-zA-Z0-9_.-\/]+$/', $path);
    }

    /**
     * [isPaths 判断多个文件路径（含文件名）]
     * @param  [type]  $path [description]
     * @return boolean       [description]
     */
    public static function isPaths($path) {
        return preg_match('/^[a-zA-Z0-9_.-;\/]+$/', $path);
    }

    public static function isDirName($dir) {
        return self::isFileName($dir);
    }

    public static function isCookie($data) {
        return (is_object($data) && (get_class($data) == 'Cookie' && get_class($data) == 'CookieModel'));
    }

    public static function isOptUnsignedId($id) {
        return is_null($id) OR self::isUnsignedId($id);
    }

    public static function isString($data) {
        return !empty($data) && is_string($data);
    }

    public static function isSerializedArray($data) {
        return $data === null || (is_string($data) && preg_match('/^a:[0-9]+:{.*;}$/s', $data));
    }

    public static function isIp4($data) {
        $ary = explode('.', $data);
        if (!preg_match('/[^\.\d]/', $data) && count($ary) == 4 && $ary[0] >= 0 && $ary[1] >= 0 && $ary[2] >= 0 && $ary[3] >= 0 && $ary[0] <= 255 && $ary[1] <= 255 && $ary[2] <= 255 && $ary[3] <= 255
        )
            return true;
        else
            return false;
    }

    public static function isIMEI($data) {
        return preg_match('/^[0-9a-z]{15}$/i', $data);
    }

    public static function isISBN($isbn) {
        return preg_match('/^[0-9]{13}$/', $isbn);
    }

    public static function isPublishTime($time) {
        return preg_match('/^[0-9]{4}-[0-9]{2}$/', $time);
    }

    public static function isNickname($data) {
        return preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z_]{2,16}$/u", $data);
    }

    public static function isOptNickname($data) {
        if ($data == null || self::isNickname($data))
        {
            return true;
        }

        return false;
    }

    public static function isColor($color) {
        return preg_match("/^\d{1,3},\d{1,3},\d{1,3}$/", $color);
    }

    public static function isNumber($data) {
        return preg_match("/^-?[0-9]+$/u", $data);
    }

    public static function isExpressNumber($data) {
        return preg_match('/^[0-9A-Za-z]+$/', $data);
    }

    public static function isIdCard($id_card){
        if(strlen($id_card)==18){
            return self::idcard_checksum18($id_card);
        }elseif((strlen($id_card)==15)){
            $id_card=self::idcard_15to18($id_card);
            return self::idcard_checksum18($id_card);
        }else{
            return false;
        }
    }
    // 计算身份证校验码，根据国家标准GB 11643-1999 
    public static function idcard_verify_number($idcard_base){
        if(strlen($idcard_base)!=17){
            return false;
        }
        //加权因子 
        $factor=array(7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2);
        //校验码对应值 
        $verify_number_list=array('1','0','X','9','8','7','6','5','4','3','2');
        $checksum=0;
        for($i=0;$i<strlen($idcard_base);$i++){
            $checksum += substr($idcard_base,$i,1) * $factor[$i];
        }
        $mod=$checksum % 11;
        $verify_number=$verify_number_list[$mod];
        return $verify_number;
    }
    // 将15位身份证升级到18位 
    public static function idcard_15to18($idcard){
        if(strlen($idcard)!=15){
            return false;
        }else{
            // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码 
            if(array_search(substr($idcard,12,3),array('996','997','998','999')) !== false){
                $idcard=substr($idcard,0,6).'18'.substr($idcard,6,9);
            }else{
                $idcard=substr($idcard,0,6).'19'.substr($idcard,6,9);
            }
        }
        $idcard=$idcard.self::idcard_verify_number($idcard);
        return $idcard;
    }
    // 18位身份证校验码有效性检查 
    public static function idcard_checksum18($idcard){
        if(strlen($idcard)!=18){
            return false;
        }
        $idcard_base=substr($idcard,0,17);
        if(self::idcard_verify_number($idcard_base)!=strtoupper(substr($idcard,17,1))){
            return false;
        }else{
            return true;
        }
    }

    

    /**
     * [isIntId 整形ID]
     * @param  [type]  $data  [description]
     * @return boolean        [description]
     */
    public static function isIntId($data) {
        return preg_match("/^[0-9]+$/u", $data);
    }

    /**
     * [isIntIds 逗号分割的多个id]
     * @param  [type]  $data  [description]
     * @return boolean        [description]
     */
    public static function isIntIds($data) {
        if(is_string($data) || is_numeric($data))
            return preg_match("/^[0-9,]+$/u", $data);
        return false;
    }
    
    /**
     * [isMixId 混合型ID]
     * @param  [type]  $data  [description]
     * @return boolean        [description]
     */
    public static function isMixId($data) {
        return preg_match("/^[\w]+$/u", $data);
    }

    /**
     * [isMixIds 逗号分割的多个id]
     * @param  [type]  $data  [description]
     * @return boolean        [description]
     */
    public static function isMixIds($data) {
        return preg_match("/^[\w,]+$/u", $data);
    }

    public static function isFields($fields='') {
        $fields = empty($fields) ? '*' : $fields;
        return preg_match("/^[a-zA-Z_*`][\w\s]*(,[a-zA-Z_`][(`\w`)\s]+[`]?)*$/u", $fields);
    }

    public static function isSortRule($sortBy) {
        return preg_match("/^([a-zA-Z_][\w]+\s(asc|desc|ASC|DESC),?)*$/u", $sortBy);
    }

    public static function isEname($str) {
        return preg_match("/^[a-zA-Z]+[a-zA-Z`\s]+[a-zA-Z]+$/",$str);
    }

    public static function isArray($arr) {
        return is_array($arr);
    }






    /**
     * validParams 验证参数
     * @param array &$params 参数(关联数组)
     * @param array $fields 字段规范
     * @param bool $multiErr 是否返回多个错误信息
     * @return mixed 成功返回true，失败返回字符串
     */
    public static function validParams(&$params, $fields=array(), $multiErr=false) {
        if(empty($fields)) {
            return "未定义任何参数验证规范";
        }
        if($multiErr===true) $msgs = array();
        foreach($fields as $field=>$item) {
            $msg = self::checkData($field,$item,$params);
            if($msg===true) {
                continue;
            } else {
                if($multiErr===true) $msgs[$field]=$msg;
                else return $msg;
            }
        }
        if($multiErr===true && !empty($msgs)) return $msgs;
        $params = array_intersect_key($params,array_flip(array_keys($fields)));
        return true;
    }

    /**
     * 检查数据是否符合要求
     * @param string $field 字段
     * @param array $item 字段规范
     * @param array &$params 参数(关联数组)
     * @return mixed 成功返回true，失败返回字符串
     */
    private static function checkData($field,$item,&$params)
    {
        if(empty($field) || empty($item) || ( !isset($item['_validate']) && !isset($item['_regexp']) )) return true;
        if(!empty($item['_validate'])) {
            $validcond = explode('|',$item['_validate']);
            if(isset($params[$field]) && in_array('trim',$validcond)) { //去除两边空格
                $params[$field]= trim($params[$field]);
            }
            $allowEmpty = in_array('allowEmpty',$validcond);
            $unsetEmpty = in_array('unsetEmpty',$validcond);
            foreach($validcond as $vi) {
                $issetVal = isset($params[$field]);
                if($vi=='require' && (!$issetVal || (is_string($params[$field]) ? !preg_match("/\S+/", $params[$field]) : empty($params[$field]))  ) ) {
                    return '“'.$item['name'].'”不能为空';
                }
                else if(substr($vi,0,11)=='require_if:' && (!$issetVal || $params[$field]=='')) { //某条件下必填
                    $cond = explode(':',substr($vi,11));
                    if(!empty($cond) && count($cond)==3 && isset($params[$cond[0]])) {
                        if(self::checkCondition($params[$cond[0]],$cond[1],$cond[2])) {
                            return '“'.$item['name'].'”不能为空';
                        }
                    }
                }
                else if(substr($vi,0,6)=='fixed:') { //设置固定值
                    $params[$field]= substr($vi,6);
                }
                else if(substr($vi,0,8)=='default:' && (!$issetVal || trim($params[$field])=='')) { //设置默认值
                    $params[$field]= substr($vi,8);
                }
                else if($issetVal && $allowEmpty && empty($params[$field])) {
                    return true;
                }
                else if($issetVal && $unsetEmpty && (empty($params[$field]) && !is_numeric($params[$field]))) {
                    unset($params[$field]);
                    return true;
                }

                if($issetVal && ( (!empty($params[$field]) || $params[$field]===0) || (!$allowEmpty || !$unsetEmpty) )) { //非空参数检查
                    if($vi=='numeric' && !is_numeric($params[$field])) {
                        return '“'.$item['name'].'”参数值必须是数字';
                    }
                    else if(isset(self::$vFuncNames[$vi]) && !call_user_func_array(array(__CLASS__,$vi),array($params[$field]))) {
                        return '“'.$item['name'].'”参数值必须是'.self::$vFuncNames[$vi];
                    }
                    else if(substr($vi,0,10)=='maxLength:' && strlen($params[$field])>intval(substr($vi,10))) {
                        return '“'.$item['name'].'”参数值字符长度不能超过'.substr($vi,10);
                    }
                    else if(substr($vi,0,10)=='mimLength:' && strlen($params[$field])<intval(substr($vi,10))) {
                        return '“'.$item['name'].'”参数值字符长度不能小于'.substr($vi,10);
                    }
                    else if( in_array( $op=substr($vi,0,strpos($vi,':')) , array_keys(self::$vConditions) )) {
                        $value = substr($vi,strpos($vi,':')+1);
                        if(!self::checkCondition($params[$field],$op,$value)) {
                            return '“'.$item['name'].'”必须'.self::$vConditions[$op].$value;
                        }
                    }
                    else if($vi=='array') { //如果参数是数组则递归检查
                        if(!is_array($params[$field])) {
                            return '“'.$item['name'].'”参数值必须是数组';
                        }
                        else if(!empty($item['_fields'])) {
                            if($item['is_list']===true) { //二维数组(列表)
                                foreach($params[$field] as $subParam) {
                                    return self::validParams($subParam,$item['_fields']);
                                }
                            }
                            else { //关联数组
                                return self::validParams($params[$field],$item['_fields']);
                            }
                        }
                    }
                    else if($vi=='json') { //如果参数是Json数组则递归检查
                        if(!self::isJson($params[$field])) {
                            return '“'.$item['name'].'”参数值必须是JSON字符串';
                        }
                        else if(!empty($item['_fields'])) {
                            $tmpArray = json_decode($params[$field],true);
                            if($item['is_list']===true) { //二维数组(列表)
                                foreach($tmpArray as $subParam) {
                                    return self::validParams($subParam,$item['_fields']);
                                }
                            }
                            else { //关联数组
                                return self::validParams($tmpArray,$item['_fields']);
                            }
                        }
                    }
                }
            }
        }
        if(!empty($item['_regexp'])) {
            foreach($item['_regexp'] as $regex=>$msg) {
                if(!preg_match($regex,$params[$field])) {
                    return $msg;
                }
            }
        }
        return true;
    }

    /**
     * [checkCondition 条件检查]
     * @param  string $var   变量
     * @param  string $op    操作符
     * @param  string $value 值
     * @return bool          [description]
     */
    private static function checkCondition($var,$op,$value) {
        if(in_array($op,array('eq','=='))) {        //等于
            return $var==$value;
        }
        else if(in_array($op,array('neq','!='))) {  //不等于
            return $var!=$value;
        }
        else if(in_array($op,array('heq','==='))) { //恒等于
            return $var===$value;
        }
        else if(in_array($op,array('gt','>'))) {    //大于
            return $var>$value;
        }
        else if(in_array($op,array('elg','>='))) {  //大于等于
            return $var>=$value;
        }
        else if(in_array($op,array('lt','<'))) {    //小于
            return $var<$value;
        }
        else if(in_array($op,array('elt','<='))) {  //小于等于
            return $var<=$value;
        }
        return true;
    }

}