<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 图片滚动内容获取
 * @author mochaokai
 *
 */
class Locationcontent extends MY_Controller{
    
    public function __construct(){
        parent::__construct();
        
        $this->load->model(array(
        	'Model_location_content' => 'Mlocation_content'
        ));
    }
    
    /**
     * 获取具体内容
     * @author mochaokai
     */
    public function get_location(){
        
        $l_id = $this->input->post('l_id');
        $limit = $this->input->post('limit');
        
        $where = array('l_id' => $l_id);
        $field = array('id', 'title', 'url', 'img');
        $order = array('sort' => 'asc');
        
        $lists = $this->Mlocation_content->get_lists($field, $where, $order);
        
        if($lists){
            foreach($lists as $k => $v){
                if(empty($v['url'])){
                    $lists[$k]['url'] = 'javascript:viod(0)';
                }
                $lists[$k]['img'] = get_full_img_url($v['img']);
            }
            $this->return_success($lists);
        }else{
            $this->return_failed();
        }
    }
}