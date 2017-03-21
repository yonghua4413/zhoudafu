<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Callcenter extends MY_Controller {
	public $data = array();
	public function __construct()
	{
		parent::__construct();
		$this->data['domain'] = C('domain');
		$this->load->model(array(
			'Model_customer' => 'Mcustomer',
			'Model_user' => 'Muser',
			'Model_customer_call_list' => 'Mcalllist',
			'Model_location_provinces' => 'Mprovince',
			'Model_location_cities' => 'Mcity',
			'Model_location_areas' => 'Marea',
		));
	}

	/**
	 * 呼叫中心客户信息记录页面
	 * @author yuanxiaolin@global28.com
	 * @param number $id
	 * @ruturn return_type
	 */
	public function customer()
	{
		 $data['call_from'] 	= $call['call_from'] 	= $this->input->get('call_from') ?: '';
		 $data['call_to'] 		= $call['call_to']   	= $this->input->get('call_to') ?: '';
		 $data['staff_no']		= $call['staff_no']  	= $this->input->get('staff_no') ?: '0';
		 $data['call_id'] 		= $call['call_id']		= $this->input->get('call_id') ?: uniqid();
		 $call['start_time']	= $call['end_time']  	= $this->input->server("REQUEST_TIME");
		 $call['created_time'] 	= $call['updated_time'] = $this->input->server("REQUEST_TIME");
		 
		 $data['customer'] 		= $this->Mcustomer->info_customer(['mobile'=>$data['call_from']]);
		 $data['call_list']		= $this->Mcalllist->lists_customer_call(['call_from'=>$data['call_from'],'status'=>C('status.valid.code')]);
		 //$data['userinfo']		= $this->Muser->info_user(['tel'=>$data['call_from']]);
		 
		 
		 $this->Mcalllist->add_customer_call($call);
		 
		 $data = array_merge($data,$this->data);
		 $this->load->view('callcenter/index',$data);
	}
	
	/**
	 * 客户信息保存
	 * @author yuanxiaolin@global28.com
	 * @ruturn json
	 */
	public function customer_save(){
		
		//1.获取客户注册的基本信息
		$mobile = $this->input->post('call_from') ?: '';
		$customer_info	= array();
		if (!empty($mobile)) {
			$user_info 		= $this->Muser->get_one('*',['tel'=>$mobile]);
		}
		
		//2.保存本次通话记录
		$call_params 			= $this->call_history_params();
		$call_where['call_id'] 	= $call_params['call_id']; 
		$history_result 		= $this->Mcalllist->update_info($call_params,$call_where);
		
		//3.保存客户信息
		$post_data 			= $this->post_params($user_info);
		$customer_result 	= $this->Mcustomer->replace_into($post_data);
		
		if (!$history_result || !$customer_result) {
			$this->return_failed('保存失败');
		}
		$this->return_success($customer_result);
	}
	
	/**
	 * 获取省份列表
	 * @author yuanxiaolin@global28.com
	 * @ruturn return_type
	 */
	public function ajax_lists_provinces()
	{
		$result = $this->Mprovince->lists_provinces();
		$this->return_success($result);
	}
	
	
	/**
	 * 获取城市列表
	 * @author yuanxiaolin@global28.com
	 * @param number $province_id 省份ID
	 * @ruturn json
	 */
	public function ajax_lists_cities()
	{
		$province_id = $this->input->get_post('province_id',true);
		$result = $this->Mcity->lists_cities($province_id);
		$this->return_success($result);
	}
	
	/**
	 * 获取地区列表
	 * @author yuanxiaolin@global28.com
	 * @param number $cityid 城市ID
	 * @ruturn return_type
	 */
	public function ajax_lists_areas()
	{
		$city_id = $this->input->get_post('city_id',true);
		$result = $this->Marea->lists_areas($city_id);
		$this->return_success($result);
	}
	
	private function post_params( $user_info = array()){
		
		$province_id 				=	$this->input->post('province_id');
		$city_id 					=	$this->input->post('city_id');
		$area_id 					=	$this->input->post('area_id');
		$data['user_id']			= 	isset($user_info['id']) ? $user_info['id'] : '0';
		$data['user_name'] 			= 	isset($user_info['name']) ? $user_info['name'] : '';
		$data['real_name'] 			= 	$this->input->post('real_name');
		$data['mobile'] 			= 	$this->input->post('mobile');
		$data['gender'] 			= 	$this->input->post('gender') ?: 1;
		$data['type'] 				= 	$this->input->post('type') ?: 1;
		$data['standby_mobile']	 	= 	$this->input->post('standby_mobile');
		$data['standby_tel'] 		= 	$this->input->post('standby_tel');
		$data['merchant'] 			= 	$this->input->post('merchant');
		$data['email'] 				= 	$this->input->post('email');
		$data['qq'] 				= 	$this->input->post('qq');
		$data['weichat'] 			= 	$this->input->post('weichat');
		$data['address_province'] 	= 	$this->input->post('province_name');
		$data['address_city'] 		= 	$this->input->post('city_name');
		$data['address_area'] 		= 	$this->input->post('area_name');
		$data['company'] 			= 	$this->input->post('company');
		$data['address_detail'] 	= 	$this->input->post('address');
		$data['source'] 			= 	$this->input->post('source') ?: 1;
		$data['location'] 			=	implode(',', [$province_id,$city_id,$area_id]);
		
		return $data;
	}
	
	private function call_history_params(){
		
		$data['call_from']	= 	$this->input->post('mobile') ?: '';
		$data['call_to']	= 	$this->input->post('call_to') ?: '';
		$data['call_id']	= 	$this->input->post('call_id') ?: 0;
		$data['staff_no']	= 	$this->input->post('staff_no') ?: '';
		$data['remarks']	= 	$this->input->post('remarks') ?: '';
		$data['status']		=	C('status.valid.code');
		$data['end_time'] 			= $this->input->server("REQUEST_TIME");
		$data['updated_time'] 		= $this->input->server("REQUEST_TIME");
		
		return $data;
	}
}
