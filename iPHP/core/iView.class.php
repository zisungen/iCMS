<?php
/**
 * iPHP - i PHP Framework
 * Copyright (c) 2012 iiiphp.com. All rights reserved.
 *
 * @author coolmoo <iiiphp@qq.com>
 * @website http://www.iiiphp.com
 * @license http://www.iiiphp.com/license
 * @version 2.0.0
 */
class iView {
    public static $handle  = NULL;
    public static $gateway = null;
    public static $config = array();

    public static function init($config = array()) {
        self::$config = $config;
        self::$handle = new iTemplateLite();
        self::$handle->debugging    = iPHP_TPL_DEBUGGING;
        self::$handle->template_dir = iPHP_TPL_DIR;
        self::$handle->compile_dir  = iPHP_TPL_CACHE;
        self::$handle->reserved_template_varname = iPHP_APP;
        self::$handle->error_reporting_header    = "<?php defined('iPHP') OR exit('What are you doing?');error_reporting(iPHP_TPL_DEBUG?E_ALL & ~E_NOTICE:0);?>\n";
        self::$handle->left_delimiter  = '<!--{';
        self::$handle->right_delimiter = '}-->';
        self::$handle->register_modifier("date", "get_date");
        self::$handle->register_modifier("cut", "csubstr");
        self::$handle->register_modifier("htmlcut", "htmlcut");
        self::$handle->register_modifier("cnlen", "cstrlen");
        self::$handle->register_modifier("html2txt", "html2text");
        self::$handle->register_modifier("key2num", "key2num");
        self::$handle->register_modifier("unicode", "get_unicode");
        self::$handle->register_modifier("random", "random");
        self::$handle->register_modifier("fields", "select_fields");
        self::$handle->register_modifier("pinyin",array("iPinyin", "get"));
        self::$handle->register_modifier("thumb", array("files", "thumb"));
        self::$handle->register_block("cache", array("iView", "block_cache"));
        self::$handle->template_callback = array(
            "resource" => array("iView","callback_resource"),
            "func"     => array("iView","callback_func"),
            "plugin"   => array("iView","callback_plugin"),
        );
        self::$handle->assign('_GET', $_GET);
        self::$handle->assign('_POST', $_POST);
        self::set_template_dir(iPHP_TPL_DIR);
        iPHP_TPL_DEBUG && self::$handle->clear_compiled_tpl();
    }
    public static function set_template_dir($dir) {
        self::$handle->template_dir = $dir;
    }
    public static function check_func($app) {
        $path = iPHP_APP_DIR . '/' . $app . '/' . $app . '.func.php';
        return is_file($path);
    }
    public static function callback_func($args,$tpl) {
        $keys = isset($args['as'])?$args['as']:$args['app'].($args['method']?'_'.$args['method']:'');
        if($args['method']){
            $callback = array($args['app'].'Func',$args['app'].'_'.$args['method']);
            //自定义APP模板调用 iCMS:test:list 调用 contentFunc
            if(self::$config['define']){
                $apps = self::$config['define']['apps'];
                $func = self::$config['define']['func'];
                if(!self::check_func($args['app']) && $apps[$args['app']]){
                    $callback = array($func.'Func',$func.'_'.$args['method']);
                }
            }
            //自定义APP模板调用 iCMS:content:list app="$app.app"
            if($args['_app']){
                $keys     = isset($args['as'])?$args['as']:$args['_app'].'_'.$args['method'];
                $callback = array($args['_app'].'Func',$args['_app'].'_'.$args['method']);
            }
            if(!@is_callable($callback) && strpos($callback[1], '__')===false){
                iPHP::error_throw("Unable to find method '{$callback[0]}::{$callback[1]}'");
            }
        }else{
            $callback = iPHP_APP.'_' . $args['app'];
            function_exists($callback) OR require_once(iPHP_TPL_FUN."/".iPHP_APP.".".$args['app'].".php");
        }
        if(isset($args['vars'])){
            $vars = $args['vars'];
            unset($args['vars'],$vars['loop'],$vars['page']);
            $args = array_merge($args,$vars);
        }

        if(is_array($callback)){
            strpos($callback[1], '__')!==false && $callback = array('iView','callback_func_proxy');
            $tpl->assign($keys,call_user_func_array($callback, array($args)));
        }else{
            $tpl->assign($keys,$callback($args));
        }
    }
    public static function callback_func_proxy($vars=null){
        $func = 'func';
        $vars['func'] && $func = $vars['func'];
        $callback = array($vars['app'].$vars['method'],$func);
        if(@is_callable($callback)){
            call_user_func_array($callback, array($vars));
        }else{
            iPHP::error_throw("Unable to find method '{$callback[0]}::{$callback[1]}'");
        }
    }
    public static function callback_plugin($name,$tpl) {
        $path = iPHP_TPL_FUN."/tpl.".$name;
        if (is_file($path)) {
            return $path;
        }
        return false;
    }
    public static function block_cache($vars, $content, $tpl) {
        $vars['id'] OR iUI::warning('cache 标签出错! 缺少"id"属性或"id"值为空.');
        $cache_time = isset($vars['time']) ? (int) $vars['time'] : -1;
        $cache_name = iPHP_DEVICE . '/block_cache/' . $vars['id'];
        $cache = iCache::get($cache_name);
        if (empty($cache)) {
            if ($content === null) {
                return false;
            }
            $cache = $content;
            iCache::set($cache_name, $content, $cache_time);
        }
        if ($vars['assign']) {
            $tpl->assign($vars['assign'], $cache);
            return true;
        }
        return $cache;
    }
    /**
     * 模板路径
     * @param  [type] $tpl [description]
     * @return [type]      [description]
     */
    public static function callback_resource($tpl,$obj){
        $tpl = ltrim($tpl,'/');
        strpos($tpl,'..') && iPHP::error_404("The template file path has a '..'");

        if(strpos($tpl, 'file::')!==false){
            list($_dir,$tpl)   = explode('||',str_replace('file::','',$tpl));
            $obj->template_dir = $_dir;
            return $tpl;
        }

        strpos($tpl,'./') !==false && $tpl = str_replace('./',dirname($obj->_file).'/',$tpl);

        $flag = iPHP_APP . ':/';
        if (strpos($tpl, $flag) !== false) {
            // 模板名/$tpl
            if ($_tpl = self::check_app_tpl($tpl, iPHP_DEFAULT_TPL)){
                return $_tpl;
            }
            // iCMS/$tpl
            if ($_tpl = self::check_app_tpl($tpl, iPHP_APP)) {
                return $_tpl;
            }
            // iCMS/设备名/$tpl
            if ($_tpl = self::check_app_tpl($tpl, iPHP_APP.'/'.iPHP_DEVICE)) {
                return $_tpl;
            }
            // // 其它移动设备$tpl
            // if(iPHP_MOBILE){
            //     // iCMS/mobile/$tpl
            //     if ($_tpl = self::check_app_tpl($tpl, iPHP_APP.'/mobile')) {
            //         return $_tpl;
            //     }
            // }
            $tpl = str_replace($flag, iPHP_DEFAULT_TPL, $tpl);
        } elseif (strpos($tpl, '{iTPL}') !== false) {
            $tpl = str_replace('{iTPL}', iPHP_DEFAULT_TPL, $tpl);
        }

        if (is_file(iPHP_TPL_DIR . "/" . $tpl)) {
            return $tpl;
        } else {
            iPHP::error_404('Unable to find the template file <b>iPHP:://template/' . $tpl . '</b>', '002', 'TPL');
        }
    }
    public static function check_app_tpl($tpl, $dir) {
        $flag = iPHP_APP.':/';
        $_tpl = str_replace($flag, $dir, $tpl);
        if (is_file(iPHP_TPL_DIR . "/" . $_tpl)) {
            return $_tpl;
        }
        return false;
    }
    public static function app_vars($app_name = true, $out = false) {
        $app_name === true && $app_name = iPHP::$app_name;
        $rs = self::get_vars($app_name);
        return $rs['param'];
    }
    public static function get_vars($key = null) {
        return self::$handle->get_template_vars($key);
    }
    public static function set_iVARS($value = null,$key=null,$append=false) {
        if(is_array($value)){
            if($key){
                self::$handle->_iVARS[$key] = $value;
            }else{
                self::$handle->_iVARS += $value;
            }
        }else{
            if($append){
                self::$handle->_iVARS[$key].= $value;
            }else{
                self::$handle->_iVARS[$key] = $value;
            }
        }
    }

