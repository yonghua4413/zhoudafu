<?php $this->load->view('common/header');?>
<div class="place">
    <span>位置：</span>
    <ul class="placeul">
        <li><a href="/common">首页</a></li>
        <li><a href="#">系统管理</a></li>
        <li><a href="/about">关于我们</a></li>
        <li><a href="#">修改</a></li>
    </ul>
</div>

<div class="formbody">
<form  method="post" id="form">
    <div class="formtitle"><span>编辑</span></div>
    <ul class="forminfo">
        <li>
            <label>主题标题</label>
            <input name="title" type="text" class="dfinput" value="<?php echo $info['title']?>" valType="required" msg="不能为空"/><i></i>
        </li>
        <li>
            <label>主题封面图<b>*</b></label>
            <ul id="uploader_cover_img">
            <?php if(isset($info['cover_img'])):?>
                <?php if($info['cover_img']):?>
                <li class='pic pro_gre' style='margin-right: 20px; clear:none;'>
                    <a class='close del-pic' href='javascript:;'></a>
                    <a href="<?php echo get_img_url($info['cover_img']);?>" target="_blank"><img src="<?php echo get_img_url($info['cover_img']);?>" style='width:100%;height:100%'/></a>
                    <input type="hidden" name="cover_img" value="<?php echo $info['cover_img'];?>"/>
                </li>
                <?php endif;?>
                <?php endif;?>
                <li class="pic pic-add add-pic" style="float: left;width: 220px;height: 175px;clear:none;">
                    <a href="javascript:;" class="up-img"  id="file_cover_img"><span>+</span><br>添加照片</a>
                </li>
            </ul>
        </li>
        <li>
            <label>相册<b>*</b></label>
            <ul id="uploader_theme_img">
            <?php if(isset($info['images'])):?>
                <?php if($info['images']):?>
                <?php foreach ($info['images'] as $k=>$v):?>
                <li class='pic pro_gre' style='margin-right: 20px; clear:none;'>
                    <a class='close del-pic' href='javascript:;'></a>
                    <a href="<?php echo get_img_url($v);?>" target="_blank"><img src="<?php echo get_img_url($v);?>" style='width:100%;height:100%'/></a>
                    <input type="hidden" name="images" value="<?php echo $v;?>"/>
                </li>
                <?php endforeach;?>
                <?php endif;?>
                <?php endif;?>
                <li class="pic pic-add add-pic" style="float: left;width: 220px;height: 175px;clear:none;">
                    <a href="javascript:;" class="up-img"  id="file_theme_img"><span>+</span><br>添加照片</a>
                </li>
            </ul>
        </li>
        
        <li><label>&nbsp;</label><input name="" type="submit" class="btn" value="确认保存"/></li>
    </ul>
    </form>
</div>
<script src="<?php echo css_js_url('jquery.min.js','admin');?>"></script>
<script src="<?php echo css_js_url('jq.validate.js','admin');?>"></script>
<script src="<?php echo css_js_url('jquery.form.js','admin');?>"></script>
<script type="text/javascript" src="<?php echo css_js_url('jquery.swfupload.js', 'common');?>"></script>
<script type="text/javascript" src="<?php echo css_js_url('swfupload.js', 'admin')?>"></script>
<script type="text/javascript" src="<?php echo css_js_url('admin_upload.js', 'admin');?>"></script>
<script src="<?php echo css_js_url('common.js','admin');?>"></script>
<script type="text/javascript" src="<?php echo css_js_url('dialog.js', 'admin');?>"></script>
<script type="text/javascript">
$("form").submit(function(e){
	 e.preventDefault();
	 var post_arr_data = $("form").serializeArray();
	 $.post("",{arr:post_arr_data},function(data){
			if(data.status == 0){
				showDialog(data.msg, '', '/theme');
			}else{
				showDialog(data.msg);
			}
			
	  });

	 function showDialog(msg, title, url){
		    var title = arguments[1] ? arguments[1] : '提示信息';
		    var url = arguments[2] ? arguments[2] : '';
		    var d = dialog({
		        title: title,
		        content: msg,
		        modal:false,
		        okValue: '确定',
		        ok: function () {
		            if(url != '')
		            {
		                window.location.href=url;
		            }
		            return true;
		        }
		    });
		    d.width(320);
		    d.show();
		}
})
    
</script>
</body>
</html>