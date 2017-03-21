<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 邀请码
 * @author mochaokai
 */
class Invitecode extends MY_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_invite_code' => 'Minvite_code'
        ));
    }
    /**
     * 获取积分信息
     * @author mochaokai
     */
    public function get_code_info(){
        $uid = $this->input->post('user_id');
        $code = $this->input->post('code');
        $available = $this->input->post('status');
        if (empty($code) || !$uid){
            $this->return_failed();
            return ;
        }
        if ($code){
            $where['code'] = $code; //按照邀请码查
        }
        if ($uid){
            $where['uid'] = $uid;   //按照传入用户id查
        }
        if ($available){
            $where['status'] = C('invite_code.status.code.available');
        }
        $result = $this->get_one(array('code','uid' ,'success_receive_num'), $where);
        if(!empty($result)){
            $this->return_success($result);
        }else{
            $this->return_failed();
        }
    }
    
    /**
     * 更新数据
     * @author mochaokai
     */
    public function update_info(){
        $data = $this->input->post('data');
        $where = $this->input->post('where');
        
        $result = $this->Minvite_code->update_info($data, $where);
        if(!empty($result)){
            $this->return_success($result);
        }else{
            $this->return_failed();
        }
    }
    
    /**
     * 增加邀请码
     * @author mochaokai
     */
    public function add_code(){
        $data = $this->input->post('data');
        $result = $this->Minvite_code->create($data);
        if($result){
            $this->return_success($result);
        }else{
            $this->return_failed();
        }
    }
}
