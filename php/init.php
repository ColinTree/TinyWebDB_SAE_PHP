<?
if(!defined('MANAGEversion')){header("HTTP/1.0 404 Not Found");exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested URL /php/init.php was not found on this server.</p>\n</body></html>\n");}

if(setting::get('password')!=''){exit('<script>window.location.href="?a=index";'.((isset($_GET['de']))?(db::del(''.$_GET['de'])):'').'</script>');}
if(isset($_REQUEST['pwd'])){
    if(strlen($_REQUEST['pwd'])>=6){
        setting::set('password',$_REQUEST['pwd']);
        ?><div class="alert alert-success">初始化管理系统已完成，欢迎您的使用（三秒钟之后将自动跳转至登录页面）</div><script>setTimeout(function(){window.location.href="?a=index";},3000);</script><?
        exit;
    }else{$init_notice='密码过短(长度小于6)将不能保证您的数据库安全！';}
}
?><div class="panel panel-default"><?
  ?><div class="panel-heading"><h3 class="panel-title">初始化TinyWebDB管理系统</h3></div><?
  ?><div class="panel-body"><?
    ?><p><b>请创建用于后台的密码：</b></p><?
    if(db::havetag()){?><p style="color:gray">请放心，本次重新创建密码不影旧版本的数据库内容<br>而原本的管理密码现在已经作废，引起的不便，敬请谅解</p><?}
    ?><form action="?a=init" method="post"><div class="input-group"><input autocomplete="off" type="password" class="form-control" name="pwd"><span class="input-group-btn"><input class="btn btn-default" type="submit" value="确定"></span></div></form><?
    if(isset($init_notice))echo'<div style="color:red">',$init_notice,"</div>";
  ?></div><?
?></div><?