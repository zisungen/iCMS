<?php
/**
* iCMS - i Content Management System
* Copyright (c) 2007-2012 idreamsoft.com iiimon Inc. All rights reserved.
*
* @author coolmoo <idreamsoft@qq.com>
* @site http://www.idreamsoft.com
* @licence http://www.idreamsoft.com/license.php
* @version 6.2.0
*/
class admincpApp{
    public function __construct() {
        menu::$callback['sidebar'] = array(__CLASS__,'__sidebar');
    }
    public static function __sidebar($menu){
        $history   = menu::history(null,true);
        $caption   = menu::get_caption();
        foreach ($history as $key => $url) {
            $uri   =  str_replace(__ADMINCP__.'=', '', $url);
            $title = $caption[$uri];
            $title && $nav.= '<li><a href="'.$url.'"><i class="fa fa-link"></i> <span>'.$title.'</span></a></li>';
        }
        return $nav;
    }
    public function do_logout(){
   	    members::logout();
    	iUI::success('注销成功!','url:'.iPHP_SELF);
    }
    /**
     * [操作记录]
     * @return [type] [description]
     */
    public function do_access_log(){
        $sql = "WHERE 1=1";
        if($_GET['keywords']) {
            $sql.=" AND CONCAT(username,app,uri,useragent,ip,method,referer) REGEXP '{$_GET['keywords']}'";
        }
        $_GET['cid'] && $sql.=" AND `uid` = '{$_GET['uid']}'";
        $_GET['sapp'] && $sql.=" AND `app` = '{$_GET['sapp']}'";
        $_GET['ip'] && $sql.=" AND `ip` = '{$_GET['ip']}'";


        $orderby    =$_GET['orderby']?$_GET['orderby']:"id DESC";
        $maxperpage = $_GET['perpage']>0?(int)$_GET['perpage']:20;
        $total      = iCMS::page_total_cache("SELECT count(*) FROM `#iCMS@__access_log` {$sql}","G");
        iUI::pagenav($total,$maxperpage,"条记录");
        $rs     = iDB::all("SELECT * FROM `#iCMS@__access_log` {$sql} order by {$orderby} LIMIT ".iUI::$offset." , {$maxperpage}");
        $_count = count($rs);
        include admincp::view("admincp.access");
    }
    public function do_iCMS(){
        //数据统计
        $rs=iDB::all("SHOW FULL TABLES FROM `".iPHP_DB_NAME."` WHERE table_type = 'BASE TABLE';");
        foreach($rs as $k=>$val) {
            if(strstr(iPHP_DB_PREFIX, $val['Tables_in_'.iPHP_DB_NAME])===false) {
                $iTable[]=strtoupper($val['Tables_in_'.iPHP_DB_NAME]);
            }else {
                $oTable[]=$val['Tables_in_'.iPHP_DB_NAME];
            }
        }
        $content_datasize = 0;
        $tables = iDB::all("SHOW TABLE STATUS");
        $_count	= count($tables);
        for ($i=0;$i<$_count;$i++) {
            $tableName	= strtoupper($tables[$i]['Name']);
            if(in_array($tableName,$iTable)) {
                $datasize += $tables[$i]['Data_length'];
                $indexsize += $tables[$i]['Index_length'];
                if (stristr(strtoupper(iPHP_DB_PREFIX."article,".iPHP_DB_PREFIX."category,".iPHP_DB_PREFIX."comment,".iPHP_DB_PREFIX."article_data"),$tableName)) {
                    $content_datasize += $tables[$i]['Data_length']+$tables[$i]['Index_length'];
                }
            }
        }
        $acc = iDB::value("SELECT count(*) FROM `#iCMS@__category` WHERE `appid`='".iCMS_APP_ARTICLE."'");
        $tac = iDB::value("SELECT count(*) FROM `#iCMS@__category` WHERE `appid`='".iCMS_APP_TAG."'");
        $pac = iDB::value("SELECT count(*) FROM `#iCMS@__category` WHERE `appid`='".iCMS_APP_PUSH."'");

        $ac  = iDB::value("SELECT count(*) FROM `#iCMS@__article`");
        $ac0 = iDB::value("SELECT count(*) FROM `#iCMS@__article` WHERE `status`='0'");
        $ac2 = iDB::value("SELECT count(*) FROM `#iCMS@__article` WHERE `status`='2'");

        $ctc = iDB::value("SELECT count(*) FROM `#iCMS@__comment`");
        $tc  = iDB::value("SELECT count(*) FROM `#iCMS@__tags`");
        $kc  = iDB::value("SELECT count(*) FROM `#iCMS@__keywords`");
        $pc  = iDB::value("SELECT count(*) FROM `#iCMS@__push`");
        $uc  = iDB::value("SELECT count(*) FROM `#iCMS@__user`");
        $fdc = iDB::value("SELECT count(*) FROM `#iCMS@__files`");
        $lc  = iDB::value("SELECT count(*) FROM `#iCMS@__links`");

    	include admincp::view("admincp.index");
    }
    // 检测函数支持
    public function isfun($fun = ''){
        if (!$fun || trim($fun) == '' || preg_match('~[^a-z0-9\_]+~i', $fun, $tmp)) return '错误';
        return $this->check((false !== function_exists($fun)));
    }
    //检测PHP设置参数
    public function show($varName){
        switch($result = get_cfg_var($varName)){
            case 0:
                return $this->check(0);
            break;
            case 1:
                return $this->check(1);
            break;
            default:
                return $result;
            break;
        }
    }
    public function check($o) {
        return $o?'<font color="green"><i class="fa fa-check"></i></font>':'<font color="red"><i class="fa fa-times"></i></font>';
    }
}
