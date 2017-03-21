<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Score extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model([
            'Model_score' => 'Mscore',
            'Model_score_op_log' => 'Mscore_op_log' 
        ]);
    
    }

    /**
     * 获取用户积分信息
     * @param int user_id 用户ID
     * @param string score_type 积分类型，支持多个积分类型如：1,2,3 逗号隔开
     * @param int status 积分状态：1，可用，0不可用
     */
    public function score_info(){

        try
        {
            $user_id = $this->input->post('user_id');
            $score_type = $this->input->post('score_type');
            $status = $this->input->post('status');
            
            if(! $user_id)
            {
                throw new Exception("参数user_id必须传");
            }
            $score_info = $this->Mscore->get_score_info($user_id, $score_type, $status);
            
            $this->return_success($score_info);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }
    
    /**
     * 获取用户积分列表
     * @author mochaokai@global28.com
     * @throws Exception
     */
    public function score_list(){
        try {
            $user_id = $this->input->post('user_id');
            if(empty($user_id)){
                throw new Exception('user_id参数不能为空');
            }
            $field = $this->input->post('field');
            if(empty($field)){
                $field = '*';
            }
            $where = ['uid' => $user_id];
            $result = $this->Mscore->get_lists($field,$where);
            $this->return_success($result);
        }catch (Exception $e){
            $this->return_failed($e->getMessage());
        }
    }
    /**
     * 变更用户积分(添加和修改)
     */
    public function change_score_info(){

        try
        {
            $user_id = $this->input->post('user_id');
            $score_type = $this->input->post('score_type');
            $score = $this->input->post('score');
            $is_decr = $this->input->post('is_decr');
            
            $admin_id = $this->input->post('admin_id');
            if(! $user_id || ! $score_type || ! $score || ! is_null($is_decr))
            {
                throw new Exception("缺少必要参数");
            }
            
            $return_info = $this->Mscore->change_score($user_id, $score_type, $score, $is_decr);
            
            // 记录操作日志
            $op = $is_decr ? '增加' : '减掉';
            if($admin_id)
            {
                $op_msg = "ID为 【{$admin_id}】后台用户给ID为【{$user_id}】前台用户{$op}了{$score}积分";
                $log_arr = array(
                    'uid' => $admin_id,
                    'op_uid' => $user_id,
                    'op_msg' => $op_msg 
                );
            }
            else
            {
                $op_msg = "用户ID为【{$user_id}】前台用户{$op}了{$score}积分";
                $log_arr = array(
                    'op_uid' => $user_id,
                    'op_msg' => $op_msg 
                );
            }
            $this->Mscore_op_log->write_score_log($log_arr);
            
            $this->return_success($return_info);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }
}
