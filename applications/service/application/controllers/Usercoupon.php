<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 卡券
 * @author mochaokai
 */
class Usercoupon extends MY_Controller{
    
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_user_coupon' => 'Muser_coupon'
        ));
    }
    
    /**
     * @description 获取用户所有卡券
     * @author mochoakai
     */
    public function lists(){
        try {
            $order = $this->input->post('order');
            $page = (int) $this->input->post('page');
            $offset = (int) $this->input->post('offset')?:0;
            $field = $this->input->post('field')?:'*';
            $user_id = $this->input->post('user_id');
            $where = ['user_id'=>$user_id];
            $this->db->from('t_user_coupon');
            $this->db->where($where);
            if(empty($user_id) || empty($page)){
                throw new Exception('参数错误');
            }
            if(!empty($order)){
                foreach ($order as $k => $v){
                    $this->db->order_by($k, $v);
                }
            }
            if(!empty($page)){
                $this->db->limit($page, $offset);
            }
            $this->db->select($field);
            $result = $this->db->get()->result_array();
            $count = $this->Muser_coupon->count($where);
            if(!empty($result)){
                $this->return_success($result, $count);
            }else{
                $this->return_failed();
            }
        }catch (Exception $e){
            $this->return_failed($e->getMessage());
        }
    }
    
    /**
     * 获取优惠券信息
     * @author mochaokai@global28.com
     */
    public function detail(){
        try {
            $user_id = (int)$this->input->post('user_id');
            $coupon_flag = $this->input->post('coupon_flag');
            $field = $this->input->post('field');
            if(empty($user_id)){
                throw new Exception('user_id参数不能为空');
            }
            $where['user_id'] = $user_id;
            if(!empty($coupon_flag)){
                $where['coupon_flag'] = $coupon_flag;
            }
            if(empty($field)){
                $field = '*';
            }
            $result = $this->Muser_coupon->get_one($field, $where);
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
     * 添加优惠券记录
     * @author mochaokai@global28.com
     * 
     */
    public function add(){
        try {
            $data = $this->input->post('data');
            if(!is_array($data)){
                throw new Exception('参数必须为数组');
            }
            $result = $this->Muser_coupon->create($data);
            if($result){
                $this->return_success();
            }else{
                $this->return_failed();
            }
        }catch (Exception $e){
            $this->return_failed($e->getMessage());
        }
    }
}