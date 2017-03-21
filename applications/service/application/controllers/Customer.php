<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 客户信息
 * @author mochaokai
 */
class Customer extends MY_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_customer' => 'Mcustomer'
        ));
    }
    
    /**
     * 获取客户详细信息
     * @author mochaokai
     */
    public function info(){
        try {
            $user_id = $this->input->post('user_id');
            $mobile = $this->input->post('mobile');
            $field = $this->input->post('field');
            if(empty($mobile) && empty($user_id)){
                throw new Exception('参数为空');
            }
            if(empty($field)){
                $field = '*';
            }
            if(!empty($mobile) && !empty($user_id)){
                $result = $this->Mcustomer->get_one($field,['mobile'=>$mobile, 'or' => array('user_id' => $user_id)]);
            }else if(!empty($user_id) && empty($mobile)){
                $result = $this->Mcustomer->get_one($field,['user_id'=>$user_id]);
            }elseif (!empty($mobile) && empty($user_id)){
                $result = $this->Mcustomer->get_one($field, ['mobile' => $mobile]);
            }
            if(!empty($result)){
                $this->return_success($result);
            }else{
                $this->return_failed();
            }
        }catch (Exception $e){
            $this->return_failed($e->getMessage());
        }
    }
    
    /**
     * 更新客户信息
     * @author mochaokai
     */
    public function update_info(){
        try {
            $data = $this->input->post('data');
            $user_id = $this->input->post('user_id');
            $mobile = $this->input->post('mobile');
            if(empty($user_id) && empty($mobile)){
                throw new Exception('必须包含条件参数');
            }
            if(empty($data)){
                throw new Exception('参数不能为空');
            }
            if(!empty($mobile) && !empty($user_id)){
                $result = $this->Mcustomer->update_info($data,['mobile'=>$mobile, 'or' => array('user_id' => $user_id)]);
            }else if(!empty($mobile) && empty($user_id)){
                $result = $this->Mcustomer->update_info($data,['mobile'=>$mobile]);
            }else if(empty($mobile) && !empty($user_id)){
                $result = $this->Mcustomer->update_info($data,['user_id'=>$user_id]);
            }
            if($result){
                $this->return_success();
            }else{
                $this->return_failed();
            }
        }catch (Exception $e){
            $this->return_failed($e->getMessage());
        }
    }
    
    /**
     * 增加客户信息
     * @author mochaokai
     */
    public function add_customer(){
        try {
            $data = $this->input->post('data');
            if(empty($data) || !is_array($data)){
                throw new Exception('参数错误');
            }
            $result = $this->Mcustomer->create($data);
            if($result){
                $this->return_success($result);
            }else{
                $this->return_failed();
            }
        }catch (Exception $e){
            $this->return_failed($e->getMessage());
        }
    }
}