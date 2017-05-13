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
function lineNumber(jqueryEle){
    if(jqueryEle.length){
        var arr=jqueryEle.html().split("\n");
        var text="<ol>";
        $.each(arr,function(i,item){
            if(item.trim()!=""){
                text+="<li><a>"+item+"</a></li>";
            }
        });
        if(text=='<ol>'){
            text+='<li><a style="color:gray">(该标签的值为空)</a></li>';
        }
        text+="</ol>";
        jqueryEle.html(text).addClass('line-number');
    }
}
function myconfirm(title,msg,btnok,btncancel,fnok,fncancel){
    $('#myconfirm_title').text(title);
    $('#myconfirm_msg').text(msg);
    $('#myconfirm_ok').text(btnok);
    $('#myconfirm_cancel').text(btncancel);
    $('#myconfirm_background').fadeIn(200).off('click').click(function(){
        $('#myconfirm_cancel').click();
    }).children('div[class*=panel-default]').off('click').click(function(){
        return false;
    }).css('left','50%').css('top','50%');
    $('#myconfirm_ok').keyup(function(e){
        if(e.keyCode==27){
            $('#myconfirm_cancel').click();
            $('#myconfirm_ok').off('keyup');
        }
    });
    $('#myconfirm_ok').off('click').click(function(){
        $('#myconfirm_background').fadeOut(200);
        if($.isFunction(fnok)){
            fnok.call();
        }
    }).focus();
    $('#myconfirm_cancel').off('click').click(function(){
        $('#myconfirm_background').fadeOut(200);
        if($.isFunction(fncancel)){
            fncancel.call();
        }
    });
}
$(document).ready(function(){
    lineNumber($("pre.line-number"));
    
    //file - upload (bootstrap fileinput)
    //THIS PART MOVED TO FILE.PHP
    
    //file - filename clicked
    $('[kvfile-filepath]').click(function(){
        $(this).parent().next().children('[kvfile-download-file]').click();
        return false;
    })
    //file - tr.click (locale from filename)
    .parent().parent().click(function(){
        $(this).find('[kvfile-show-file]').click();
    });
    
    //file - dir clicked
    $('[kvfile-dirpath]').click(function(){
        $(this).parent().next().children('[kvfile-open-dir]').click();
    })
    //file - tr.click (locale from dir)
    .parent().parent().click(function(){
        $(this).find('[kvfile-open-dir]').click();
    });

    //file - buttons
    $('#file_upload_div>button').click(function(){
        $('#file_upload_background').fadeIn(200);
    });
    $('[kvfile-open-dir]').click(function(){
        location.href='?a=file&dir='+escape($(this).parent().prev().children('a').attr('kvfile-dirpath'));
        return false;
    });
    $('[kvfile-dir-auth],[kvfile-file-auth]').click(function(){
        var filepath=$(this).parent().prev().children('a').attr('kvfile-'+($(this).is('[kvfile-dir-auth]') ? 'dir' : 'file')+'path');
        $('#file_auth_background').fadeIn(200).attr('kvfile-filepath',filepath).addClass('loading')
            .children('div[class*=panel-default]').css('left','50%').css('top','50%')
            .parent().find('input,button').prop('disabled',true);
        $('#file_auth_background [class*=heading] h3').text('设置'+($(this).is('[kvfile-dir-auth]') ? '目录' : '文件')+'权限');
        $.ajax({
            type:"post",
            async:true,
            url:'?a=file-getauth',
            data:{'noecho':true,'filepath':filepath}
        }).done(function(response){
            if(response.status===true){
                $('#file_auth_write').prop('checked',response.auth=='wr'||response.auth=='rw'||response.auth=='w');
                $('#file_auth_read').prop('checked',response.auth=='wr'||response.auth=='rw'||response.auth=='r');
                $('#file_auth_background').removeClass('loading')
                    .find('input,button').prop('disabled',false);
            }else{
                alertify.error('加载失败');
            }
        }).fail(function(){
            alertify.error('加载失败');
        });
        return false;
    });
    $('#file_auth_write, #file_auth_read').click(function(e){
        e.stopPropagation();
    }).parent().click(function(e){
        $(this).children('input').prop('checked', !$('#file_auth_background').hasClass('loading') && !$(this).children('input').prop('checked')===true);
    });
    $('#file_auth_background button').click(function(){
        var filepath=$(this).parent().parent().parent().parent().attr('kvfile-'+($(this).is('[kvfile-dir-auth]') ? 'dir' : 'file')+'path');
        $(this).text('保存中……').prop('disabled',true);
        $.ajax({
            type:"post",
            async:true,
            url:'?a=file-setauth',
            data:{'noecho':true,'filepath':filepath,'auth':''+($('#file_auth_write').prop('checked')===true ? 'w' : '')+($('#file_auth_read').prop('checked')===true ? 'r' : '')}
        }).done(function(response){
            if(response.status===true){
                $('#file_auth_background button').text('保存成功').prop('disabled',false).removeClass('btn-primary').addClass('btn-success');
                setTimeout(function(){$('#file_auth_background button').text('保存').addClass('btn-primary').removeClass('btn-success');},1500);
            }else{
                alertify.error('保存失败');
                $('#file_auth_background button').prop('disabled',false);
            }
        }).fail(function(){
            alertify.error('加载失败');
            $('#file_auth_background button').prop('disabled',false);
        });
    });
    $('[kvfile-show-file]').click(function(){
        $('#file_preview_background').fadeIn(200).off('click').click(function(){$(this).find('span.glyphicon-remove').click();})
            .children().click(function(){return false;}) // window.click ignore the click event of background's
            .find('[file-loading]').show()
            .next().hide()
            .next().hide();
        $('#file_preview_background')
            .children('div[class*=panel-default]').css('left','50%').css('top','50%');
        var filepath='?a=file-get&noecho=true&filename='+encodeURI($(this).parent().prev().children('a').attr('kvfile-filepath'));
        var ct=$(this).parent().parent().find('[kvfile-filesize]').text(); //content-type showed(some in chinese)
        if(ct.match(/图片/)){
            $('#file_preview_background [file-loading]')
                .next().show().attr('src',filepath).off('load').on('load',function(){$(this).prev().hide();})
                .next().hide();
        }else if(ct.match(/文本文件/)){
            $.ajax({
                type:"GET",
                async:true,
                url:filepath
            }).done(function(message){
                $('#file_preview_background [file-loading]').hide()
                    .next().hide()
                    .next().show().text(message);
                lineNumber($('#file_preview_background [file-loading]').next().next());
            }).fail(function(){
                alertify.error('获取文件内容失败');
                $('#file_preview_background').fadeOut(200)
            });
        }else{
            $('#file_preview_background [file-loading]').hide()
                .next().hide()
                .next().show().text('暂时不支持预览该文件');
        }
        return false;
    });
    $('[kvfile-download-file]').click(function(){
        window.open('file'+$(this).parent().prev().children('a').attr('kvfile-filepath'));
        return false;
    });
    $('[kvfile-delete-file]').click(function(){
        var filepath=$(this).parent().prev().children('a').attr('kvfile-filepath');
        myconfirm('删除文件','确认删除文件“'+filepath+'”吗？执行之后无法恢复','确认','取消',function(){
            $('#all_processing_msg1').text('删除中……');
            $('#all_processing_msg2').text('请勿关闭本页面');
            $('#all_processing_background').fadeIn(200)
                .children('div[class*=panel-default]').css('left','50%').css('top','50%');
            $.ajax({
                sync:true,
                method:'post',
                url:'?a=file-delete',
                data:{'noecho':true,'filepath':filepath}
            }).done(function(response){
                $('#all_processing_msg1').text('删除完成');
                $('#all_processing_msg2').text('页面正在刷新');
                $('#all_processing_background').fadeOut(50).fadeIn(50);
                setTimeout(function(){location.reload(true);},800);
            }).fail(function(){
                alertify.error('删除失败：未知原因');
                $('#all_processing_background').fadeOut(200);
            });
        },function(){
            alertify.log('删除取消');
        });
        
        return false;
    });
    
    //search - filter expand
    $('#search_filter_controler').click(function(){
        $(this).children('span').css('transform',(
            $(this).next().css('display')=='none' ? 'rotate(180deg)' : 'rotate(0deg)'
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
        $('#all_processing_background').fadeIn(200)
            .children('div[class*=panel-default]').css('left','50%').css('top','50%');
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
    
    //all - window - close
    $('div[id*=_background] span.glyphicon-remove').parent().click(function(){
        $(this).parent().parent().parent().fadeOut(200);
        return false; // return false for ignoring the same events of the parents
    }).mousedown(function(){
        return false;
    }).mousemove(function(){
        return false;
    }).mouseup(function(){
        return false;
    });
    
    //all - window - heading draged
    var px=0; //page x
    var py=0; //page y
    var awhp=null;//all_window heading's parent
    $('div[id*=_background] div[class*=heading]').mousedown(function(e){
        px=e.pageX;
        py=e.pageY;
        awhp=$(this).parent();
    });
    $(document).mousemove(function(e){
        if(awhp!==null){
            var x=parseInt(awhp.css('left'))+(e.pageX-px),
                y=parseInt(awhp.css('top')) +(e.pageY-py)
            awhp.css('left',x+'px').css('top',y+'px');
            px=e.pageX;
            py=e.pageY;
        }
    }).mouseup(function(e){
        awhp=null;
    });
        
    //all - window - escape
    $('*').keyup(function(e){
        if(e.keyCode==27){
            $('div[id*=_background] span.glyphicon-remove').not('#file_upload_background *').parent().click();
        }
    });
    $('div[id*=_background]').filter('[id*=tag_],[id*=file_]').not('#file_upload_background').click(function(e){
        $(this).find('span.glyphicon-remove').parent().click();
    }).children('div').click(function(){
        return false;
    });
    
    //all - window - set - submit
    $('#tag_edit_background input[type=text]').keyup(function(e){
        if(e.keyCode==13){
            $('[button-task=edit-tag-submit]').click();
        }
    });
    
    //all - input#new_tag .onEnter
    $('#new_tag').keyup(function(e){
        if(e.keyCode==13){
            $('[button-task=new-tag]').click();
        }
    });
    
    //all - buttons - new tag
    var new_tag=false;
    $('[button-task=new-tag]').click(function(){
        var tag=$(this).attr('tinywebdb_key');
        new_tag=true;
        $('#tag_edit_background h3').text('新建标签');
        $('#tag_edit_background input').val($('#new_tag').val());
        $('#tag_edit_background textarea').text('').focus();
        $('#tag_edit_background').fadeIn(200)
            .children('div[class*=panel-default]').css('left','50%').css('top','50%');
        $('#tag_edit_background textarea').focus();
    });
    
    //all - buttons - show-tag / show-tag-json
    $('[button-task=show-tag], [button-task=show-tag-json]').click(function(){
        var tag=$(this).attr('tinywebdb_key');
        $('#tag_show_background code').text(tag);
        $('#tag_show_background pre').text('加载中……');
        $('#tag_show_background').fadeIn(200)
            .children('div[class*=panel-default]').css('left','50%').css('top','50%');
        $('#tag_show_background [button-task]').prop('disabled',true);
        if($(this).text()=='查看'){
            $('#tag_show_background [button-task]').text('查看原值');
        }
        $.ajax({
            sync:true,
            method:'post',
            url:'?a=apiget'+$(this).attr('button-task').substring(8),
            data:{'noecho':true,'tag':tag}
        }).done(function(response){
            var btn=$('#tag_show_background [button-task]');
            btn .attr('tinywebdb_key',response.tag)
                .attr('button-task',btn.text()=='查看原值' ? 'show-tag-json' : 'show-tag')
                .text(btn.text()=='解析列表(或JSON)' ? '查看原值' : '解析列表(或JSON)')
                .prop('disabled',false)
                .focus();
            $('#tag_show_background code').text(response.tag);
            $('#tag_show_background pre').text(response.value);
            lineNumber($('#tag_show_background pre.line-number'));
        }).fail(function(){
            $('#tag_show_background pre').text('获取失败');
            $('#tag_show_background [button-task]').prop('disabled',false).focus();
        });
    });
    
    //all - buttons - edit tag
    $('[button-task=edit-tag]').click(function(){
        var tag=$(this).attr('tinywebdb_key');
        $('#tag_edit_background h3').text('编辑标签');
        $('#tag_edit_background input').val('').focus().prop('disabled',true);
        $('#tag_edit_background textarea').text('加载中……').prop('disabled',true);
        $('[button-task=edit-tag-submit]').prop('disabled',true);
        $('#tag_edit_background').fadeIn(200)
            .children('div[class*=panel-default]').css('left','50%').css('top','50%');
        $.ajax({
            sync:true,
            method:'post',
            url:'?a=apiget',
            data:{'noecho':true,'tag':tag}
        }).done(function(response){
            $('#tag_edit_background input[type=text]').val(response.tag).prop('disabled',false).focus();
            $('#tag_edit_background textarea').text(response.value).prop('disabled',false);
            $('[button-task=edit-tag-submit]').prop('disabled',false);
        }).fail(function(){
            $('#tag_edit_background input[type=text]').val('').prop('disabled',false).focus();
            $('#tag_edit_background textarea').text('获取失败').prop('disabled',false);
            $('[button-task=edit-tag-submit]').prop('disabled',false);
        });
    });
    
    //all - buttons - edit tag submit
    $('[button-task=edit-tag-submit]').click(function(){
        var tag=$(this).attr('tinywebdb_key');
        $(this).text('保存中……').prop('disabled',true);
        $.ajax({
            sync:true,
            method:'post',
            url:'?a=apiset',
            data:{
                'noecho':true,
                'tag':$(this).parent().prev().prev().children('input').val(),
                'value':$(this).parent().prev().children('textarea').val()
            }
        }).done(function(response){
            function setToSave(){
                $('[button-task=edit-tag-submit]').attr('class','btn btn-primary').text('保存');
            }
            if(response.status==true){
                $('[button-task=edit-tag-submit]').text('保存成功').attr('class','btn btn-success').prop('disabled',false);
                $('#tag_edit_background input[type=text], #tag_edit_background textarea').keyup(function(){
                    setToSave();
                });
            }else{
                $('[button-task=edit-tag-submit]').text('保存失败').attr('class','btn btn-danger').prop('disabled',false);
                $('#tag_edit_background input[type=text], #tag_edit_background textarea').keyup(function(){
                    setToSave();
                });
            }
            setTimeout(function(){
                setToSave();
                if(new_tag==true){
                    $('#tag_edit_background').find('span.glyphicon-remove').click();
                }
            },3000);
        }).fail(function(){
            $('[button-task=edit-tag-submit]').text('保存失败').attr('class','btn btn-danger').prop('disabled',false);
            setTimeout(function(){$('[button-task=edit-tag-submit]').attr('class','btn btn-primary').text('保存');},3000);
        });
        $('div[id*=_background]').find('span.glyphicon-remove').parent().off('click').click(function(){
            $(this).parent().parent().parent().fadeOut(200);
            $('#all_processing_msg1').text('由于您对标签进行了修改');
            $('#all_processing_msg2').text('我们将刷新页面');
            $('#all_processing_background').fadeIn(200)
                .children('div[class*=panel-default]').css('left','50%').css('top','50%');
            setTimeout(function(){location.reload(true);},2000);
        });
    });
    
    //all - buttons - delete tag
    $('[button-task=delete-tag]').click(function(){
        var tag=$(this).attr('tinywebdb_key');
        myconfirm('删除标签','确认删除操作吗？执行之后无法恢复','确认','取消',function(){
            $('#all_processing_msg1').text('删除中……');
            $('#all_processing_msg2').text('请勿关闭本页面');
            $('#all_processing_background').fadeIn(200)
                .children('div[class*=panel-default]').css('left','50%').css('top','50%');
            $.ajax({
                sync:true,
                method:'post',
                url:'?a=apimdelete',
                data:{'noecho':true,'tags':[tag]}
            }).done(function(response){
                $('#all_processing_msg1').text('删除完成');
                $('#all_processing_msg2').text('页面正在刷新');
                $('#all_processing_background').fadeOut(50).fadeIn(50);
                setTimeout(function(){location.reload(true);},2000);
            }).fail(function(){
                alertify.error('删除失败：未知原因');
                $('#all_processing_background').fadeOut(200);
            });
        },function(){
            alertify.log('删除取消');
        });
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
        myconfirm('删除标签','确认删除操作吗？执行之后无法恢复','确认','取消',function(){
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
                url:'?a=apimdelete',
                data:{'noecho':true,'tags':querydata}
            }).done(function(response){
                $('#all_processing_msg1').text('删除完成');
                $('#all_processing_msg2').text('页面正在刷新');
                $('#all_processing_background').fadeOut(50).fadeIn(50);
                setTimeout(function(){location.reload(true);},2000);
            }).fail(function(){
                alertify.error('批量删除失败：未知原因');
                $('#all_processing_background').fadeOut(200);
            });
        },function(){
            alertify.log('删除取消');
        });
    });
});