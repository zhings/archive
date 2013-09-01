<!doctype html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>MO-UP</title>
    <link rel="stylesheet" href="pure-min.css">
    <link rel="stylesheet" href="mo-up.css">
</head>
<body>
    <div class="box">
        <h3>图片管理</h3>
        <ul class="pure-g thumb">
            <li class="pure-u-1-5">
                <img src="//l300.qiniudn.com/300x300">
                <a class="remove">×</a>
            </li>
            <li class="pure-u-1-5"><img src="//l300.qiniudn.com/300x300"></li>
            <li class="pure-u-1-5"><img src="//l300.qiniudn.com/300x300"></li>
            <li class="pure-u-1-5">
                <span class="loading">75%</span>
                <a class="remove">×</a>
            </li>
            <li class="pure-u-1-5"><img src="//l300.qiniudn.com/300x300"></li>
            <li class="pure-u-1-5">
                <div id="drop">
                    拖拽图片到这儿

                    <a title="点击添加图片">＋</a>
                    <input id="upload" type="file" name="file" multiple>
                </div>
            </li>
        </ul>
    </div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="jquery.ui.widget.js"></script>
<script src="jquery.iframe-transport.js"></script>
<script src="jquery.fileupload.js"></script>
<?php 
//  服务端生成 upToken
require("qiniu/rs.php");
$putPolicy = new Qiniu_RS_PutPolicy('l300');
$upToken = $putPolicy->Token(null);
?>
<script>
$(function(){

    //  随机数据函数（6位）
    var rd = function(){
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        for ( var i=0; i < 6; i+=1 ){
            text += possible.charAt(Math.floor(Math.random() * possible.length));
        }
        return text;
    };


    //  将a点击绑定到input点击，弹出选择文件的对话框
    $("#drop a").click(function(){
        $("#upload").click();
    });

    //  jQuery File Upload 插件核心配置
    $("#upload").fileupload({
        dropZone: $("#drop"),
        url:"http://up.qiniu.com/",

        add: function (e, data) {

            //  额外添加七牛文件上传所需的数据
            data.formData = {
                "token":"<?php echo $upToken;?>",
                "key":rd()
                //"x.model":$("#model").val()   //  自定义数据
            };

            var tpl = $('<li class="pure-u-1-5"><span class="loading">…</span><a class="cancel">×</a></li>');
            data.context = tpl.insertBefore($("#drop").parent("li"));

            //  监听a点击事件，撤销上传
            tpl.find(".cancel").click(function(){
                jqXHR.abort();
                tpl.fadeOut(function(){
                    tpl.remove();
                });
            });

            var jqXHR = data.submit();  //  自动上传提交到队列的文件
        },

        progress: function(e, data){
            var progress = parseInt(data.loaded / data.total * 100, 10);
            data.context.find(".loading").text(progress + '%');
            /*
            if (progress == 100){
                data.context.find("a").removeClass("cancel");
            }*/
        },

        done: function (e, data){
            var url = 'http://l300.qiniudn.com/' + data.result.key;
            var foo = '<img src="'+url+'/s" width="138" heigth="138">\
            <input type="hidden" value="'+url+'" name="images[]">\
            <a class="remove">×</a>';
            data.context.html(foo);
        },

        fail:function(e, data){
            data.context.addClass('error');
        }

    });     //  结束 jQuery File Upload 插件核心配置

    //  阻止文件戳拖拽时的默认动作
    $(document).on('drop dragover', function (e) {
        e.preventDefault();
    });

    //  监听绑定文件移除remove事件
    $(document).on("click", ".remove", function(event){
        alert("暂时不能删除文件！");
    });
});
</script>
</body>
</html>