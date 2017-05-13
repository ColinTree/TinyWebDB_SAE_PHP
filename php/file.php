<?
if(!defined('MANAGEversion')){http_response_code(404);exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n<p>The requested URL /php/init.php was not found on this server.</p>\n</body></html>\n");}

?><div id="file_preview_background" style="display:none"><div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">预览文件</h3><div><span class="glyphicon glyphicon-remove"></span></div></div><div class="panel-body text-center"><div file-loading>加载中……</div><img src="" style="display:none"/><pre class="line-number" style="display:none;text-align:left"></pre></div></div></div>
<div id="file_upload_background" style="display:none"><div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title">上传文件</h3><div><span class="glyphicon glyphicon-remove"></span></div></div><div class="panel-body text-center"><input type="file" name="files" class="projectfile" value="${deal.image}"/></div></div></div>
<script>
    $(document).ready(function(){
        var fileuploaded=false;
        $('input[class=projectfile]').each(function(){
            $(this).fileinput({
                uploadUrl:"?a=file-upload&noecho=true&dir=<? echo htmlspecialchars($_REQUEST['dir']); ?>",
                autoReplace:true,
                language:'zh',
                maxFileSize:4000,
                maxFileCount:5,
            });
        }).off('fileuploaded').on('fileuploaded',function(msg,params,event){
            $('#file_upload_background span.glyphicon-remove').off('click').click(function(){
                $(this).parent().parent().parent().fadeOut(200);
                $('#all_processing_msg1').text('由于您上传了新的文件');
                $('#all_processing_msg2').text('我们将刷新页面');
                $('#all_processing_background').fadeIn(200)
                    .children('div[class*=panel-default]').css('left','50%').css('top','50%');
                setTimeout(function(){location.reload(true);},2000);
            });
            alertify.success('上传完成，您可以选择继续上传其他文件');
        });
    });
</script>
<div class="panel panel-default"><div class="panel-heading"><h3 class="panel-title" style="display:inline-block;">文件列表：<? echo htmlspecialchars(kvfile::formatFN($_REQUEST['dir'])); ?></h3><div id="file_upload_div"><button class="btn btn-default"><span class="glyphicon glyphicon-cloud-upload"></span></button></div></div><div class="panel-body text-center"><table class="table table-hover"><thead><th></th><th>文件名</th><th>操作</th></thead><tbody><?
    $filelist=kvfile::getfilelist($_REQUEST['dir']);
    $path=kvfile::formatFN($_REQUEST['dir']);
    if(strlen($path)>3 && substr($path,-1)=='/'){
        $upper=explode('/',$path);array_pop($upper);array_pop($upper);$upperdir=implode('/',$upper);
        $td1=''; $td2='<a kvfile-dirpath="'.htmlspecialchars($upperdir).'">../ <small>(上一级目录)</small></a>'; $td3='<button class="btn btn-primary" kvfile-open-dir>进入</button>';
        echo'<tr><td>',$td1,'</td><td>',$td2,'</td><td>',$td3,'</td></tr>';
    }
    foreach($filelist as $path){
        $fullpath=kvfile::formatFN($_REQUEST['dir'].'/'.$path);
        if(substr($path,-1)=='/'){
            $td1=''; $td2='<a kvfile-dirpath="'.htmlspecialchars($fullpath).'">'.htmlspecialchars($path).'</a><br><div kvfile-filesize>文件夹</div>'; $td3='<button class="btn btn-primary" kvfile-open-dir>进入</button>';
        }else{
            $extension=kvfile::getextension(kvfile::getmime(kvfile::read($fullpath))); $extension=$extension=='bin'?'file':$extension;
            $formatedsize=kvfile::getformatedsize($fullpath);
            $td1='';/*'<input type="checkbox" kvfile_path="'.$path.'">';*/ $td2='<a kvfile-filepath="'.htmlspecialchars($fullpath).'">'.htmlspecialchars($path).'</a><br><div kvfile-filesize>'.$extension.' - '.$formatedsize.'</div>'; $td3='<button class="btn btn-primary" kvfile-show-file>预览</button><button class="btn btn-primary" kvfile-download-file>下载</button><button class="btn btn-danger" kvfile-delete-file>删除</button>';
        }
        echo'<tr><td>',$td1,'</td><td>',$td2,'</td><td>',$td3,'</td></tr>';
    }
    ?></tbody></table></div></div>