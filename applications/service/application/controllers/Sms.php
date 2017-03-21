<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms extends MY_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(array(
		        'Model_user_tel_verify' => 'Muser_tel_verify'
		));
	}
	
	
	/**
	 * 获取手机验证码
	 */
	public function get_mobile_code(){
	    $mobile = (int)$this->input->post('mobile');
	    $sms_config = C('sms.sms_config');
	    $delay_time = 60;
	    $status_arr = array(
	            'yes' => 1, 
	            'no' => 0
	    );
	    $status_msg_arr = array(
	            '验证码已经成功发往您填的手机号, 请等待一会!', 
	            '发送失败, 请一分钟后再试!',
	            '发送间隔不能小于 60 秒!',
	            '电话号码错误！'
	    );
	    
	    $time = time();
	    $return_info = array();
	    
	    if($mobile && preg_match(C('regular_expression.mobile'), $mobile)){
	        $code = get_code();
	        $result = FALSE;
	        $info = $this->Muser_tel_verify->get_verify_by_tel($mobile);
	        if($info){
	            if(($time - $info['add_time']) < $delay_time){
	                $return_info = array('msg'=>$status_msg_arr[2], 'status'=>$status_arr['no']);
	            }else{
	                $result = $this->Muser_tel_verify->update_info(array( 'code'=>$code, 'add_time'=>$time), array('id'=>$info['id']));
	            }
	        }else{
	            $result = $this->Muser_tel_verify->create(array('tel'=>$mobile,'code'=>$code,'add_time'	=>$time));
	        }
	        $return = send_msg($mobile, '验证码为：' . $code . $sms_config['waring']);
	        if($result && $return){
	            $return_info = array('msg'=>$status_msg_arr[0], 'status'=>$status_arr['yes']);
	        }else{
	            $return_info = array('msg'=>$status_msg_arr[1], 'status'=>$status_arr['no']);
	        }
	    }else{
	        $return_info = array('msg'=>$status_msg_arr[3], 'status'=>$status_arr['no']);
	    }
	    
	    $this->return_success($return_info);
	}
	
}
