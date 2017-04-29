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
function post(path, params, method) {  //from   http://stackoverflow.com/questions/133925/javascript-post-request-like-a-form-submit
    method = method || "post";
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);
    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);
            form.appendChild(hiddenField);
         }
    }
    document.body.appendChild(form);
    form.submit();
}
$(document).ready(function(){
    //search - filter expand
    $('#search_filter_controler').click(function(){
        $(this).children('span').css('transform',(
            $(this).next().css('display')=='none' ?
            'rotate(180deg)' :
            'rotate(0deg)'
        ));
        $(this).next().toggle(400);
    });
    
    //setting - expand
    $('#setting_expand').click(function(){
        var count=0, tot=0, children=$('#setting_ol').children('li[id]');
        children.each(function(){
            if($(this).css('display')=='none')
                count++;
            tot++;
        });
        var new_vis=(count<(tot/2) ? 'none' : 'list-item'),
            char=(new_vis=="none" ? ">" : " ");
        children.each(function(){
            var id=$(this).attr('id'),
                p=$('#'+id.substring(0,id.length-3));
            $(this).css('display',new_vis);
            p.text(char + p.text().substring(1));
        });
    });
    
    //setting - ol>li
    $('#setting_ol>li:not([id])').click(function(){
        var p=$(this).children('p'), li=$(this).next();
        if(li.css('display')!='none'){
            li.hide();
            p.text(">"+p.text().substring(1));
        }else{
            li.css('display','list-item');
            p.text(" "+p.text().substring(1));
        }
    });
    
    //setting - all
    /*$('#setting_all_form').submit(function(){
        post($(this).attr('action'), {'setting_type':'all'}, $(this).attr('method'));
        return false;
    });*/
    
    //setting - change_password
    function changePasswordOnInput(ele){
        $(ele).next().text('');
        $(ele).parent().removeClass('has-error');
    }
    $('#change_password_old').on('input',function(){
        changePasswordOnInput(this);
    });
    $('#change_password_new').on('input',function(){
        changePasswordOnInput(this);
    });
    $('#change_password_submit').click(function(){
        var pwd1=$(this).parent().prev().prev().prev().children(':first').next();
        var pwd2=$(this).parent().prev().children(':first').next();
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
    });
    
    //setting - clear_tinywebbdb
    function setting_clear_tinywebdb(val){
        $('#all_processing_msg1').text('已确认清除');
        $('#all_processing_msg2').text('请勿打断自动跳转');
        $('#all_processing_background').fadeIn(200);
        setTimeout(function(){ $('input[value='+val+']').parent().submit(); },1500);
    }
    $('input[value=清空数据库]').click(function(){
        if('确认清除所有标签'==prompt('请输入“确认清除所有标签”以确认清空数据库')){setting_clear_tinywebdb('clear_data');}
        return false;
    });
    $('input[value=初始化设置]').click(function(){
        if('确认清除所有设置'==prompt('请输入“确认清除所有设置”以确认清空数据库')){setting_clear_tinywebdb('clear_settings');}
        return false;
    });
    $('input[value=清空数据库和设置信息]').click(function(){
        if('确认重置整个系统'==prompt('请输入“确认重置整个系统”以确认清空数据库')){setting_clear_tinywebdb('clear_all');}
        return false;
    });
    
    //all - category
    $('#all_category').change(function(){
        document.location="?a=all"+"&category="+escape($(this).val());
    });
    
    //all - multi chioce
    var all_checked=0,all_toolbar_showed=false;
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
            $('#all_processing_background').fadeOut(50).fadeIn(50);
            location.reload(true);
        }).fail(function(){
            alert('批量删除失败：未知原因');
            $('#all_processing_background').fadeOut(200);
        });
    });
});