    public static function clear_tpl($file = null) {
        self::$handle OR self::init();
        self::$handle->clear_compiled_tpl($file);
    }
    public static function value($key, $value) {
        self::$handle->assign($key, $value);
    }
    public static function assign($key, $value) {
        self::$handle->assign($key, $value);
    }
    public static function append($key, $value = null, $merge = false) {
        self::$handle->append($key, $value, $merge);
    }
    public static function clear($key) {
        self::$handle->clear_assign($key);
    }
    public static function display($tpl) {
        self::$handle OR self::init();
        self::$handle->display($tpl);
    }
    public static function fetch($tpl) {
        self::$handle OR self::init();
        return self::$handle->fetch($tpl);
    }
    public static function render($tpl, $p = 'index') {
        $tpl OR iPHP::error_404('Please set the template file', '001', 'TPL');
        self::receive_tpl($tpl);
        if (self::$gateway == 'html') {
            return self::$handle->fetch($tpl);
        } else {
            self::$handle->display($tpl);
            iPHP::debug_info($tpl);
        }
    }
    public static function receive_tpl(&$iTPL,$tpl=null){
        $tpl===null && $tpl = iSecurity::escapeStr($_GET['tpl']);
        if($tpl){
            $tpl.= '.htm';
            $tpl = iSecurity::escapeDir(ltrim($tpl,'/'));
            if(iSecurity::_escapePath($tpl)){
                $tplpath = iPHP_TPL_DIR . '/' .iPHP_DEFAULT_TPL.'/'.$tpl;
                if (is_file($tplpath)) {
                    $iTPL = '{iTPL}/'.$tpl;
                }
            }
        }
    }
}
