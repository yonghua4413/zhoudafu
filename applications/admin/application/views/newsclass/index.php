<?php $this->load->view('common/public_top');?>
            <!-- right main -->
            <div class=row>
            <div class="table-responsive ibox-content">
                <p>
                    <a href="/newsclass/add" class="btn btn-w-m btn-primary">添加分类</a>
                </p>
                <table class="table table-bordered table-hover">
                    <tr>
                        <th>ID</th>
                		<th>分类名称</th>
                		<th>添加时间</th>
                		<th>删除状态</th>
                		<th>操作</th>
        	        </tr>
                    <?php if(isset($list)):?>
                	<?php foreach ($list as $k=>$v):?>
                            <tr <?php if($k%2 !=0 ){ echo 'class="odd"';}?>>
                                <td><?php echo $v['id']?></td>
                                <td><?php echo $v['name']?></td>
                                <td><?php echo $v['create_time']?></td>
                                <td><?php if ($v['is_del']==0):?><span>正常</span>
                        		<?php else:?>
                        		          <span style="color:red;font-weight:bold;">删除</span>
                        		<?php endif;?>
                        		</td>
                                <td><a href="/newsclass/edit/<?php echo $v['id']?>" class="btn btn-outline btn-primary">修改</a> 
                                <?php if(!isset($v['child'])):?>
                                <a href="javascript:;" class="btn btn-outline btn-primary delete"  data-id="<?php echo $v['id']?>"  onClick="delete_data('/newsclass/del_class/<?php echo $v['id']?>')">删除</a>
                                <?php endif;?>
                                </td>
                            </tr>
                            <?php if(isset($v['child'])):?>
                                <?php foreach ($v['child'] as $key => $val):?>
                                    <tr <?php if($key%2 !=0 ){ echo 'class="odd"';}?>>
                                        <td><?php echo $val['id']?></td>
                                        <td><?php echo '&nbsp;&nbsp;&nbsp;┗━━'.$val['name']?></td>
                                        <td><?php echo $val['create_time']?></td>
                                        <td><?php if ($val['is_del']==0):?><span>正常</span>
                                		<?php else:?><span style="color:red;font-weight:bold;">删除</span>
                                		<?php endif;?>
                                		</td>
                                        <td><a href="/newsclass/edit/<?php echo $val['id']?>" class="btn btn-outline btn-primary">修改</a> 
                                        <a href="javascript:;" class="btn btn-outline btn-primary delete"  data-id="<?php echo $val['id']?>"  onClick="delete_data('/newsclass/del_class/<?php echo $val['id']?>')">删除</a>
                                        </td>
                                    </tr>
                                <?php endforeach;?>
                            <?php endif;?>
                          <?php endforeach;?>
                	<?php endif;?> 
                </table>
                <!-- page -->
                <div class="row">
                    <nav style="float: right">
                        <ul class="pagination">
                            <li class="disabled"><a>共<?php echo isset($data_count) ? $data_count: '0';?>条</a></li>
                            <?php echo isset($pagestr) ? $pagestr : ''?>
                        </ul>
                    </nav>
                </div>
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
	    seajs.use(['bootstrap', 'metisMenu', 'slimscroll'], function() {	
        });
        //Custom and plugin javascript
		seajs.use(['<?php echo css_js_url('hplus.js', 'admin');?>', '<?php echo css_js_url('/plugins/pace/pace.min.js', 'admin');?>'], function() {
        });
    </script>
    <script src="<?php echo css_js_url('jquery.min.js', 'common')?>"></script>
    <script src="<?php echo css_js_url('dialog.js', 'common')?>"></script>
    <script>
		var delete_data = function(url){
			var d = dialog({
				title:"提示",
				content:"你确定要删除吗",
				cancelValue:"取消",
				cancel:function(){},
				ok:function(){
					window.location.href=url;
				},
				okValue:"确定"
			})
			d.width(320);
			d.showModal();
			} 

    </script>

</body>

</html>