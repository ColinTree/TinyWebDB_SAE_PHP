<?
if(!defined('MANAGEversion')){header("HTTP/1.0 404 Not Found");exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested URL /php/setting.php was not found on this server.</p>\n</body></html>\n");}

$countSettingDefault='special_count';
$mgetSettingDefault='special_mget';
$listgetSettingDefault='special_listget';
$searchSettingDefault='special_search';
if(isset($_POST['setting_type'])){
    // security
    if($_POST['setting_type']=='security'){
        $kv->set('tinywebdbMANAGE_allow_front',$_REQUEST['allow_front']);
        $kv->set('tinywebdbMANAGE_allow_browser',$_REQUEST['allow_browser']);
    }
    // backup
    if($_POST['setting_type']=='backup'){
        $kv->set('tinywebdbMANAGE_backup_excel_store_to_storage',$_REQUEST['excel_store_to_storage']);
        $kv->set('tinywebdbMANAGE_backup_excel_auto_width',$_REQUEST['excel_auto_width']);
    }
    // special_tags
    if($_POST['setting_type']=='special_tags'){
        $kv->set('tinywebdbMANAGE_special_tags_count',$_REQUEST['count']);
        $kv->set('tinywebdbMANAGE_special_tags_mget',$_REQUEST['mget']);
        $kv->set('tinywebdbMANAGE_special_tags_listget',$_REQUEST['listget']);
        $kv->set('tinywebdbMANAGE_special_tags_search',$_REQUEST['search']);
    }
    // change password
    if($_POST['setting_type']=='change_password'){
        if(!(empty($_REQUEST['old_pwd']) || empty($_REQUEST['new_pwd']))){
            if(PWD!=md5($_REQUEST['old_pwd'])){
                $setting_notice_change_pwd_old='密码错误';
            }else{
                if(strlen($_REQUEST['new_pwd'])>=6){
                    $kv->set('tinywebdbMANAGE_password',$_REQUEST['new_pwd']);
                    exit('<div class="alert alert-success">修改已完成，请重新登录继续使用（三秒钟之后将自动跳转至登录页面）</div><script>setTimeout(function(){window.location.href="?a=index";},3000);</script>');
                }else{
                    $setting_notice_change_pwd_new='密码过短(长度小于6)将不能保证您的数据库安全！';
                }
            }
        }else{
            if(empty($_REQUEST['old_pwd']))
                $setting_notice_change_pwd_old='本项必填！';
            if(empty($_REQUEST['new_pwd']))
                $setting_notice_change_pwd_new='本项必填！';
        }
    }
}
?><div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title"><b>设置</b> <a href="javascript:expand();" style="color:#aaa">展开/折叠所有</a></h3></div>
    <div class="panel-body">
        <ol style="list-style-type:none;padding-left:14px;">
            <li onclick="changeVisibility('security_li')">
                <p id="security" style="cursor:pointer;margin-left:-14px;font-weight:bold"><? echo($_POST['setting_type']=='security'?'    ':'&gt;');?>&nbsp;数据安全</p>
            </li>
            <li<? echo($_POST['setting_type']=='security'?'':' style="display:none"');?> id="security_li">
                <form action="?a=setting#security" method="post">
                    <input type="hidden" name="setting_type" value="security"/>
                    <div class="checkbox">
                        <label><input type="checkbox" name="allow_front"<? if($kv->get("tinywebdbMANAGE_allow_front")=="on")echo"checked";?>/>允许可视化操作页面</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="allow_browser"<? if($kv->get("tinywebdbMANAGE_allow_browser")=="on")echo"checked";?>/>允许来自浏览器对数据库的访问</label>
                    </div>
                    <span>说明：本功能对于数据防修改能力较弱，仅做简单的浏览器级别防御，请不要过度依赖</span>
                    <p><input type="submit" value="保存" class="btn btn-default"/><? if($_POST["setting_type"]=="security")echo'已保存'; ?></p>
                </form>
            </li>
            <hr style="margin-left:-14px"/>
            <li onclick="changeVisibility('special_tags_li')">
                <p id="special_tags" style="cursor:pointer;margin-left:-14px;font-weight:bold"><? echo($_POST['setting_type']=='special_tags'?'    ':'&gt;');?>&nbsp;特殊标签功能</p>
            </li>
            <li<? echo($_POST['setting_type']=='special_tags'?'':' style="display:none"');?> id="special_tags_li">
                <form action="?a=setting#special_tags" method="post">
                    <input type="hidden" name="setting_type" value="special_tags"/>
                    <div>
                        <span class="control-label">返回总标签数量(不要包含#)：</span>
                        <input type="text" class="form-control" name="count" autocomplete="off" value="<? echo htmlspecialchars($kv->get('tinywebdbMANAGE_special_tags_count')); ?>" placeholder="<? echo $countSettingDefault; ?>" oninput="changeSetting(this.value,'<? echo $countSettingDefault; ?>','special_tags_count')"/>
                        <div style="margin-left:5px;">
                            使用方法：请求获取标签“<span id="special_tags_count"><? $countSetting=$kv->get('tinywebdbMANAGE_special_tags_count');echo(empty($countSetting)?$countSettingDefault:htmlspecialchars($countSetting)); ?></span>[#计数前缀]”
                            <br>返回：一个数字<br>
                            <small>带有[]的部分表示这是选填的。比如计数，special_count是统计所有标签的数量，special_count#a则是统计所有是a开头的标签的数量</small>
                        </div>
                    </div>
                    <p style="margin:0;margin-top:20px;"></p>
                    <div>
                        <span class="control-label">批量获取数据库的标签和值(此标签不要包含#)：</span>
                        <input type="text" class="form-control" name="mget" autocomplete="off" value="<? echo htmlspecialchars($kv->get('tinywebdbMANAGE_special_tags_mget')); ?>" placeholder="<? echo $mgetSettingDefault; ?>" oninput="changeSetting(this.value,'<? echo $mgetSettingDefault; ?>','special_tags_mget')"/>
                        <div style="margin-left:5px;">
                            使用方法：请求获取标签“<span id="special_tags_mget"><? $mgetSetting=$kv->get('tinywebdbMANAGE_special_tags_mget');echo(empty($mgetSetting)?$mgetSettingDefault:htmlspecialchars($mgetSetting)); ?></span>[#前缀]”
                            <br>返回：一个键值对列表，格式如((标签 值)(标签 值)....)
                        </div>
                    </div>
                    <p style="margin:0;margin-top:20px;"></p>
                    <div>
                        <span class="control-label">批量获取数据库的标签和值（一次性获取多个标签的值）(此标签不要包含#)：</span>
                        <input type="text" class="form-control" name="listget" autocomplete="off" value="<? echo htmlspecialchars($kv->get('tinywebdbMANAGE_special_tags_listget')); ?>" placeholder="<? echo $listgetSettingDefault; ?>" oninput="changeSetting(this.value,'<? echo $listgetSettingDefault; ?>','special_tags_listget')"/>
                        <div style="margin-left:5px;">
                            使用方法：请求获取标签“<span id="special_tags_listget"><? $mgetSetting=$kv->get('tinywebdbMANAGE_special_tags_listget');echo(empty($listgetSetting)?$listgetSettingDefault:htmlspecialchars($listgetSetting)); ?></span>[#标签1][#标签2][……]”
                            <br>返回：一个键值对列表，格式如((标签 值)(标签 值)....)<br>
                            <small>本功能理论上可以获取无限多的标签，但是需要对效率进行权衡</small>
                        </div>
                    </div>
                    <p style="margin:0;margin-top:20px;"></p>
                    <div>
                        <span class="control-label">搜索数据库的标签和值(此标签不要包含#)：</span>
                        <input type="text" class="form-control" name="search" autocomplete="off" value="<? echo htmlspecialchars($kv->get('tinywebdbMANAGE_special_tags_search')); ?>" placeholder="<? echo $searchSettingDefault; ?>" oninput="changeSearch(this.value,'<? echo $searchSettingDefault; ?>','special_tags_search')"/>
                        <div style="margin-left:5px;">
                            使用方法：请求获取标签“<span id="special_tags_search"><? $searchSetting=$kv->get('tinywebdbMANAGE_special_tags_search');echo(empty($searchSetting)?$searchSettingDefault:htmlspecialchars($searchSetting)); ?></span>[#搜索关键词][#搜索范围(前缀)]”
                            <br>返回：一个键值对列表，格式如((标签 值)(标签 值)....)                            
                        </div>
                    </div>
                    <p><input type="submit" value="保存" class="btn btn-default"/><? if($_POST['setting_type']=='special_tags')echo'已保存'; ?></p>
                </form>
            </li>
            <hr style="margin-left:-14px"/>
            <li onclick="changeVisibility('backup_li')">
                <p id="backup" style="cursor:pointer;margin-left:-14px;font-weight:bold"><? echo($_POST['setting_type']=='backup'?'    ':'&gt;');?>&nbsp;备份相关</p>
            </li>
            <li<? echo($_POST['setting_type']=='backup'?'':' style="display:none"');?> id="backup_li">
                <form action="?a=setting#backup" method="post">
                    <input type="hidden" name="setting_type" value="backup"/>
                    <div class="checkbox">
                        <label><input type="checkbox" name="excel_store_to_storage"<? if($kv->get('tinywebdbMANAGE_backup_excel_store_to_storage')=='on')echo'checked';?>/>导出的excel表自动存档备份至Storage</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="excel_auto_width"<? if($kv->get('tinywebdbMANAGE_backup_excel_auto_width')=='on')echo'checked';?>/>导出Excel表自动调整列宽</label>
                    </div>
                    <p><input type="submit" value="保存" class="btn btn-default"/><? if($_POST['setting_type']=='backup')echo'已保存'; ?></p>
                </form>
            </li>
            <hr style="margin-left:-14px"/>
            <li onclick="changeVisibility('change_password_li')">
                <p id="change_password" style="cursor:pointer;margin-left:-14px;font-weight:bold"><? echo($_POST['setting_type']=='change_password'?'    ':'&gt;');?>&nbsp;修改密码</p>
            </li>
            <li<? echo($_POST['setting_type']=='change_password'?'':' style="display:none"');?> id="change_password_li">
                <form action="?a=setting#change_password" method="post">
                    <input type="hidden" name="setting_type" value="change_password"/>
                    <div class="<? echo(isset($setting_notice_change_pwd_old)?' has-error':'');?>">
                        <span class="control-label">原密码：</span>
                        <input type="password" class="form-control" name="old_pwd" oninput="changePasswordOnInput(this)" autocomplete="off"/>
                        <span class="help-block"><? echo(isset($setting_notice_change_pwd_old)?$setting_notice_change_pwd_old:'');?></span>
                    </div>
                    <p></p>
                    <div class="<? echo(isset($setting_notice_change_pwd_new)?' has-error':'');?>">
                        <span class="control-label">新密码：</span>
                        <input type="password" class="form-control" name="new_pwd" oninput="changePasswordOnInput(this)" autocomplete="off"/>
                        <span class="help-block"><? echo(isset($setting_notice_change_pwd_new)?$setting_notice_change_pwd_new:'');?></span>
                    </div>
                    <p><input type="submit" value="保存" class="btn btn-default" onclick="return checkChangePassword(this)"/><? if($_POST['setting_type']=='change_password' && !isset($setting_notice_change_pwd_old) && !isset($setting_notice_change_pwd_new))echo'已保存'; ?></p>
                </form>
            </li>
            <hr style="margin-left:-14px"/>
        </ol>
        <span style="color:#aaa">更多设置开发中…… / More setting still developing...</span>
    </div>
</div>