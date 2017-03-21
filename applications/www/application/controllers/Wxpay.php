<?php
/**
 * 微信支付
 * @author chaokai@gz-zc.cn
 *
 */
class Wxpay extends MY_Controller{
    
    public function __construct(){
        parent::__construct();
        
        $this->load->model(array(
                        'Model_order_pay' => 'Morder_pay'
        ));
        $this->load->library('weixinhelper');
        //加载相关类库
        $weixin_config = array(
                        'app_id' => C('wechat_app.app_id.value'),
                        'app_secret' => C('wechat_app.app_secret.value'),
                        'key' => C('wechat_app.key.value'),
                        'mch_id' => C('wechat_app.mch_id.value')
        );
        $this->load->library('weixinpay', $weixin_config);
    }
    
    /**
     * 支付结果异步通知
     * @author cahokai@gz-zc.cn
     */
    public function notify(){
    
        $response = $this->weixinpay->verify_notify();
        $return_data = array('return_code' => 'SUCCESS', 'return_msg' => 'OK');
        if($response['error'] == 1){
            $return_data['return_code'] = 'FAIL';
            $return_data['return_msg'] = '签名失败';
            echo Weixinhelper::array_to_xml($return_data);
            exit;
        }
    
        $response_data = $response['data'];
        //更新订单支付状态
        $out_trade_id = $response_data['out_trade_no'];
        //查询订单状态是否已更改
        $is_change = $this->Morder_pay->get_one('pay_status', array('order_num' => $out_trade_id));
        if(!$is_change || $is_change['pay_status']){//订单不存在或订单状态已更改返回操作成功通知
            die(Weixinhelper::array_to_xml($return_data));
        }
    
        if($response['error'] == 2){
            //订单支付失败
            $update_data = array('pay_status' => 2, 'error_desc' => $response_data['return_msg']);
        }elseif($response['error'] == 3){
            $update_data = array('pay_status' => 2, 'error_desc' => $response_data['err_code_des']);
        }else{
            $update_data = array('pay_status' => 1);
        }
        $update_data['pay_time'] = date('Y-m-d H:i:s');
        $this->Morder_pay->update_info($update_data, array('order_num' => $out_trade_id));
    
        //返回结果
        echo Weixinhelper::array_to_xml($return_data);
    }
    
    /**
     * 查询订单状态
     * @author cahokai@gz-zc.cn
     */
    public function check_order(){
        $order_id = $this->input->get('order_id');
        !$order_id && $this->return_failed('参数错误');
    
        $order_info = $this->Morder_pay->get_one('*', array('id' => $order_id));
        !$order_info && $this->return_failed('订单不存在');
    
        $this->return_success(array('status' => $order_info['status'], 'error_desc' => $order_info['error_desc']));
    }
}