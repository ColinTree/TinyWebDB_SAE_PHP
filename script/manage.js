function CheckUpdate(url,compare){
    $.ajax({
        sync:true,
        url:"http://"+url+"/version/TinyWebDB_SAE_PHP",
        method:'get'
    }).done(function(response){
        if(response>compare){
            $("#update_available").css('display','table-cell');
        }
    });
}
function changeSetting(val,defaultVal,id){
    $('#'+id).text(val==""?defaultVal:val);
}
function changeVisibility(id){
    var ele=$('#'+id), ele2=$('#'+id.substring(0,id.length-3));
    if(ele.css('display')!='none'){
        ele.hide();
        ele2.text(">"+ele2.text().substring(1));
    }else{
        ele.css('display','list-item');
        ele2.text(" "+ele2.text().substring(1));
    }
}
function checkChangePassword(ele){
    var pwd1=$(ele).parent().prev().prev().prev().children(':first').next();
    var pwd2=$(ele).parent().prev().children(':first').next();
    var rtn=true;
    if(pwd1.val()==""){
        pwd1.parent().addClass('has-error');
        pwd1.next().text("本项必填！");
        rtn=false;
    }else if(pwd1.val().length<6){
        pwd1.parent().addClass('has-error');
        pwd1.next().text("原密码过短(长度小于6)！如需绕过此检查请禁用javascript");
        rtn=false;
    }
    if(pwd2.val()==""){
        pwd2.parent().addClass('has-error');
        pwd2.next().text("本项必填！");
        rtn=false;
    }else if(pwd2.val().length<6){
        pwd2.parent().addClass('has-error');
        pwd2.next().text("密码过短(长度小于6)将不能保证您的数据库安全！");
        rtn=false;
    }
    return rtn;
}
function changePasswordOnInput(ele){
    $(ele).next().text('');
    $(ele).parent().removeClass('has-error');
}
function expand(){
    var liList=["security_li","special_tags_li","backup_li","change_password_li"];
    var count=0;
    for(var i=0;i<liList.length;i++){
        if($('#'+liList[i]).css('display')=='none')
            count++;
    }
    var new_visibility='list-item';
    if(count<(liList.length/2))
        new_visibility='none';
    for(var i=0;i<liList.length;i++){
        $('#'+liList[i]).css('display',new_visibility);
        $('#'+liList[i].substring(0,liList[i].length-3)).text(
            (new_visibility=="none"?">":" ")+
            $('#'+liList[i].substring(0,liList[i].length-3)).text().substring(1)
        );
    }
}
var all_checked=0,all_toolbar_showed=false;
$(document).ready(function(){
    $('input[tinywebdb_key]').change(function(){
        if($(this).prop('checked')){all_checked++;}else{all_checked--;}
        if(all_checked==0 && all_toolbar_showed){
            all_toolbar_showed=false;
            $('#all_toolbar_frame').fadeOut(100);
        }else if(all_checked!=0 && !all_toolbar_showed){
            all_toolbar_showed=true;
            $('#all_toolbar_frame').fadeIn(100);
        }
    });
    $('#all_toolbar_selectall').click(function(){
        $('input[tinywebdb_key]').each(function(){
            if($(this).prop('checked')!=true)all_checked++;
            $(this).prop('checked',true);
        });
        $('#all_toolbar_selectall').hide();
        $('#all_toolbar_selectnone').show();
    });
    $('#all_toolbar_selectnone').click(function(){
        $('input[tinywebdb_key]').prop('checked',false);
        all_checked=0;
        $('#all_toolbar_selectall').show();
        $('#all_toolbar_selectnone').hide();
    });
    $('#all_toolbar_delete').click(function(){
        if(!confirm('确认删除所有选中标签？删除后无法恢复'))return;
        var querydata=[];
        $('input[tinywebdb_key]:checked').each(function(){
            querydata.push($(this).attr('tinywebdb_key'));
        });
        $('#all_processing_msg1').text('删除中……');
        $('#all_processing_msg2').text('请勿关闭本页面');
        $('#all_processing_background').fadeIn(200);
        $.ajax({
            sync:true,
            method:'post',
            url:'?a=mdelete',
            data:{'noecho':true,'tags':querydata}
        }).done(function(response){
            $('#all_processing_msg1').text('删除完成');
            $('#all_processing_msg2').text('页面正在刷新');
            $('#all_processing_background').fadeOut(50);
            $('#all_processing_background').fadeIn(50);
            location.reload(true);
        }).fail(function(){
            alert('批量删除失败：未知原因');
            $('#all_processing_background').fadeOut(200);
        });
    });
});