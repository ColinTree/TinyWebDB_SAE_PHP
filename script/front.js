$(document).ready(function(){
    $("form[action='/tinywebdb/getvalue']").submit(function(){
        $.ajax({sync:true,url:$(this).attr("action"),method:"POST",data:{"tag":$("#getvalue_tag").val()}}).done(function(response){if(response[2]=="")$("#getvalue_value").text("该标签的值为空").css("color","#555");else $("#getvalue_value").text(response[2]).css("color","black");}).fail(function(){$("#getvalue_value").text("获取失败(可能无权限)").css("color","#555");});
        $("#getvalue_value").text("查询中……").css("color","#555");
        return false;
    });
    $("form[action='/tinywebdb/storeavalue']").submit(function(){
        $.ajax({sync:true,url:$(this).attr("action"),method:"POST",data:{"tag":$("#storeavalue_tag").val(),"value":$("#storeavalue_value").val()}}).done(function(response){$("#storeavalue_status").text("已将标签“"+response[1]+"”的值更新为“"+response[2]+"”").css("color","black");}).fail(function(){$("#storeavalue_status").text("储存失败").css("color","black");});
        $("#storeavalue_status").text("储存中……").css("color","#555");
        return false;
    });
    $("#getvalue_save").change(function(){if($(this).prop("checked"))$("#getvalue_tag").attr("autocomplete","on");else $("#getvalue_tag").attr("autocomplete","off");});
    $("#storeavalue_save").change(function(){if($(this).prop("checked")){$("#storeavalue_tag").attr("autocomplete","on");$("#storeavalue_value").attr("autocomplete","on");}else{$("#storeavalue_tag").attr("autocomplete","off");$("#storeavalue_value").attr("autocomplete","off");}});
});