function CheckUpdate(url,compare){
    try{
        var xmlhttp=(window.XMLHttpRequest) ? (new XMLHttpRequest()) : (new ActiveXObject("Microsoft.XMLHTTP"));
        xmlhttp.onreadystatechange=function(){
            if(xmlhttp.readyState==4 && xmlhttp.status==200){
                if(xmlhttp.responseText>compare){
                    document.getElementById("update_available").style.display="table-cell";
                }
            }
        };
        xmlhttp.open("GET","http://"+url+"/version/TinyWebDB_SAE_PHP",true);
        xmlhttp.send();
    }catch(err){console.log(err.message);}
}
function changeSetting(val,defaultVal,id){
    val=val.replace(/&/g,'&amp;').replace(/'/g,'&#39;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    document.getElementById(id).innerHTML=(val==""?defaultVal:val);
}
function changeVisibility(id){
    ele=document.getElementById(id);
    ele2=document.getElementById(id.substring(0,id.length-3));
    if(ele.style.display!='none'){
        ele.style.display='none';
        ele2.innerHTML="&gt;"+ele2.innerHTML.substring(4);
    }else{
        ele.style.display='list-item';
        ele2.innerHTML="    "+ele2.innerHTML.substring(4);
    }
}
function checkChangePassword(ele){
    var pwd1=ele.parentNode.previousSibling.previousSibling.previousSibling.firstChild.nextSibling;
    var pwd2=ele.parentNode.previousSibling.firstChild.nextSibling;
    var rtn=true;
    if(pwd1.value==""){
        pwd1.parentNode.className='has-error';
        pwd1.nextSibling.innerHTML="本项必填！";
        rtn=false;
    }else if(pwd1.value.length<6){
        pwd1.parentNode.className='has-error';
        pwd1.nextSibling.innerHTML="原密码过短(长度小于6)！如需绕过此检查请禁用javascript";
        rtn=false;
    }
    if(pwd2.value==""){
        pwd2.parentNode.className='has-error';
        pwd2.nextSibling.innerHTML="本项必填！";
        rtn=false;
    }else if(pwd2.value.length<6){
        pwd2.parentNode.className='has-error';
        pwd2.nextSibling.innerHTML="密码过短(长度小于6)将不能保证您的数据库安全！";
        rtn=false;
    }
    return rtn;
}
function changePasswordOnInput(ele){
    //
    ele.nextSibling.innerHTML='';
    ele.parentNode.className='';
}
function expand(){
    var liList=["security_li","special_tags_li","backup_li","change_password_li"];
    var count=0;
    for(var i=0;i<liList.length;i++){
        if(document.getElementById(liList[i]).style.display=='none')
            count++;
    }
    var new_visibility='list-item';
    if(count<(liList.length/2))
        new_visibility='none';
    for(var i=0;i<liList.length;i++){
        document.getElementById(liList[i]).style.display=new_visibility;
        document.getElementById(liList[i].substring(0,liList[i].length-3)).innerHTML=
            (new_visibility=="none"?"&gt;":"    ")+
            document.getElementById(liList[i].substring(0,liList[i].length-3)).innerHTML.substring(4);
    }
}