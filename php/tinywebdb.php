<?
define('MANAGEversion','201704292');
require_once('class/db.php');
$countSettingDefault='special_count';
$mgetSettingDefault='special_mget';
$listgetSettingDefault='special_listget';
$searchSettingDefault='special_search';

$kv=new SaeKV();
if(isset($_REQUEST['do'])){
    if(setting::get('allow_browser')!='on'){
        if(isset($_SERVER['HTTP_ORIGIN'])||isset($_SERVER['HTTP_REFERER'])||isset($_SERVER['HTTP_USER_AGENT'])||isset($_SERVER['HTTP_COOKIE'])||$_SERVER['HTTP_ACCEPT']!='application/json'){
            http_response_code(401);$_REQUEST['tag']='';
        }
    }
    $_REQUEST['tag'].='';$_REQUEST['value'].='';
    if($_REQUEST['do']=='storeavalue'){
        db::set($_REQUEST['tag'],$_REQUEST['value']);
        header('Content-Type: application/json');
        exit(json_encode(['STORED',$_REQUEST['tag'],$_REQUEST['value']]));
    }
    elseif($_REQUEST['do']=='getvalue'){
        header('Content-Type: application/json');
        $tmparray=explode('#',$_REQUEST['tag'],2);
        $queryFunction=$tmparray[0];
        if(count($tmparray)>1){$queryParam=$tmparray[1];}
        $countSetting=setting::get('special_tags_count');if(empty($countSetting)){$countSetting=$countSettingDefault;}
        $mgetSetting=setting::get('special_tags_mget');if(empty($mgetSetting)){$mgetSetting=$mgetSettingDefault;}
        $listgetSetting=setting::get('special_tags_listget');if(empty($listgetSetting)){$listgetSetting=$listgetSettingDefault;}
        $searchSetting=setting::get('special_tags_search');if(empty($searchSetting)){$searchSetting=$searchSettingDefault;}
        if($queryFunction==$countSetting){exit(json_encode(['VALUE',$_REQUEST['tag'],db::count($queryParam)]));}
        elseif($queryFunction==$mgetSetting){exit(json_encode(['VALUE',$_REQUEST['tag'],db::getall($queryParam,true)]));}
        elseif($queryFunction==$listgetSetting){$ReturnTags=[];$paramArray=explode('#',$queryParam);$paramArray=array_unique($paramArray);exit(json_encode(['VALUE',$_REQUEST['tag'],db::mget($paramArray,true)]));}
        elseif($queryFunction==$searchSetting){$ReturnTags=[];$paramArray=explode('#',$queryParam,2);if(count($paramArray)>1){$paramPrefix=''.$paramArray[1];}else{$paramPrefix='';}$paramKeyWord=''.$paramArray[0];exit(json_encode(['VALUE',$_REQUEST['tag'],db::search($paramKeyWord,false,true,true,$paramPrefix,true)]));}
        else{exit(json_encode(['VALUE',$_REQUEST['tag'],''.db::get($_REQUEST['tag'])]));}
    }
}else{
    if(setting::get('allow_front')=='on'){
?><html>
    <head>
        <title>TinyWebDB Manage System</title>
        <link rel="stylesheet" href="/script/front.css">
        <script src="//cdn.bootcss.com/jquery/3.2.1/jquery.js"></script>
        <script src="/script/front.js"></script>
    </head>
    <body>
        <div id="content">
            <div id="content_title">TinyWebDB可视化操作页面</div>
            <div id="content_subtitle">您的网络微数据库地址是：http://<? echo $_SERVER["HTTP_APPNAME"]; ?>.applinzi.com/tinywebdb</div>
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
            <div id="signature">By ColinTree, Version <? echo MANAGEversion;?></div>
        </div>
    </body>
</html>
<?}else{?>可视化操作页面已被禁用，如需启用，请前往<a href="/tinywebdb/manage">服务器后台</a><br>您的网络微数据库地址是：http://<? echo $_SERVER["HTTP_APPNAME"]; ?>.applinzi.com/tinywebdb<?}}?>