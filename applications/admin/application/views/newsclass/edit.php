<?php $this->load->view('common/public_top');?>
            <!-- right main -->
            <div class=row>
             <div class="ibox-content">
                    <form class="form-horizontal" action="/newsclass/edit" method="post">
                        <p>欢迎登录本站(⊙o⊙)</p>
                        
                        <input type="hidden" name="id" value="<?php echo $info['id']?>"/>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">父级分类：</label>
                            <div class="col-sm-3">
                                <select class="form-control" name="parent_id">
                                    <option value='0'>--请选择--</option>
                                    <?php foreach ($parent_class as $k=>$v) :?>
                                        <option <?php if($info['parent_id'] == $v['id']){echo 'selected';}?> value="<?php echo $v['id']?>">--<?php echo $v['name']?>--</option>
                                    <?php endforeach;?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">分类名称：</label>

                            <div class="col-sm-4">
                                <input type="text" placeholder="分类名称" value="<?php echo $info['name']?>" class="form-control" name="name">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="col-sm-3 control-label">排序：</label>

                            <div class="col-sm-4">
                                <input type="text" placeholder="排序" value="<?php echo $info['sort']?>" class="form-control" name="sort">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-8">
                                <button class="btn btn-primary" type="submit">保存</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- right main -->
                <?php $this->load->view('common/footers');?>
            </div>

        </div>
    </div>
    <?php $this->load->view('common/footer')?>
    <script type="text/javascript">
        //Mainly scripts
        var baseUrl = "<?php echo $domain['admin']['url'];?>";
    	var staticUrl = "<?php echo $domain['static']['url']?>";
    	var uploadUrl = "<?php echo $domain['upload']['url']?>";
	    var object = [
                {"obj": "#uploader_img_url", "btn": "#btn_img_url"},
                {"obj": "#uploader_video", "btn": "#btn_video", "type":'video'},
	    ];
// 	    seajs.use(['bootstrap', 'metisMenu', 'slimscroll'], function() {	
//         });
        //Custom and plugin javascript
		seajs.use(['admin_uploader','jqueryswf','swfupload','jqvalidate'], function(swfupload) {
			swfupload.swfupload(object);
        });
    </script>
    

</body>

</html>