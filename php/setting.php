<?
if(!defined('MANAGEversion')){http_response_code(404);exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested URL /php/setting.php was not found on this server.</p>\n</body></html>\n");}

$countSettingDefault='special_count';
$mgetSettingDefault='special_mget';
$listgetSettingDefault='special_listget';
$searchSettingDefault='special_search';
if(isset($_POST['setting_type'])){
    // all (category on page manage?a=all)
    if($_POST['setting_type']=='all'){
        setting::set('all_categorylist',$_REQUEST['categorylist']);
    }
    // security
    if($_POST['setting_type']=='security'){
        setting::set('allow_front',$_REQUEST['allow_front']);
        setting::set('allow_browser',$_REQUEST['allow_browser']);
    }
    // backup
    if($_POST['setting_type']=='backup'){
        // TODO: add one that is the download file name.
        setting::set('backup_excel_store_to_storage',$_REQUEST['excel_store_to_storage']);
        setting::set('backup_excel_auto_width',$_REQUEST['excel_auto_width']);
    }
    // special_tags
    if($_POST['setting_type']=='special_tags'){
        setting::set('special_tags_count',$_REQUEST['count']);
        setting::set('special_tags_mget',$_REQUEST['mget']);
        setting::set('special_tags_listget',$_REQUEST['listget']);
        setting::set('special_tags_search',$_REQUEST['search']);
    }
    // change password
    if($_POST['setting_type']=='change_password'){
        if(!(empty($_REQUEST['old_pwd']) || empty($_REQUEST['new_pwd']))){
            if(PWD!=md5($_REQUEST['old_pwd'])){
                $setting_notice_change_pwd_old='密码错误';
            }else{
                if(strlen($_REQUEST['new_pwd'])>=6){
                    setting::set('password',$_REQUEST['new_pwd']);
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
    // clear_tinywebdb
    if($_POST['setting_type']=='clear_tinywebdb'){
        if($_POST['clear_type']=='clear_data'){
            echo'清除中...<br>',"\n";
            db::clear();
            echo'清除完成，正在跳转...<script>location.href="?a=all"</script>';
        }
        if($_POST['clear_type']=='clear_settings'){
            echo'清除中...<br>',"\n";
            setting::clear();
            echo'清除完成，正在跳转...<script>location.href="?a=init"</script>';
        }
        if($_POST['clear_type']=='clear_all'){
            echo'清除中...<br>',"\n";
            db::clear();
            setting::clear();
            echo'清除完成，正在跳转...<script>location.href="?a=init"</script>';
        }
    }
}
?><div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title"><b>设置</b> <a id="setting_expand">展开/折叠所有</a></h3></div>
    <div class="panel-body">
        <ol id="setting_ol">
            <li><p id="all"><? echo($_POST['setting_type']=='all'?'    ':'&gt;');?>&nbsp;标签浏览页设定</p></li>
            <li<? echo($_POST['setting_type']=='all'?'':' style="display:none"');?> id="all_li">
                <form action="?a=setting#all" method="post" id="setting_all_form">
                    <input type="hidden" name="setting_type" value="all"/>
                    <div>
                        <span class="control-label">标签浏览页-分类列表(使用井号#分隔)：</span>
                        <input type="text" class="form-control" name="categorylist" autocomplete="off" value="<? echo htmlspecialchars(setting::get('all_categorylist')); ?>"/>
                        <div style="margin-left:5px;">
                            如果需要默认显示所有标签，则在本项最前面加上一个井号#（显示全部，即前缀为空）<br>
                            例如：默认显示全部，可选显示“student_”或者“teacher_”开头的标签，则应当这么填：<b>#student_#teacher_</b><br>
                            例如：默认显示“student_”，可选显示全部和开头为“teacher_”的标签，则应当这么填：<b>student_##teacher_</b>
                        </div>
                    </div>
                    <p><input type="submit" value="保存" class="btn btn-default"/><? if($_POST['setting_type']=='all')echo'已保存'; ?></p>
                </form>
            </li>
            <hr>
            <li><p id="security"><? echo($_POST['setting_type']=='security'?'    ':'&gt;');?>&nbsp;数据安全</p></li>
            <li<? echo($_POST['setting_type']=='security'?'':' style="display:none"');?> id="security_li">
                <form action="?a=setting#security" method="post">
                    <input type="hidden" name="setting_type" value="security"/>
                    <div class="checkbox"><label><input type="checkbox" name="allow_front"<? if(setting::get('allow_front')=="on")echo"checked";?>/>允许可视化操作页面</label></div>
                    <div class="checkbox"><label><input type="checkbox" name="allow_browser"<? if(setting::get('allow_browser')=="on")echo"checked";?>/>允许来自浏览器对数据库的访问</label></div>
                    <span>说明：本功能对于数据防修改能力较弱，仅做简单的浏览器级别防御，请不要过度依赖</span>
                    <p><input type="submit" value="保存" class="btn btn-default"/><? if($_POST["setting_type"]=="security")echo'已保存'; ?></p>
                </form>
            </li>
            <hr>
            <li><p id="special_tags"><? echo($_POST['setting_type']=='special_tags'?'    ':'&gt;');?>&nbsp;特殊标签功能</p></li>
            <li<? echo($_POST['setting_type']=='special_tags'?'':' style="display:none"');?> id="special_tags_li">
                <form action="?a=setting#special_tags" method="post">
                    <input type="hidden" name="setting_type" value="special_tags"/>
                    <div>
                        <span class="control-label">返回总标签数量(不要包含#)：</span>
                        <input type="text" class="form-control" name="count" autocomplete="off" value="<? echo htmlspecialchars(setting::get('special_tags_count')); ?>" placeholder="<? echo $countSettingDefault; ?>" oninput="changeSetting(this.value,'<? echo $countSettingDefault; ?>','special_tags_count')"/>
                        <div style="margin-left:5px;">
                            使用方法：请求获取标签“<span id="special_tags_count"><? $countSetting=setting::get('special_tags_count');echo(empty($countSetting)?$countSettingDefault:htmlspecialchars($countSetting)); ?></span>[#计数前缀]”
                            <br>返回：一个数字<br>
                            <small>带有[]的部分表示这是选填的。比如计数，special_count是统计所有标签的数量，special_count#a则是统计所有是a开头的标签的数量</small>
                        </div>
                    </div>
                    <p style="margin:20px 0 0 0"></p>
                    <div>
                        <span class="control-label">批量获取数据库的标签和值(此标签不要包含#)：</span>
                        <input type="text" class="form-control" name="mget" autocomplete="off" value="<? echo htmlspecialchars(setting::get('special_tags_mget')); ?>" placeholder="<? echo $mgetSettingDefault; ?>" oninput="changeSetting(this.value,'<? echo $mgetSettingDefault; ?>','special_tags_mget')"/>
                        <div style="margin-left:5px;">
                            使用方法：请求获取标签“<span id="special_tags_mget"><? $mgetSetting=setting::get('special_tags_mget');echo(empty($mgetSetting)?$mgetSettingDefault:htmlspecialchars($mgetSetting)); ?></span>[#前缀]”
                            <br>返回：一个键值对列表，格式如((标签 值)(标签 值)....)
                        </div>
                    </div>
                    <p style="margin:20px 0 0 0"></p>
                    <div>
                        <span class="control-label">批量获取数据库的标签和值（一次性获取多个标签的值）(此标签不要包含#)：</span>
                        <input type="text" class="form-control" name="listget" autocomplete="off" value="<? echo htmlspecialchars(setting::get('special_tags_listget')); ?>" placeholder="<? echo $listgetSettingDefault; ?>" oninput="changeSetting(this.value,'<? echo $listgetSettingDefault; ?>','special_tags_listget')"/>
                        <div style="margin-left:5px;">
                            使用方法：请求获取标签“<span id="special_tags_listget"><? $mgetSetting=setting::get('special_tags_listget');echo(empty($listgetSetting)?$listgetSettingDefault:htmlspecialchars($listgetSetting)); ?></span>[#标签1][#标签2][……]”
                            <br>返回：一个键值对列表，格式如((标签 值)(标签 值)....)<br>
                            <small>本功能理论上可以获取无限多的标签，但是需要对效率进行权衡</small>
                        </div>
                    </div>
                    <p style="margin:20px 0 0 0"></p>
                    <div>
                        <span class="control-label">搜索数据库的标签和值(此标签不要包含#)：</span>
                        <input type="text" class="form-control" name="search" autocomplete="off" value="<? echo htmlspecialchars(setting::get('special_tags_search')); ?>" placeholder="<? echo $searchSettingDefault; ?>" oninput="changeSearch(this.value,'<? echo $searchSettingDefault; ?>','special_tags_search')"/>
                        <div style="margin-left:5px;">
                            使用方法：请求获取标签“<span id="special_tags_search"><? $searchSetting=setting::get('special_tags_search');echo(empty($searchSetting)?$searchSettingDefault:htmlspecialchars($searchSetting)); ?></span>[#搜索关键词][#搜索范围(前缀)]”
                            <br>返回：一个键值对列表，格式如((标签 值)(标签 值)....)
                        </div>
                    </div>
                    <p><input type="submit" value="保存" class="btn btn-default"/><? if($_POST['setting_type']=='special_tags')echo'已保存'; ?></p>
                </form>
            </li>
            <hr>
            <li><p id="backup"><? echo($_POST['setting_type']=='backup'?'    ':'&gt;');?>&nbsp;备份相关</p></li>
            <li<? echo($_POST['setting_type']=='backup'?'':' style="display:none"');?> id="backup_li">
                <form action="?a=setting#backup" method="post">
                    <input type="hidden" name="setting_type" value="backup"/>
                    <div class="checkbox"><label><input type="checkbox" name="excel_store_to_storage"<? if(setting::get('backup_excel_store_to_storage')=='on')echo'checked';?>/>导出的excel表自动存档备份至Storage</label></div>
                    <div class="checkbox"><label><input type="checkbox" name="excel_auto_width"<? if(setting::get('backup_excel_auto_width')=='on')echo'checked';?>/>导出Excel表自动调整列宽</label></div>
                    <p><input type="submit" value="保存" class="btn btn-default"/><? if($_POST['setting_type']=='backup')echo'已保存'; ?></p>
                </form>
            </li>
            <hr>
            <li><p id="change_password"><? echo($_POST['setting_type']=='change_password'?'    ':'&gt;');?>&nbsp;修改密码</p></li>
            <li<? echo($_POST['setting_type']=='change_password'?'':' style="display:none"');?> id="change_password_li">
                <form action="?a=setting#change_password" method="post">
                    <input type="hidden" name="setting_type" value="change_password"/>
                    <div class="<? echo(isset($setting_notice_change_pwd_old)?' has-error':'');?>">
                        <span class="control-label">原密码：</span>
                        <input type="password" class="form-control" name="old_pwd" id="change_password_old" autocomplete="off"/>
                        <span class="help-block"><? echo(isset($setting_notice_change_pwd_old)?$setting_notice_change_pwd_old:'');?></span>
                    </div>
                    <p></p>
                    <div class="<? echo(isset($setting_notice_change_pwd_new)?' has-error':'');?>">
                        <span class="control-label">新密码：</span>
                        <input type="password" class="form-control" name="new_pwd" id="change_password_new" autocomplete="off"/>
                        <span class="help-block"><? echo(isset($setting_notice_change_pwd_new)?$setting_notice_change_pwd_new:'');?></span>
                    </div>
                    <p><input type="submit" value="保存" class="btn btn-default" id="change_password_submit"/><? if($_POST['setting_type']=='change_password' && !isset($setting_notice_change_pwd_old) && !isset($setting_notice_change_pwd_new))echo'已保存'; ?></p>
                </form>
            </li>
            <hr>
            <li><p id="clear_tinywebdb"><? echo($_POST['setting_type']=='clear_tinywebdb'?'    ':'&gt;');?>&nbsp;清除数据</p></li>
            <li<? echo($_POST['setting_type']=='clear_tinywebdb'?'':' style="display:none"');?> id="clear_tinywebdb_li">
                <form action="?a=setting#clear_tinywebdb" method="post">
                    <input type="hidden" name="setting_type" value="clear_tinywebdb"/>
                    <input type="hidden" name="clear_type" value="clear_data"/>
                    <input type="submit" value="清空数据库" class="btn btn-danger"/>
                    <span class="text-danger">执行清空数据库，设置将被保留</span>
                </form>
                <form action="?a=setting#clear_tinywebdb" method="post">
                    <input type="hidden" name="setting_type" value="clear_tinywebdb"/>
                    <input type="hidden" name="clear_type" value="clear_settings"/>
                    <input type="submit" value="初始化设置" class="btn btn-danger"/>
                    <span class="text-danger">清除设置将会重置已有密码，请及时设置新密码</span>
                </form>
                <form action="?a=setting#clear_tinywebdb" method="post">
                    <input type="hidden" name="setting_type" value="clear_tinywebdb"/>
                    <input type="hidden" name="clear_type" value="clear_all"/>
                    <input type="submit" value="清空数据库和设置信息" class="btn btn-danger"/>
                </form>
            </li>
            <hr>
        </ol>
        <span style="color:#aaa">更多设置开发中…… / More setting still developing...</span>
    </div>
</div>