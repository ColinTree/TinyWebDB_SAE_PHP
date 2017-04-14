# TinyWebDB_SAE_PHP
在新浪云SAE上，跑TinyWebDB   
基于(PHP)

特点：   
>便宜啊   
>费用压到新浪云最低价格了   
>一天一毛钱   
>一年也才36.5好伐，，   

### [下载地址](https://github.com/ColinTree/TinyWebDB_SAE_PHP/tree/Download)

## 使用教程
首先前往<b><a target="_blank" href="http://sae.sina.com.cn">新浪云应用</a></b>，还没注册的朋友可以通过<b><a target="_blank" href="http://t.cn/R4Yn6cv">我的邀请</a></b>注册一下，当做是给我支持hh
    <br>
    然后在<b><a target="_blank" href="http://sae.sina.com.cn">新浪云应用SAE主页</a></b>，点击创建新应用
    <br>
    在这个界面选择适用的服务器环境：
    <ul>
        <li>这里我提供的代码是PHP的，所以语言选择PHP</li>
        <li>其中最关键的是标准环境这一项，如果是选择云空间，会导致费用飙升</li>
        <li>语言版本没什么好说的，5.6已测未发现问题</li>
        <li>代码管理看个人喜好，我一般选择SVN，但是GIT更加流行。这里无论选哪个，本文后续步骤都一样</li>
    </ul>
    <img style="max-width:100%;border:1px solid #aaa;" src="http://extensions.sinacloud.net/ArticlePics/TinyWebDB_SAE_PHP/step1.png">
    <br>
    应用信息按照自己的想法填就好了
    <br>
    最后确认创建应用
</p>
<p>
	下一步是创建代码版本（在上一步最后的时候，点击创建，会开始部署，然后自动跳转代码管理页面）
    <br>
    先创建一个版本，版本号随意：
    <br>
    <img style="max-width:100%;border:1px solid #aaa;" src="http://extensions.sinacloud.net/ArticlePics/TinyWebDB_SAE_PHP/step2.png">
    <br>
    <img style="max-width:100%;border:1px solid #aaa;" src="http://extensions.sinacloud.net/ArticlePics/TinyWebDB_SAE_PHP/step3.png">
    <br>
    在新建的版本中，找到上传代码包，导入<b><a target="_blank" href="TinyWebDB_SAE_PHP-1">完整安装文件</a></b>
    <br>
    <small>zip包内容：（如不是新创建的应用请慎重导入）<ul style="margin-top:-16px"><li>/config.yaml</li><li>/php/tinywebdb.php</li><li>/php/tinywebdbMANAGE.php</li></ul></small>
    <img style="max-width:100%;border:1px solid #aaa;" src="http://extensions.sinacloud.net/ArticlePics/TinyWebDB_SAE_PHP/step4.png">
    <br>
    待导入完毕，大功告成
    <br>
    （为了您的数据安全，请尽快前往后台进行密码设置）
</p>
<p>
	为防止有的朋友还是不懂怎么用，这里再继续解释一下用法：
    <br>
    这里我作为演示的应用，名字为tinywebdbsae，那么我的tinywebdb服务地址就是 http://tinywebdbsae.applinzi.com/tinywebdb
    <br>
    而只需要将这个地址，填入网络微数据库的 服务地址 这一组件属性即可
    <br>
    <br>
    如果要在网页上查看或操作的话，直接访问 http://tinywebdbsae.applinzi.com/tinywebdb 就可以直接看到可视化管理页面
    <br>
    <br>
    <b>祝AI愉快！</b>
</p>
