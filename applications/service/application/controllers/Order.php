<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Order extends MY_Controller{
    public $data = array();

    public function __construct(){

        parent::__construct();
        $this->load->model(array(
            'Model_customer' => 'Mcustomer',
            'Model_user' => 'Muser',
            'Model_order' => 'Morder',
            'Model_order_hotel' => 'Morder_hotel',
            'Model_order_gather' => 'Morder_gather',
            'Model_score' => 'Mscore',
            'Model_score_op_log' => 'Mscore_op_log' 
        ));
    
    }

    /**
     * 创建订单接口
     * @author yuanxiaolin@global28.com
     * @param s order params
     * @ruturn json
     */
    public function create(){

        try
        {
            
            // 订单已存在暂不考虑
            // $order_exists = $this->check_order_exists($order_data);
            
            // 创建订单流程开启事务
            $this->db->trans_begin();
            
            // 创建订单
            $order_id = 0;
            $order_data = $this->get_order_params();
            $order_id = $this->create_order($order_data);
            
            if(empty($order_id))
            {
                throw new Exception('订单创建失败');
            }
            
            // 如果是酒店预订订单:写入酒店预订订单详情
            if($order_data['order_type'] == C('order.type.hotel.value'))
            {
                $this->create_hotel_order($order_id);
            }
            
            // 如果是项目众筹订单:写入众筹订单详情
            if($order_data['order_type'] == C('order.type.gather.value'))
            {
                $this->create_gather_order($order_id);
            }
            $this->return_failed('订单创建失败');
        }
        catch(Exception $e)
        {
            $this->db->trans_rollback();
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 取消订单接口
     * @param int 订单Id
     * @param int 用户Id
     * @return json 操作结果
     * @author logan@global28.com
     */
    public function cancel(){

        try
        {
            $uid = $this->input->get_post('user_id'); //
            $orderid = (int) $this->input->get_post('order_id'); //
            
            if(empty($uid))
            {
                throw new Exception("无用户id");
            }
            $where['user_id'] = $uid;
            $where['id'] = $orderid;
            
            $rows = $this->Morder->update_info(array(
                "order_status" => 0 
            ), $where);
            
            $this->return_success($rows);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 更新订单信息
     * @author logan
     * @param integer 订单id
     * @param integer 用用户id
     * @return json
     */
    public function update($orderid){

        try
        {
            $data = $this->get_exists_order_params(); // 取消提交参数信息;
            $uid = $data['user_id']; //
            
            if(empty($uid))
            {
                throw new Exception("无用户id");
            }
            $where['user_id'] = $uid;
            $where['id'] = intval($orderid);
            
            $rows = $this->Morder->update_info($data, $where);
            
            $this->return_success($rows);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 更新订单详情接口
     * @author yuanxiaolin@global28.com
     * @ruturn return_type
     */
    public function update_gather_detail(){

        try
        {
            $data = $this->get_gather_detail_params(); // 取消提交参数信息;
            
            if(empty($data['user_id']))
            {
                throw new Exception("无用户id");
            }
            $where['user_id'] = $data['user_id'];
            $where['order_id'] = intval($data['order_id']);
            
            $rows = $this->Morder_gather->update_info($data, $where);
            $this->return_success($rows);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }
    
    // 编辑订单接口
    public function edit(){

    
    }

    /**
     * 订单详情接口
     * @author yuanxiaolin@global28.com
     * @method post
     * @param $order_id | $order_number order_id或order_number必填其一
     * @param $user_id 用户ID可选
     * @throws Exception @ruturn json
     */
    public function info(){

        $order_id = $this->input->post('order_id');
        $order_number = $this->input->post('order_number');
        $user_id = $this->input->post('user_id');
        try
        {
            if(empty($order_id) && empty($order_number))
            {
                throw new Exception("order_id或order_number必填其一");
            }
            if(! empty($user_id))
            {
                $where['user_id'] = $user_id;
            }
            if(! empty($order_id))
            {
                $where['id'] = $order_id;
            }
            if(! empty($order_number))
            {
                $where['order_number'] = $order_number;
            }
            $order = $this->Morder->get_one('*', $where);
            if(! empty($order) && $order['order_type'] == C('order.type.gather.value'))
            {
                $query['user_id'] = $user_id;
                $query['order_id'] = $order_id;
                $order['detail'] = $this->Morder_gather->get_one('*', $query);
            }
            if(! empty($order) && $order['order_type'] == C('order.type.hotel.value'))
            {
                $query['user_id'] = $user_id;
                $query['order_id'] = $order_id;
                $order['detail'] = $this->Morder_hotel->get_one('*', $query);
            }
            $this->return_success($order);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 订单列表接口
     * @author yuanxiaolin@global28.com
     * @param int $product_id 产品ID
     * @param int $user_id 用户ID
     * @param int $page 当前页码
     * @param int $size 每页显示条数
     *        @ruturn json
     */
    public function lists(){

        try
        {
            $pid = $this->input->get_post('product_id');
            $uid = $this->input->get_post('user_id');
            $page = $this->input->get_post('page') ?  : 1;
            $order_type = $this->input->get_post('order_type');
            
            $page_size = $this->input->get_post('size') ?  : 10;
            if(! empty($pid))
            {
                $where['product_id'] = intval($pid);
            }
            if(! empty($uid))
            {
                $where['user_id'] = intval($uid);
            }
            
            $where['pay_status'] = C('order.pay.status.success.value');
            $order_by['created_time'] = 'desc';
            
            $result = $this->Morder->get_lists('*', $where, $order_by, $page_size, ($page - 1) * $page_size);
            $count = $this->Morder->count($where);
            $this->return_success($result, $count);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 统计每个价格计划订单数量接口
     * @author yuanxiaolin@global28.com
     * @param int $plan_id 价格计划ID
     * @param int $pay_status 支付状态
     * @param int $other...扩展其他参数 @ruturn return_type
     */
    public function count_order(){

        $where['order_status !='] = C('order.status.canceled.value');
        if(! empty($this->input->post('plan_id')))
        {
            $where['plan_id'] = $this->input->post('plan_id');
        }
        if(! empty($this->input->post('pay_status')))
        {
            $where['pay_status'] = $this->input->post('pay_status');
        }
        // 往下扩展
        
        $count = $this->Morder->count($where);
        $this->return_success(! empty($count) ? $count : 0);
    
    }

    /**
     * 统计每个价格计划已销售数量接口
     * @author yuanxiaolin@global28.com
     * @param int $plan_id 价格计划ID
     * @param int $pay_status 支付状态
     *        @ruturn return_type
     */
    public function count_quantity(){

        $where['order_status !='] = C('order.status.canceled.value');
        if(! empty($this->input->post('plan_id')))
        {
            $where['plan_id'] = $this->input->post('plan_id');
        }
        if(! empty($this->input->post('pay_status')))
        {
            $where['pay_status'] = $this->input->post('pay_status');
        }
        $count = $this->Morder->get_one("count(*) as order_count,sum(product_quantity) as saled_quantity", $where);
        $this->return_success(! empty($count) ? $count : array());
    
    }

    /**
     * 用户订单列表接口
     * @author logan@global28.com
     * @param int 用户ID
     * @param int 订单类型
     * @param int 当前页码
     * @param int 每页显示条数
     *        @ruturn json
     */
    public function userorderlists(){

        try
        {
            
            $uid = $this->input->get_post('user_id');
            $page = $this->input->get_post('page') ?  : 1;
            $order_type = $this->input->get_post('order_type');
            
            $page_size = $this->input->get_post('size') ?  : 10;
            
            if(empty($uid))
            {
                throw new Exception("无用户Id");
            }
            
            $where['user_id'] = intval($uid);
            if(empty($order_type))
            {
                $order_type = C('order.type.gather.value'); // 缺省为众筹订单
            }
            $where['order_type'] = intval($order_type);
            
            $order_by['created_time'] = 'desc';
            
            $result = $this->Morder->get_lists('*', $where, $order_by, $page_size, ($page - 1) * $page_size);
            $count = $this->Morder->count($where);
            $this->return_success($result, $count);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 获取用户订单数量
     * @author mochaokai@global28.com
     */
    public function user_order_count(){
        try {
            $user_id = $this->input->post('user_id');
            if(empty($user_id)){
                throw new Exception('user_id required');
            }
            $count = $this->Morder->count(['user_id' => $user_id]);
            $this->return_success($count);
        }catch (Exception $e){
            $this->return_failed($e->getMessage());
        }
        
    }
    /**
     * 创建通用订单
     * @author yuanxiaolin@global28.com
     * @param unknown $order_data
     * @throws Exception
     * @return unknown
     */
    private function create_order($order_data = array()){

        if(! empty($order_data))
        {
            $order_id = $this->Morder->create($order_data);
        }
        
        if($order_id)
        {
            if($order_data['order_type'] == C('order.type.hotel.value') && $order_data['cost_score'] > 0)
            {
                $user_id = $order_data['user_id'];
                $cost_score = $order_data['cost_score'];
                $result = $this->deduction_score($user_id, $cost_score, $order_id);
                
                // 积分支付和支付总额相等，更新支付状态未已支付
                if($result && $cost_score == $order_data['total_price'])
                {
                    $update['pay_status'] = C('order.pay.status.success.value');
                    $where['id'] = $order_id;
                    if(! $this->Morder->update_info($update, $where))
                    {
                        throw new Exception('更新订失败');
                    }
                }
            }
            return $order_id;
        }
        else
        {
            throw new Exception('创建订单失败');
        }
    
    }

    /**
     * 创建众筹支付详情订单
     * @author yuanxiaolin@global28.com
     * @param number $order_id
     * @throws Exception @ruturn return_type
     */
    private function create_hotel_order($order_id = 0){

        $order_hotel_data = $this->get_hotel_order_params();
        $order_hotel_data['order_id'] = $order_id;
        $order_hotel_insert_id = $this->Morder_hotel->create($order_hotel_data);
        if($order_hotel_insert_id)
        {
            $order_res['order_id'] = $order_id;
            $order_res['order_detail_id'] = $order_hotel_insert_id;
            $this->db->trans_commit();
            $this->return_success($order_res);
        }
        else
        {
            throw new Exception('创建众筹支付详情订单失败');
        }
        return $order_hotel_insert_id;
    
    }

    /**
     * 创建众筹支付详情订单
     * @author yuanxiaolin@global28.com
     * @param number $order_id
     * @throws Exception @ruturn return_type
     */
    private function create_gather_order($order_id = 0){

        $order_gather_data = $this->get_gather_order_params();
        $order_gather_data['order_id'] = $order_id;
        $order_gather_insert_id = $this->Morder_gather->create($order_gather_data);
        if($order_gather_insert_id)
        {
            $order_res['order_id'] = $order_id;
            $order_res['order_detail_id'] = $order_gather_insert_id;
            $this->db->trans_commit(); // 提交事务
            $this->return_success($order_res);
        }
        else
        {
            throw new Exception('创建众筹支付详情订单失败');
        }
    
    }

    /**
     * 扣减用户积分操作
     * @author yuanxiaolin@global28.com
     * @param number $user_id
     * @param number $cost_score @ruturn return_type
     */
    private function deduction_score($user_id = 0, $cost_score = 0, $order_id = 0){

        $score_type = sprintf('%s,%s', C('score.score_type.code.normal'), C('score.score_type.code.special'));
        $status = C('score.status.code.available');
        $user_score = $this->Mscore->get_score_info($user_id, $score_type, $status);
        
        // 优先扣减可消费不可提现的积分
        if(! empty($user_score['lists']))
        {
            $normal_score = 0;
            $special_score = 0;
            foreach($user_score['lists'] as $key => $value)
            {
                $score = ! empty($value['score']) ? $value['score'] : 0;
                $score_type = ! empty($value['score_type']) ? $value['score_type'] : 0;
                
                // 通用积分，可消费可体现
                if($score_type == C('score.score_type.code.normal'))
                {
                    $normal_score += $score;
                }
                
                // 特殊积分，可消费不可提现
                if($score_type == C('score.score_type.code.special'))
                {
                    $special_score += $score;
                }
            }
            
            // 先扣减可消费不可提现的积分
            $leave_score = $cost_score;
            if($special_score > 0)
            {
                $take_score = $special_score > $cost_score ? $cost_score : $special_score;
                $leave_score = $special_score > $cost_score ? 0 : $cost_score - $special_score;
                $type = C('score.score_type.code.special');
            }
            elseif($normal_score > 0)
            {
                $take_score = $normal_score > $cost_score ? $cost_score : $normal_score;
                $leave_score = $normal_score > $cost_score ? 0 : $cost_score - $normal_score;
                $type = C('score.score_type.code.normal');
            }
            
            // 扣减积分并记录日志
            $result = $this->Mscore->change_score($user_id, $type, $take_score, true);
            
            if($result && $result['code'] == 2)
            {
                $op_msg = "用户{$user_id}预订酒店消费了{$take_score}积分，类型为{$type}";
                $log_arr['op_uid'] = $user_id;
                $log_arr['uid'] = $user_id;
                $log_arr['op_msg'] = $op_msg;
                $this->Mscore_op_log->write_score_log($log_arr);
            }
            else
            {
                throw new Exception('扣减积分异常');
            }
            
            // 再扣减可消费可提现的积分
            if($leave_score > 0)
            {
                $type = C('score.score_type.code.normal');
                $result = $this->Mscore->change_score($user_id, $type, $leave_score, true);
                if($result && $result['code'] == 2)
                {
                    $op_msg = "用户{$user_id}预订酒店消费了{$leave_score}积分，类型为{$type}";
                    $log_info['op_uid'] = $user_id;
                    $log_info['uid'] = $user_id;
                    $log_info['op_msg'] = $op_msg;
                    $this->Mscore_op_log->write_score_log($log_info);
                }
                else
                {
                    throw new Exception('扣减积分异常');
                }
            }
        }
        return $result;
    
    }

    /**
     * 检查创建订单参数
     * @author yuanxiaolin@global28.com
     * @throws Exception
     * @return boolean
     */
    private function check_order_params(){

        $this->load->library('form_validation');
        $this->form_validation->set_rules('user_id', 'user_id', 'trim|htmlspecialchars|required|numeric', [
            'required' => 'user_id不能为空',
            'numeric' => 'user_id不能位非数字' 
        ]);
        $this->form_validation->set_rules('user_name', 'user_name', 'trim|htmlspecialchars|required', [
            'required' => 'user_name不能为空' 
        ]);
        $this->form_validation->set_rules('user_mobile', 'user_mobile', 'trim|htmlspecialchars|required', [
            'required' => 'user_mobile不能为空' 
        ]);
        $this->form_validation->set_rules('product_id', 'product_id', 'trim|htmlspecialchars|required|numeric', [
            'required' => 'product_id不能为空',
            'numeric' => 'product_id不能为非数字' 
        ]);
        $this->form_validation->set_rules('product_name', 'product_name', 'trim|htmlspecialchars|required', [
            'required' => 'product_name不能为空' 
        ]);
        $this->form_validation->set_rules('total_price', 'total_price', 'trim|htmlspecialchars|required', [
            'required' => 'total_price不能为空' 
        ]);
        $this->form_validation->set_rules('order_type', 'order_type', 'trim|htmlspecialchars|required', [
            'required' => 'order_type不能为空' 
        ]);
        $this->form_validation->set_rules('pay_type', 'pay_type', 'trim|htmlspecialchars|required', [
            'required' => 'pay_type不能为空' 
        ]);
        if($this->form_validation->run() == true)
        {
            return true;
        }
        else
        {
            $errors = $this->form_validation->error_array();
            $field_values = array_values($errors);
            throw new Exception($field_values[0]);
        }
    
    }

    /**
     * 检查订单是否存在
     * @author yuanxiaolin@global28.com
     * @param unknown $order_data['user_id','product_id','plan_id']
     * @return boolean | json
     */
    private function check_order_exists($order_data = array()){

        $order = array();
        if(! empty($order_data))
        {
            $where['user_id'] = $order_data['user_id'];
            $where['product_id'] = $order_data['product_id'];
            $where['plan_id'] = $order_data['plan_id'];
            $order = $this->Morder->get_one('*', $where);
        }
        
        if(! empty($order))
        {
            $this->return_failed('订单已经存在', $order, '-2');
        }
        else
        {
            return false;
        }
    
    }

    /**
     * post提交订单字段信息
     * @author logan@global28.com
     * @return array $data
     */
    private function get_exists_order_params(){

        $data = array();
        $postdata = $this->input->post();
        
        ! array_key_exists("order_number", $postdata) or $data['order_number'] = $postdata["order_number"];
        ! array_key_exists("user_id", $postdata) or $data['user_id'] = $postdata["user_id"];
        ! array_key_exists("user_name", $postdata) or $data['user_name'] = $postdata["user_name"];
        ! array_key_exists("user_mobile", $postdata) or $data['user_mobile'] = $postdata["user_mobile"];
        ! array_key_exists("product_id", $postdata) or $data['product_id'] = $postdata["product_id"];
        ! array_key_exists("product_name", $postdata) or $data['product_name'] = $postdata["product_name"];
        ! array_key_exists("product_quantity", $postdata) or $data['product_quantity'] = $postdata["product_quantity"];
        ! array_key_exists("plan_id", $postdata) or $data['plan_id'] = $postdata["plan_id"];
        ! array_key_exists("total_price", $postdata) or $data['total_price'] = $postdata["total_price"];
        ! array_key_exists("order_type", $postdata) or $data['order_type'] = $postdata["order_type"];
        ! array_key_exists("cost_cash", $postdata) or $data['cost_cash'] = $postdata["cost_cash"];
        ! array_key_exists("cost_score", $postdata) or $data['cost_score'] = $postdata["cost_score"];
        ! array_key_exists("trans_cash", $postdata) or $data['trans_cash'] = $postdata["trans_cash"];
        ! array_key_exists("invite_code", $postdata) or $data['order_number'] = $postdata["invite_code"];
        
        ! array_key_exists("created_time", $postdata) or $data['created_time'] = $postdata["created_time"];
        ! array_key_exists("updated_time", $postdata) or $data['updated_time'] = $postdata["updated_time"];
        ! array_key_exists("pay_img", $postdata) or $data['pay_img'] = $postdata["pay_img"];
        ! array_key_exists("order_status", $postdata) or $data['order_status'] = $postdata["order_status"];
        return $data;
    
    }

    /**
     * 众筹详情订单字段映射-用于更新订单详情
     * @author yuanxiaolin@global28.com
     * @return multitype:NULL
     */
    private function get_gather_detail_params(){

        $data = array();
        $data['user_id'] = $this->input->get_post('user_id');
        $data['order_id'] = $this->input->get_post('order_id');
        $data['pay_img'] = $this->input->get_post('pay_img');
        
        return $data;
    
    }

    private function check_hotel_order_params(){

    
    }

    private function check_gather_order_params(){

    
    }

    /**
     * 通用订单字段映射
     * @author yuanxiaolin@global28.com
     * @return array $data
     */
    private function get_order_params(){

        $data['order_number'] = $this->get_order_number();
        $data['user_id'] = $this->input->post('user_id');
        $data['user_name'] = $this->input->post('user_name');
        $data['user_mobile'] = $this->input->post('user_mobile');
        $data['product_id'] = $this->input->post('product_id');
        $data['product_name'] = $this->input->post('product_name');
        $data['product_quantity'] = $this->input->post('product_quantity');
        $data['plan_id'] = $this->input->post('plan_id');
        $data['total_price'] = $this->input->post('total_price');
        $data['order_type'] = $this->input->post('order_type');
        $data['pay_type'] = $this->input->post('pay_type');
        $data['cost_cash'] = $this->input->post('cost_cash') ?  : 0;
        $data['cost_score'] = $this->input->post('cost_score') ?  : 0;
        $data['trans_cash'] = $this->input->post('trans_cash') ?  : 0;
        $data['invite_code'] = $this->input->post('invite_code') ?  : '';
        $data['created_time'] = $this->input->post('created_time');
        $data['updated_time'] = $this->input->post('updated_time');
        $data['order_status'] = C('order.status.checking.value');
        return $data;
    
    }

    /**
     * 酒店预订订单字段映射
     * @author yuanxiaolin@global28.com
     * @return array $data
     */
    private function get_hotel_order_params(){

        $data['user_id'] = $this->input->post('user_id');
        $data['product_id'] = $this->input->post('product_id');
        $data['product_name'] = $this->input->post('product_name');
        $data['total_price'] = $this->input->post('total_price');
        $data['plan_id'] = $this->input->post('plan_id');
        $data['in_time'] = $this->input->post('in_time');
        $data['out_time'] = $this->input->post('out_time');
        $data['user_name'] = $this->input->post('user_name');
        $data['user_mobile'] = $this->input->post('user_mobile');
        $data['user_email'] = $this->input->post('user_email');
        $data['house_num'] = $this->input->post('product_quantity');
        $data['created_time'] = $this->input->post('created_time');
        $data['updated_time'] = $this->input->post('updated_time');
        return $data;
    
    }

    /**
     * 众筹订单详情字段映射
     * @author yuanxiaolin@global28.com
     * @return array $data
     */
    private function get_gather_order_params(){

        $data['user_id'] = $this->input->post('user_id');
        $data['product_id'] = $this->input->post('product_id');
        $data['product_name'] = $this->input->post('product_name');
        $data['total_price'] = $this->input->post('total_price');
        $data['plan_id'] = $this->input->post('plan_id');
        $data['user_name'] = $this->input->post('user_name');
        $data['user_mobile'] = $this->input->post('user_mobile');
        $data['user_address'] = $this->input->post('user_address');
        $data['created_time'] = $this->input->post('created_time');
        $data['updated_time'] = $this->input->post('updated_time');
        return $data;
    
    }

    /**
     * 获取唯一订单号
     * @author yuanxiaolin@global28.com
     *         @ruturn return_type
     */
    private function get_order_number(){

        $ip_addrss = $this->input->ip_address();
        $ip_addrss = str_replace('.', '', $ip_addrss);
        return sprintf('%s-%s-%s', date('YmdHis'), $ip_addrss, rand(1000, 9999));
    
    }
}
