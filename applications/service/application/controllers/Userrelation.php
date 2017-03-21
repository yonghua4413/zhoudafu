<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 客户关系
 * @author mochoakai
 */
class Userrelation extends MY_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_user_relation' => 'Muser_relation'
        ));
    }
    /**
     * 增加客户关系
     * @return boolean
     * @author mochaokai
     */
    public function add_user_relation(){
        $uid = $this->input->post('uid');
        $direct_up_uid = $this->input->post('direct_up_uid');
        $time = time();
        if (!$uid || !$direct_up_uid){
            return false;
        }
        $where_user_relation = array(
                        'uid' => $direct_up_uid
        );
        $up_user_info = $this->Muser_relation->get_one(array('layer_path'), $where_user_relation);
        if ($up_user_info){
            $layer_path = $up_user_info['layer_path'] . C('user_relation.user_relation_separator') . $uid;
            $layer_path = trim($layer_path, C('user_relation.user_relation_separator'));
        }else{
            $layer_path = $direct_up_uid . C('user_relation.user_relation_separator') . $uid;
        }
        
        $user_relation = array();
        $user_relation['layer_path'] = $layer_path;
        $user_relation['uid'] = $uid;
        $user_relation['up_uid'] = $direct_up_uid;
        $user_relation['create_time'] = $time;
        $user_relation['update_time'] = $time;
        $result = $this->Muser_relation->create($user_relation);
        if(!empty($result)){
            $this->return_success($result);
        }else{
            $this->return_failed();
        }
    }
    
    /**
     * 获取下级列表
     * @author mochaokai
     */
    public function get_down_list(){
        $user_id = $this->input->post('user_id');
        $order = $this->input->post('order');
        $pagesize = $this->input->post('page');
        $offset = $this->input->post('offset');
        
        $result = $this->Muser_relation->get_lists('*', array('up_uid'=> $user_id), $order, $pagesize, $offset);
        $count = $this->Muser_relation->count(array('up_uid'=> $user_id));
        if(!empty($result)){
            $this->return_success($result,$count);
        }else{
            $this->return_failed();
        }
    }
    
    /**
     * 获取上级信息
     * @author mochaokai
     */
    public function get_up(){
        $user_id = $this->input->post('user_id');
        $result = $this->Muser_relation->get_one('up_uid', array('uid'=> $user_id));
        if(!empty($result)){
            $this->return_success($result);
        }else {
            $this->return_failed();
        }
    }
}