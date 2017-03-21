<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 用户工作经历
 * @author huangjialin
 */
class Workhistory extends MY_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_user_work_history' => 'Muser_work_history'
        ));
    }
    
    
    /**
     * 获取用户某一工作经历信息
     */
    public function info(){
        try {
            $user_id = $this->input->post('user_id');
            $id = $this->input->post('id');
            $field = $this->input->post('field');
            if(empty($user_id) || empty($id)){
                throw new Exception('参数为空');
            }
            if(empty($field)){
                $field = '*';
            }
            $result = $this->Muser_work_history->get_one($field, ['user_id'=>$user_id, 'id'=>$id]);
    
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
     * 获取用户工作经历列表
     */
    public function lists(){
        try {
            $user_id = $this->input->post('user_id');
            $field = $this->input->post('field');
            if(empty($user_id)){
                throw new Exception('参数为空');
            }
            if(empty($field)){
                $field = '*';
            }
            $result = $this->Muser_work_history->get_lists($field, ['user_id'=>$user_id]);
            
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
     * 更新用户某一工作经历
     */
    public function update_info(){
        try {
            $data = $this->input->post('data');
            $user_id = $this->input->post('user_id');
            $id = $this->input->post('id');
            if(empty($user_id) && empty($id)){
                throw new Exception('必须包含条件参数');
            }
            if(empty($data)){
                throw new Exception('参数不能为空');
            }
            $result = $this->Muser_work_history->update_info($data,['user_id'=>$user_id,'id'=>$id]);
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
     * 增加用户工作经历
     */
    public function add_info(){
        try {
            $data = $this->input->post('data');
            if(empty($data) || !is_array($data)){
                throw new Exception('参数错误');
            }
            $result = $this->Muser_work_history->create($data);
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