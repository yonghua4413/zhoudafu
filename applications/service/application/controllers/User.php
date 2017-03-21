<?php
defined('BASEPATH') or exit('No direct script access allowed');
class User extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model([
            'Model_user_extend' => 'Muser_extend',
            'Model_user' => 'Muser',
            'Model_user_tel_verify' => 'Muser_tel_verify',
            'Model_user_class' => 'Muserclass'
        ]);
    
    }

    /**
     * 获取用户信息接口,包括注册信息，用户详情，积分信息
     * @author yuanxiaolin@global28.com
     * @param number $id 
     * @ruturn json
     */
    public function info($id = 0){

        $user_id = $this->input->post('user_id');
        $data['userInfo'] = array();
        if(! empty(intval($user_id)))
        {
            $user_info = $this->Muser->get_one('id as user_id,name as user_name,mobile,is_real_name_auth as real_auth,portrait,user_type', [
                'id' => $user_id 
            ]);
            $user_detail = $this->Muser_extend->get_one('*', [
                'user_id' => $user_id 
            ]);
            
            if(! empty($user_detail))
            {
                $data['userInfo'] = array_merge($user_detail, $user_info);
            }
            else
            {
                $data['userInfo'] = $user_info;
            }
            
        }
        $this->return_success($data);
    
    }
    
    /**
     * 获取用户列表
     * @author mochaokai
     */
    public function lists(){
        $user_ids = $this->input->post('user_ids');
        $field = $this->input->post('field');
        if(empty($field)){
            $field = '*';
        }
        $result = $this->Muser->get_lists($field,array('in' => array('id' => $user_ids)));
        if(!empty($result)){
            $this->return_success($result);
        }else{
            $this->return_failed();
        }

    }
    
    /**
     * 登陆接口
     * @author mochaokai
     * @param $mobile 手机号
     * @param $password 密码
     * 
     */
    public function login(){
        $mobile = $this->input->post('mobile');
        $password = $this->input->post('password');
        $result = $this->Muser->login($mobile, $password);
        if (!$mobile || empty($password)){
            $this->return_failed('', array('msg' => '登录参数错误!', 'code' => 2));
        }
        
        $user_info = $this->Muser->get_one('*', array('mobile' => $mobile));
        if (! $user_info){
            $this->return_failed('', array('msg' => '没有该用户信息!', 'code' => 3));
        }
        
        $encode_pwd = get_encode_pwd($password);
        if ($user_info['password'] != $encode_pwd){
            $this->return_failed('', array('msg' => '密码错误!', 'code' => 4));
        }
        
        if ($user_info['disable'] == C('user.disable.code.yes')){
            $this->return_failed('', array('msg' => '该用户已经被禁止登录!', 'code' => 5));
        }
        
        //更新登录时间
        $this->Muser->update_info(array('updated_time' => time()), array('mobile' => $mobile));
        
        $this->return_success(array('msg' => '登录成功!', 'code' => 1, 'user_data' => $user_info));
    }
    
    /**
     * 注册接口
     * @author mochaokai
     * @param $data
     */
    public function reg(){
        $data = $this->input->post('data');
        if(empty($data['mobile'])){
            $this->return_failed('', array('msg' => '手机号不能为空！', 'code' => 2));
        }
        
        if(!preg_match(C('regular_expression.mobile'), $data['mobile'])){
            $this->return_failed('', array('msg' => '手机号格式不对！', 'code' => 3));
        }
        
        $check_tel = $this->Muser->get_one(array('id','portrait'), array('mobile' => $data['mobile']));
        if($check_tel){
            $this->return_failed('', array('msg' => '手机号已被注册！', 'code' => 4));
        }
        
        if(empty($data['tel_verify'])){
            $this->return_failed('', array('msg' => '手机验证码不能为空！', 'code' => 5));
        }
        
        $check_tel_verify = $this->Muser_tel_verify->get_one(array('code'), array('mobile' => $data['mobile']));
        if(!isset($check_tel_verify['code']) || $check_tel_verify['code'] != $data['tel_verify']){
            $this->return_failed('',array('msg' => '手机验证码错误或者过期！', 'code' => 6));
        }
        
        if(empty($data['password'])){
            $this->return_failed('', array('msg' => '密码不能为空！', 'code' => 7));
        }
      
        $this->return_success(array('msg' => '注册信息验证通过！', 'code' => 1));
    }
    
    /**
     * 增加账号
     * @author mochaokai
     */
    public function add(){
        $data = $this->input->post('data');
        $result = $this->Muser->create($data);
        if($result){
            $this->return_success($result);
        }else{
            $this->return_failed();
        }
    }
    
    /**
     * 更新账号信息
     * @author mochaokai
     */
    public function update(){
        try {
            $data = $this->input->post('data');
            $id = $this->input->post('id');
            if(empty($id) || empty($data) || !is_array($data)){
                throw new Exception('参数错误');
            }
            $result = $this->Muser->update_info($data, ['id' => $id]);
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
     * 获取账号信息
     * @author mochaokai
     */
    public function detail(){
        try {
            $id = (int) $this->input->post('id');
            $mobile = $this->input->post('mobile');
            $disable = $this->input->post('disable');
            $field = $this->input->post('field');
            if(empty($field)){
                $field = '*';
            }
            if(empty($id) && empty($mobile) && empty($disable)){
                throw new Exception('参数错误');
            }
            $where = array();
            if(!empty($id)){
                $where['id'] = $id;
            }
            if(!empty($mobile)){
                $where['mobile'] = $mobile;
            }
            if(!empty($disable)){
                $where['disable'] = $disable;
            }
            $result = $this->Muser->get_one($field, $where);
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
     * interface:获取用户列表
     * @method:get|post
     * @param number $user_type 用户类型
     * @param number $is_recommend 是否推荐
     * @param number $is_real_name_auth 是否实名认证
     * @param number $page     偏移量
     * @param number $size     分页大小
     * @ruturn json
     */
    public function get_lists(){
        try
        {
            $gt_user_type  = (int)$this->input->get_post('gt_user_type');
            
            $user_type  = (int)$this->input->get_post('user_type');
           
            if($user_type)
            {
                $where['user_type'] = $user_type;
            }
            
            
            if ($gt_user_type)
            {
                $where['user_type>'] = $gt_user_type;
            }
            
            $is_recommend  = (int)$this->input->get_post('is_recommend');
            if($is_recommend)
            {
                $where['is_recommend'] = $is_recommend;
            }
            
            $is_real_name_auth  = (int)$this->input->get_post('is_real_name_auth');
            if($is_real_name_auth)
            {
                $where['is_real_name_auth'] = $is_real_name_auth;
            }
            
          
            $page = $this->input->get_post('page')?: 1;
            $size = $this->input->get_post('pagesize')?: 9;
            $order['updated_time'] = 'desc';
            $result = $this->Muser->get_lists('*', $where, $order, $size, ($page-1)*$size);
            
            $count = $this->Muser->count($where);
            $this->return_success($result,$count);
        }
        catch (Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    }
    
    public function classes(){
        try
        {
            $result = $this->Muserclass->get_lists('*');
            $count = $this->Muserclass->count();
            $this->return_success($result,$count);
        }
        catch (Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    }
    
    
    public function user_classes(){
        try
        {
            //前端显示的一级分类id
            $query = ['parent_id' => 0,'is_show' => 1,'is_del' => 0];
            $level0 = $this->Muserclass->get_lists(array('id','parent_id','name','sort'),$query,['sort' => 'asc']);
            $level0 && is_array($level0) && $level0_ids = array_column($level0, 'id');
    
            $where['is_del'] = 0;
            if(!empty($level0_ids))
            {
                $where['in']['parent_id'] = $level0_ids;
            }
    
            $order['sort'] = 'asc';
            $class = $this->Muserclass->get_lists(array('id','parent_id','name','sort'),$where,$order);
    
            //整理归类数据
            if(!empty($level0) && !empty($class))
            {
                foreach ($level0 as $key => $value)
                {
                    foreach ($class as $v)
                    {
                        if($value['id'] == $v['parent_id'])
                        {
                            $level0[$key]['sub_class'][] = $v;
                        }
                    }
                }
            }
    
            $this->return_success($level0,count($level0));
    
        }
        catch (Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    }
    

}
