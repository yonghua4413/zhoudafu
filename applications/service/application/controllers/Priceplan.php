<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Priceplan extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model([
            'Model_price_plan' => 'Mprice_plan',
//             'Model_order' => 'Morder',
//             'Model_borrow' => 'Mborrow' 
        ]);
    
    }

    /**
     * 获取价格计划
     * @author yuanxiaolin@global28.com
     * @param number $plan_id 价格计划ID
     * @param number $pro_id 项目ID
     *        @ruturn json
     */
    public function info($id = 0){
        $plan_id = $this->input->post('plan_id');
        $pro_id = $this->input->post('pro_id');
        if(! empty($pro_id))
        {
            $query['pro_id'] = $pro_id;
            $query['is_show'] = 1;
            $query['is_del'] = 0;
            $order['plan_order'] = 'desc';
            $data = $this->Mprice_plan->get_lists('*', $query, $order);
        }
        if(! empty(intval($plan_id)))
        {
            $where['id'] = $plan_id;
            $where['is_show'] = 1;
            $query['is_del'] = 0;
            $data = $this->Mprice_plan->get_one('*', $where);
        }
        
        $this->return_success($data);
    
    }

    public function lists(){
        try
        {
            $pro_id = $this->input->post('pro_id');
            $plan_type = $this->input->post('plan_type');
            if(empty($pro_id))
            {
                throw new Exception('pro_id required');
            }
            
            // 查询价格计划
            $pro_ids = explode(',', $pro_id);
            $query['in']['pro_id'] = $pro_ids;
            
            if(! is_null($plan_type))
            {
                $query['plan_type'] = $plan_type;
            }
            
            $query['is_show'] = 1;
            $query['is_del'] = 0;
            $order['plan_order'] = 'desc';
            $data = $this->Mprice_plan->get_lists('*', $query, $order);
            // 查询每个价格计划销售份数，顾客数，销售总额
            if(! empty($data))
            {
                foreach($data as $key => $value)
                {
                    if(! empty($value['plan_desc']))
                    {
                        $data[$key]['plan_desc'] = json_decode($value['plan_desc'], true);
                    }
                }
            }
            $this->return_success($this->group_tool($data, 'pro_id'));
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 获取项目众筹价格计划概况
     * @author yuanxiaolin@global28.com
     * @param $pro_ids pro_id(as:123) or pro_ids(as:1,2,3,4..)
     *        @ruturn return_type
     */
    public function situation(){

        try
        {
            $pro_ids = $this->input->get_post('pro_ids');
            
            if(empty($pro_ids))
            {
                throw new Exception('invalid pro_ids');
            }
            $pro_ids = explode(',', $pro_ids);
            if(! is_array($pro_ids))
            {
                $pro_ids = array(
                    $this->input->post('pro_ids') 
                );
            }
            
            $where['in']['pro_id'] = $pro_ids;
            $where['is_show'] = 1;
            $where['is_del'] = 0;
            $order['plan_order'] = 'desc';
            $data = $this->Mprice_plan->get_lists('*', $where);
            
            $result = array();
            if(! empty($data))
            {
                $pro_group = $this->group_tool($data, 'pro_id');
                foreach($pro_group as $pro_id => $value)
                {
                    $result[$pro_id] = $this->cacal_plan($value);
                }
                $this->return_success($result);
            }else{
                $this->return_failed('null result!');
            }
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 分组工具，将数据组按照指定的key分组
     * @author yuanxiaolin@global28.com
     * @param unknown $data
     * @param string $key
     * @return Ambigous <multitype:, unknown>
     */
    private function group_tool($data = array(), $key = ''){

        $result = array();
        if(! empty($data) && ! empty($key))
        {
            $keys = array_unique(array_column($data, $key));
            foreach($data as $value)
            {
                $k = $value[$key];
                $result[$k][] = $value;
            }
        }
        return $result;
    
    }

    /**
     * 计算筹款指标数据
     * @author yuanxiaolin@global28.com
     * @param unknown $data
     * @return Ambigous <multitype:, multitype:number mixed >
     */
    private function cacal_plan($data = array()){

        $result = array();
        if(is_array($data) && ! empty($data))
        {
            $price_list = array_column($data, 'plan_price');
            $pro_id = array_unique(array_column($data, 'pro_id'))[0];
            asort($price_list);
            
            $total_fund = 0;
            // foreach ($data as $value){
            // $total_fund += $value['plan_limit'] * $value['plan_price']; //筹款总额
            // }
            $project = $this->Mborrow->get_one('price', [
                'id' => $pro_id 
            ]);
            $total_fund = ! empty($project['price']) ? $project['price'] : 0;
            
            $result['total_fund'] = $total_fund;
            $result['min_fund'] = array_shift($price_list); // 起投额
            
            $order_info = $this->get_gathered_fund($pro_id);
            if(! empty($order_info))
            {
                $result = array_merge($result, $order_info);
            }
            $result['fund_rate'] = 0;
            if(! empty($result['exits_fund']) && ! empty($result['total_fund']))
            {
                $result['fund_rate'] = number_format($result['exits_fund'] / $result['total_fund'] * 100, 2);
            }
        }
        return $result;
    
    }

    /**
     * 计算已筹款总额及支持总数等
     * @author yuanxiaolin@global28.com
     * @param number $pro_id
     * @throws Exception
     * @return array
     */
    private function get_gathered_fund($pro_id = 0){

        if(empty($pro_id))
        {
            throw new Exception('not found param pro_id');
        }
        
        $where['product_id'] = $pro_id;
        $where['order_status !='] = C('order.status.canceled.value');
        $where['order_type'] = C('order.type.gather.value');
        $where['pay_status'] = C('order.pay.status.success.value');
        $data = $this->Morder->get_lists('*', $where);
        
        $result['exits_fund'] = 0;
        $result['saled_count'] = 0;
        $result['support_count'] = 0;
        if(! empty($data))
        {
            $result['exits_fund'] = array_sum(array_column($data, 'total_price')); // 已筹款
            $result['saled_count'] = array_sum(array_column($data, 'product_quantity')); // 已销售数量
            $result['support_count'] = count($data); // 购买人数
        }
        return $result;
    
    }
}
