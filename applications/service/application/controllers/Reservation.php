<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Reservation extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model([
            'Model_score' => 'Mscore',
            'Model_customer' => 'Mcustomer',
            'Model_borrow' => 'Mborrow',
            'Model_reservation' => 'Mreservation',
            'Model_region' => 'Mregion' 
        ]);
        
       
    
    }

    /**
     * 查询预约记录
     * @author mochaokai
     */
    public function info(){
        try {
            $user_id = $this->input->post('user_id');
            $pro_id = $this->input->post('pro_id');
            	
            if(!empty($user_id)){
                $where ['user_id'] = $user_id;
            }else{
                throw new Exception('参数user_id必须传');
            }
            if(!empty($pro_id)){
                $where['pro_id'] = $pro_id;
            }
            $reserved = $this->Mreservation->get_one('*', $where);
            if(!empty($reserved)){
                $this->return_success($reserved);
            }else{
                $this->return_failed();
            }
        }catch (Exception $e){
            $this->return_failed($e->getMessage());
        }
    }
    
    /**
     * 保存预约记录
     * @author mochaokai
     */
    public function add(){
        $data = $this->input->post('data');
        if(empty($data) || !is_array($data)){
            $this->return_failed('参数错误或为空');
            return ;
        }
        if(!array_key_exists('user_id', $data) && !array_key_exists('pro_id', $data)){
            $this->return_failed('参数错误');
            return ;
        }
        $insert = $this->Mreservation->create($data);
        if($insert){
            $this->return_success($insert);
        }else{
            $this->return_failed('保存失败');
        }
    }
}
