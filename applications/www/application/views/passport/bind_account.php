<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>约车客-账号绑定</title>
    <meta name="keywords" content="<?php echo $seo['keywords']?>">
    <meta name="description" content="<?php echo $seo['description']?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">

    <!-- 引入项目css资源文件,并配置构建地址演示 -->
    <link rel="stylesheet" href="<?php echo css_js_url('weui.min.css', 'app')?>">
    <link rel="stylesheet" href="<?php echo css_js_url('carService.css', 'app')?>">
   
  
</head>
<body>  
    <div class="cs-login user-login">
        <div class="cs-header">
            <h1>账号绑定</h1>
            <a href="tel:<?php echo $seo['company_tel']?>" class="contact"></a>
        </div>
        <div class="cs-cont mainfix">
            <div class="cs-ad">
               
            </div>
            <div class="weui_cells weui_cells_form">
                <!-- weui_cell_warn -->
                        <input type="hidden" name="open_id" value="<?php echo $open_id; ?>">
                        <input type="hidden" name="state" value="<?php echo $state; ?>" >
                        <input type="hidden" name="nickname" value="<?php echo $nickname; ?>" >
                        <input type="hidden" name="sex" value="<?php echo $sex; ?>" >
                        <input type="hidden" name="head_img" value="<?php echo $head_img; ?>" >
                        <input type="hidden" name="address" value="<?php echo $address; ?>" >
                        <input type="hidden" name="token" value="<?php echo $token ?>">
                <div class="weui_cell">
                    <div class="weui_cell_hd"><label class="weui_label">手机号</label></div>
                    <div class="weui_cell_bd weui_cell_primary">
                        <input class="weui_input" type="number" pattern="[0-9]*"  name="phone" placeholder="&#x8BF7;&#x8F93;&#x5165;&#x624B;&#x673A;&#x53F7;">
                    </div>
                </div>
                <div class="weui_cell">
                    <div class="weui_cell_hd"><label class="weui_label">验证码</label></div>
                    <div class="weui_cell_bd weui_cell_primary">
                        <input class="weui_input" type="number" pattern="[0-9]*" name="code" placeholder="&#x8BF7;&#x8F93;&#x5165;&#x9A8C;&#x8BC1;&#x7801;">
                    </div>
                    <div class="weui_cell_ft">
                        <button class="weui_btn weui_btn_mini weui_btn_default" id="getCode">获取验证码</button>
                    </div>
                </div>
            </div>
                <p class="tips" id="message"></p>
           
           
            <a href="javascript:;" class="weui_btn weui_btn_primary cs-btn submit">确认绑定</a>
        </div>
    </div>
    <!-- 引入项目js资源文件,并配置构建地址演示 -->
    <script src="<?php echo css_js_url('zepto.min.js', 'app')?>"></script>
    <script src="<?php echo css_js_url('carService.js', 'app')?>"></script>
    <script src="<?php echo css_js_url('my_dialog.js', 'app')?>"></script>
    <script>
    $(function(){
    	$(".submit").click(function(){
    		var user_mobile = $("input[name='phone']").val();
    		var verify = $("input[name='code']").val();

    		var open_id = $('input[name=open_id]').val();
            var state = $('input[name=state]').val();
            var head_img = $('input[name=head_img]').val();
            var address = $('input[name=address]').val();
            var sex = $('input[name=sex]').val();
            var nickname = $('input[name=nickname]').val();

    		if(user_mobile == ''){
    			$("#message").html('手机号不能为空！');
    			$('input[name="phone"]').focus();
    			return false;
            }
    		var isMobile=/^1[3|4|5|8|7][0-9]\d{8}$/;
    		if(!isMobile.test(user_mobile)){
    			$("#message").html('手机号不正确！');
    			$('input[name="phone"]').focus();
    			return false;
    		}
    		if(verify == ''){
    			$("#message").html('手机验证码不能为空！');
    			$('input[name="code"]').focus();
    			return false;
            }
    		else{
    			$("#message").html("");
    		}
    		
    	  	$.post(
        			'/passport/bind_account', 
        			{
          				mobile:user_mobile,
          				code:verify,
          				open_id:open_id,
          				state:state,
          				head_img:head_img,
          				address:address,
          				sex:sex,
          				nickname:nickname
          			}, 
        			function(res){
        				if(res.status == 0){
        				  	window.location.href = res.data.url;
        				}else {
        					$("#message").html(res.msg);
        				}
        	});
    	});
    	
        $('#getCode').on('click', function(){
            var btn = $(this);
            var count = 60;
            var mobile_num = $('input[name="phone"]').val();
            var token = $('input[name="token"]').val();
            var is_mobile=/^1[3|4|5|8|7][0-9]\d{8}$/;
            
            if(!is_mobile.test(mobile_num)){
            	$("#message").html("请输入11位有效手机号！");
            	return false;
            }else{
    			$("#message").html("");
    		}
            btn.text(count+"秒后可重获取");
            var resend = setInterval(function(){
                count--;
                if (count > 0){
                    btn.text(count+"秒后可重获取");
                    btn.attr('disabled',true).css('cursor','not-allowed');
                }else {
                    clearInterval(resend);
                    btn.text("获取验证码").removeAttr('disabled').removeAttr('style');
                }
            }, 1000);
            $.post(
            		"/publicservice/mobile_code", 
            		{mobile:mobile_num,token:token},
            		function(data){
            			if(data.status !=0){
            				$("#message").html(data.msg);
            				clearInterval(resend);
                            btn.text("获取验证码").removeAttr('disabled').removeAttr('style');
            			}
            		},
            		'json'
            );
            
        });
        
        //邀请码
        $(".invite-code").click(function(){
        	$("#invite_code_input").toggle();
        })
    });

    </script>
</body>
</html>
