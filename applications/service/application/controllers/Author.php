<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * 文章作者信息
 * 
 * @author huangjialin
 */
class Author extends MY_Controller{
    
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_author' => 'Mauthor'
        ));
    }
    
    /**
     * 根据作者id获取详细信息
     */
    public function get_info(){
        $id = $this->input->post('id');
        $where = $this->input->post('where');
        $id = intval($id);
        if (! $id){
            $this->return_failed();
        }
        
        $default_where = array(
            'id' => $id
        );
        
        if (! is_null($where)){
            $where = array_merge($default_where, $where);
        }else{
            $where = $default_where;
        }
        
        $info = $this->Mauthor->get_one('*', $where);
        if(!empty($info)){
            $this->return_success($info);
        }else{
            $this->return_failed();
        }
    }
    
    /**
     * 获取作者列表
     */
    public function get_list(){
        $type = $this->input->post('type');
        $order = $this->input->post('order');
        $where = array();
        $where ['is_del'] = 1; //未删除
        if ($type){
            $where ['user_type'] = $type;
        }
        $lists = $this->Mauthor->get_lists('*', $where, $order);
        if(!empty($lists)){
            $this->return_success($lists);
        }else{
            $this->return_failed();
        }
    }
}