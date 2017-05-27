<?
define('MANAGEversion','201705251');
require_once('class/db.php');

$getSettingDefault='special_getSetting';
$countSettingDefault='special_count';
$mgetSettingDefault='special_mget';
$listgetSettingDefault='special_listget';
$searchSettingDefault='special_search';

if(isset($_REQUEST['do'])){
    $do=$_REQUEST['do'];
    if($do=='file' || $do=='addfile' || $do=='savefile' || $do=='updatefile'){  //file as addfile
        $fd=file_get_contents('php://input');
        $fd=$fd ? $fd : $GLOBALS['HTTP_RAW_POST_DATA'];
        $fn=empty($_GET['filename']) ? md5($_SERVER['REQUEST_TIME'].'') : $_GET['filename'];
        $auth=kvfile::auth($fn);
        if($auth===false || ($auth!='wr' && $auth!='rw' && $auth!='w')){
            http_response_code(401);
            exit('无权限上传/写入'.$fn);
        }
        $tmpfn=kvfile::formatFN($fn);
        while(strlen($tmpfn)>1){
            while(substr($tmpfn,-1)!='/'){ $tmpfn=substr($tmpfn,0,strlen($tmpfn)-1); }
            $auth=kvfile::auth($tmpfn);
            if($auth!='wr'&&$auth!='rw'&&$auth!='w'){
                http_response_code(401);
                exit('无权限上传/写入'.htmlspecialchars($tmpfn).'下的文件');
            }else{
                $tmpfn=substr($tmpfn,0,strlen($tmpfn)-1);
            }
        }
        if(!empty($fd)){
            if($do=='file'||$do=='addfile'){
                $rst=kvfile::create($fn,$fd);
            }elseif($do=='savefile'){
                $rst=kvfile::save($fn,$fd);
            }elseif($do=='updatefile'){
                $rst=kvfile::update($fn,$fd);
            }
            exit($rst!==false ? ('http://'.$_SERVER["HTTP_APPNAME"].'.applinzi.com/file'.$rst) : '失败/FAIL');
        }
        exit;
    }
    if($do=='getfile'){
        $filepath=$_GET['filename'];
        if(empty($filepath)){
            http_response_code(404);
            exit('文件名为空');
        }else{
            if(substr($filepath,-1)=='/'){
                http_response_code(401);
                exit('无法访问目录');
            }else{
                $auth=kvfile::auth($filepath);
                session_start();
                if((isset($_SESSION['tinywebdbmanage'])?$_SESSION['tinywebdbmanage']:'')==md5(setting::get('password'))){
                    $manage_logined=true;
                }
                if($auth===false || ($auth!='wr' && $auth!='rw' && $auth!='r') && $manage_logined!==true){
                    http_response_code(401);
                    exit('无权限访问'.htmlspecialchars($filepath));
                }
                $tmpfn=kvfile::formatFN($filepath);
                while(strlen($tmpfn)>1){
                    while(substr($tmpfn,-1)!='/'){ $tmpfn=substr($tmpfn,0,strlen($tmpfn)-1); }
                    $auth=kvfile::auth($tmpfn);
                    if($auth!='wr'&&$auth!='rw'&&$auth!='r' && $manage_logined!==true){
                        http_response_code(401);
                        exit('无权限访问'.htmlspecialchars($tmpfn).'下的文件');
                    }else{
                        $tmpfn=substr($tmpfn,0,strlen($tmpfn)-1);
                    }
                }
                $rst=kvfile::read($filepath);
                if($rst!==false){
                    $fn=kvfile::getfn($filepath);
                    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                    header('Cache-Control: no-store, no-cache, must-revalidate');
                    header('Cache-Control: post-check=0, pre-check=0', false );
                    header('Pragma: no-cache');
                    header('Content-Type: '.kvfile::getmime($rst));
                    header("Content-Length: ". kvfile::getsize($filepath));
                    if(strtolower($_SERVER['REQUEST_METHOD'])!='head'){
                        if(!isset($_GET['nodownload'])){
                            header('Content-Disposition: attachment; filename*="utf8\'\''.urlencode($fn).'"');
                        }
                        exit($rst);
                    }
                }else{
                    http_response_code(404);
                    exit('文件不存在或无权限');
                }
            }
        }
    }
    if(setting::get('allow_browser')!='on'){
        if(isset($_SERVER['HTTP_ORIGIN'])||isset($_SERVER['HTTP_REFERER'])||isset($_SERVER['HTTP_USER_AGENT'])||isset($_SERVER['HTTP_COOKIE'])||$_SERVER['HTTP_ACCEPT']!='application/json'){
            http_response_code(401);$_REQUEST['tag']='';
        }
    }
    $_REQUEST['tag'].='';$_REQUEST['value'].='';
    if($do=='storeavalue'){
        db::set($_REQUEST['tag'],$_REQUEST['value']);
        header('Content-Type: application/json');
        exit(json_encode(['STORED',$_REQUEST['tag'],$_REQUEST['value']]));
    }
    elseif($do=='getvalue'){
        header('Content-Type: application/json');
        $tmparray=explode('#',$_REQUEST['tag'],2);
        $queryFunction=$tmparray[0];
        if(count($tmparray)>1){$queryParam=$tmparray[1];}
        //get settings
        $getSetting=$getSettingDefault;
        $countSetting=setting::get('special_tags_count');
        if(empty($countSetting)){
            $countSetting=$countSettingDefault;
        }
        $mgetSetting=setting::get('special_tags_mget');
        if(empty($mgetSetting)){
            $mgetSetting=$mgetSettingDefault;
        }
        $listgetSetting=setting::get('special_tags_listget');
        if(empty($listgetSetting)){
            $listgetSetting=$listgetSettingDefault;
        }
        $searchSetting=setting::get('special_tags_search');
        if(empty($searchSetting)){
            $searchSetting=$searchSettingDefault;
        }
        //do return(s)
        switch($queryFunction){
            case $getSetting:
                exit(json_encode(['VALUE',$_REQUEST['tag'],[['count',$countSetting],['mget',$mgetSetting],['listget',$listgetSetting],['search',$searchSetting]]]));
                break;
            case $countSetting:
                exit(json_encode(['VALUE',$_REQUEST['tag'],db::count($queryParam)]));
                break;
            case $mgetSetting:
                exit(json_encode(['VALUE',$_REQUEST['tag'],db::getall($queryParam,true)]));
                break;
            case $listgetSetting:
                $ReturnTags=[];
                $paramArray=explode('#',$queryParam);
                $paramArray=array_unique($paramArray);
                exit(json_encode(['VALUE',$_REQUEST['tag'],db::mget($paramArray,true)]));
                break;
            case $searchSetting:
                $ReturnTags=[];
                $paramArray=explode('#',$queryParam,2);
                if(count($paramArray)>1){
                    $paramPrefix=''.$paramArray[1];
                }else{
                    $paramPrefix='';
                }
                $paramKeyWord=''.$paramArray[0];
                exit(json_encode(['VALUE',$_REQUEST['tag'],db::search($paramKeyWord,false,true,true,$paramPrefix,true)]));
                break;
            default:
                exit(json_encode(['VALUE',$_REQUEST['tag'],''.db::get($_REQUEST['tag'])]));
        }
    }
}else{?><html>
    <head><title>TinyWebDB Manage System</title><link rel="stylesheet" href="/script/front.css"><script src="//cdn.bootcss.com/jquery/3.2.1/jquery.js"></script><script src="/script/front.js"></script></head>
    <body onload="setTimeout(CheckUpdate('aix.colintree.cn',<? echo MANAGEversion;?>),2000);setTimeout(CheckUpdate('www.source-space.cn',<? echo MANAGEversion;?>),2000);">
        <div id="content">
            <div id="content_title">TinyWebDB可视化操作页面</div>
            <div id="content_subtitle">您的网络微数据库地址是：http://<? echo $_SERVER["HTTP_APPNAME"]; ?>.applinzi.com/tinywebdb</div>
<?
    if(setting::get('allow_front')=='on'){
?>
            <form action="/tinywebdb/getvalue" method="post">
                <p><b>标签</b>/Tag <label><input type="checkbox" id="getvalue_save"/>保存输入记录</label><input id="getvalue_tag" type="text" name="tag" autocomplete="off"/></p>
                <p><b>值</b>/Value<span id="getvalue_value">&nbsp;</span></p>
                <input type="submit" value="查询">
            </form>
            <hr>
            <form action="/tinywebdb/storeavalue" method="post">
                <p><b>标签</b>/Tag <label><input type="checkbox" id="storeavalue_save"/>保存输入记录</label><input id="storeavalue_tag" type="text" name="tag" autocomplete="off"/></p>
                <p><b>值</b>/Value<input id="storeavalue_value" type="text" name="value" autocomplete="off"/></p>
                <p><b>执行结果</b><span id="storeavalue_status">&nbsp;</span></p>
                <input type="submit" value="储存">
            </form>
            <hr>
            <a id="manage_link" href="/tinywebdb/manage">服务器后台</a>
<?
    }else{
?>
            <div style="color:gray">
                <p><b>标签</b>/Tag <label><input type="checkbox" disabled/>保存输入记录</label><input type="text" name="tag" disabled/></p>
                <p><b>值</b>/Value<span id="getvalue_value">&nbsp;</span></p>
                <input type="button" value="查询" disabled>
                <hr>
                <p><b>标签</b>/Tag <label><input type="checkbox" disabled/>保存输入记录</label><input type="text" name="tag" disabled/></p>
                <p><b>值</b>/Value<input type="text" name="value" disabled/></p>
                <p><b>执行结果</b><span id="storeavalue_status">&nbsp;</span></p>
                <input type="button" value="储存" disabled>
            </div>
            <hr>
            <div id="disabled_label">
                <small>可视化操作页面已被禁用，如需启用，请前往<a id="manage_link" href="/tinywebdb/manage">服务器后台</a></small>
            </div>
<?
    }
?>
            <div id="signature">By ColinTree, Version <? echo MANAGEversion;?><a id="update_available" href="http://tsp.colintree.cn/下载页" target="_blank" style="display:none;color:red;">有更新</a></div>
        </div>
    </body>
</html><?}?>