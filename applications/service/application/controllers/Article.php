<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * 文章获取
 * @author mochaokai
 */
class Article extends MY_Controller{
    
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_article' => 'Marticle'
        ));
    }
    /**
     * 根据文章id获取文章内容
     * @author mochaokai
     */
    public function info(){
        $id = $this->input->post('id');
        $id = intval($id);
        $where = array(
        	'is_show' => 1,
            'is_del' => 1,
            'id' => $id
        );
        $info = $this->Marticle->get_one('*', $where);
        if(!empty($info)){
            $this->return_success($info);
        }else{
            $this->return_failed();
        }
    }
    
    public function info_list(){
        $class_ids = $this->input->post('class_id');
        $order = $this->input->post('order');
        
        $where ['in'] = array('class_id' => $class_ids);
        
        $lists = $this->Marticle->get_lists('*', $where, $order);
        if(!empty($lists)){
            $this->return_success($lists);
        }else{
            $this->return_failed();
        }
    }
}