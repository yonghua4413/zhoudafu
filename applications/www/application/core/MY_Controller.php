<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller {

    public $data = array();
    public function __construct() {
        parent::__construct();

        $_GET = xss_clean($_GET);
        $_POST = xss_clean($_POST);

        $this->data['domain'] = C('domain');
        $this->data['c_modle'] = $this->uri->segment(1);

        //SEO信息
        $this->get_seo_info();
        
        //网站全局配置 (包括400电话等)
        $this->data['site_config'] =  C('site_config');
        $this->data['icon'] = 'icon.ico';
        
//        $user_cookie = $this->input->cookie('user');
//         if($user_cookie){
//             $this->data['userInfo'] = array();
//             $this->get_user_info($user_cookie);
//         }else{
//             $act = $this->uri->segment(2) ? strtolower($this->uri->segment(2)) : '';
//             $ctrl = strtolower($this->uri->segment(1));
            
//             if (!in_array($ctrl, C('nologin.page'))) {
                
//                 //登录前访问的地址，登陆后跳转至此地址
//                 setcookie("return_url", 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'], time() + C('site_config.cookie_expire'), '/', C('site_config.root_domain'));
                
//                 header('location:' . C('domain.base.url') . '/passport/redirect_wechat_login');
//                 exit;
//             }
//         }

    }

    /**
     * 转化为json字符串
     * @author yuanxiaolin@global28.com
     * @param unknown $arr
     * @ruturn return_type
     */
    public function return_json($arr) {
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: X-Requested-With');
        header('Content-Type: application/json');
        header('Cache-Control: no-cache');
        echo json_encode($arr);exit;
    }

     /**
      * 请求成功返回
      * @author yuanxiaolin@global28.com
      * @param unknown $data
      * @param string $msg
      * @ruturn return_type
      */
    public function return_success($data = array(),$msg = 'request is ok') {

        $this->return_json(
                array(
                    'status'=> C('status.success.value'),
                    'data'    => $data,
                    'msg'   => $msg,
                )
        );

    }

    /**
     * 请求失败返回
     * @author yuanxiaolin@global28.com
     * @param string $result
     * @param string $success_msg
     * @param string $failure_msg
     * @ruturn return_type
     */
    public function return_failed ( $msg = 'request failed',$data = '',$status = -1) {

        $this->return_json(
            array(
                'status'    => isset($status) ? $status : C('status.failed.value'),
                'msg'       => $msg,
                'data'        => $data
            )
        );
    }

    /**
     * 通用的HTTP请求工具
     * @author yuanxiaolin@global28.com
     * @param string $path 接口请求path
     * @param unknown $data get|post请求数据
     * @param string $debug 接口的debug模式， 为true将会把数据原包返回
     * @param string $method 请求方式，默认POST
     * @param unknown $cookie 接口请求的cookie信息，用于需要登陆验证的接口
     * @param unknown $multi 文件信息
     * @param unknown $headers 附加的头文件信息
     * @ruturn return_type 返回string 或者 array
     */
    public function http_request($path = '',$data = array(),$debug=false, $method ='POST',$cookie = array(),$multi = array(),$headers = array()){
        $this->benchmark->mark('start');//start clock....
        
        $api_url = $this->create_url($path);

        $response = $this->get_response($api_url,$data,$method,$cookie,$multi,$headers);
        
        if ($debug === true) {
            return $response;
        }else{
            $response = json_decode($response,true);
        }
        
        $this->benchmark->mark('end');//end clock....
        
        $this->log_message($api_url,$response);
        
        return $response;
   }

    /**
     * 创建接口请求URL
     * @author yuanxiaolin@global28.com
     * @param string $path
     * @return string
     */
    public function create_url($path = ''){
        return sprintf('%s/%s',$this->data['domain']['service']['url'],$path);
    }


    /**
     * 登录状态某些页面跳转到个人中心
     */
    public function login_redirect(){
        if(isset($this->data['userInfo']))
        {
            redirect($this->data['domain']['base']['url'] .  C('user_center.url.user_center'));
            exit;
        }
    }

    protected function get_seo_info(){
        //SEO信息
        $this->load->model(array(
                'Model_configes'   => 'Mconfiges'
        ));
        $web_info = $this->Mconfiges->get_lists('*');
        $seo_info = array();
        if ($web_info){
            foreach ($web_info as $key => $v){
                if ($v['key'] == 'seo_title'){
                    $seo_info['seo_title']   = $v['val'];
                }

                if ($v['key'] == 'web_name'){
                    $seo_info['web_name']   = $v['val'];
                }

                if ($v['key'] == 'web_sign'){
                    $seo_info['web_sign']   = $v['val'];
                }
                if ($v['key'] == 'seo_keywords'){
                    $seo_info['seo_keywords']   = $v['val'];
                }
                if ($v['key'] == 'seo_description'){
                    $seo_info['seo_description']   = $v['val'];
                }
                if($v['key'] == 'company_tel'){
                    $seo_info['company_tel'] = $v['val'];
                }
            }
        }
        $this->data['seo'] = array(
                'title' => $seo_info['web_name'] .'-'. $seo_info['seo_title'],
                'keywords' => $seo_info['seo_keywords'],
                'description' => $seo_info['seo_description'],
                'company_tel' => $seo_info['company_tel'],
        );
    }


    protected function get_user_info($user_cookie){
        $simple_info= decrypt($user_cookie);
        if(!empty($simple_info)){
            $user_info['user_id'] = $simple_info['id'];
            $user_info['tel'] = $simple_info['tel'];
            $user_info['person_type'] = $simple_info['person_type'];
            $this->data['userInfo'] = $user_info;
        }
        
    }

    /**
     * 创建并设置访问token
     * @author yuanxiaolin@global28.com
     * @ruturn return_type
     */
    public  function set_token(){
        session_start();
        $this->data['token'] = md5(time());
        $_SESSION['user_token'] = $this->data['token'];
    }

    /**
     * 检查是否是有效token
     * @author yuanxiaolin@global28.com
     * @param string $token
     * @throws Exception
     * @ruturn return_type
     */
    public function check_token($token = ''){
        session_start();
        if($token != $_SESSION['user_token']){
            return false;
        }
        return true;
    }

    /**
     * 销毁访问token
     * @author yuanxiaolin@global28.com
     * @ruturn return_type
     */
    public function unset_token(){
        session_start();
        if(!empty($_SESSION['user_token'])){
            unset($_SESSION['user_token']);
        }
    }

    /**
     * 短信验证码验证
     * @author chaokai@gz-zc.cn
     * @param string $mobile
     * @param string $code
     */
    public function check_code($mobile, $code){
        $verify = $this->Muser_tel_verify->get_one('*', array(
                        'mobile' => $mobile
        ));
        !$verify && $this->return_failed('验证码错误');
        ($verify['code'] != $code) && $this->return_failed('验证码错误');
        $time_interval = time() - $verify['add_time'];
        ($time_interval > C('sms.sms_config_huaxing.nvalidation_time')) && $this->return_failed('验证码过期，请重新获取');
    }
    
    /**
     * 接口日志记录（此方法只限于接口监控使用）
     * @author yuanxiaolin@global28.com
     * @param unknown $data
     * @ruturn return_type
     */
    private function log_message($url = '', $data = array()){

        //日志初始化参数
        $params = array(
            'path'=>C('log.api.path'),
            'level'=>C('log.api.level')
        );

        //日志开关
        if(C('log.api.enable') === false){
            return ;
        }

        //加载日志工具
        $this->load->library('Logfile',$params);

        //接口时差，单位为毫秒
        $cost_time = $this->benchmark->elapsed_time('start','end') * 1000;

        if(isset($data['status']))
        {
            if($data['status'] == C('status.success.value'))
            {
                //返回成功，记录info日志
                $return_data = 'success';
                $message = sprintf('%s | %s | %s | %s',$data['status'],$cost_time,$url,$return_data);
                $this->logfile->info($message);
            }
            else
            {
                //返回错误，记录error日志
                $return_data = json_encode($data);
                $message = sprintf('%s | %s | %s | %s',$data['status'],$cost_time,$url,$return_data);
                $this->logfile->error($message);
            }
        }
        else
        {
            //格式错误，或者http请求未到达，记录error日志
            $return_data = 'http request error';
            $message = sprintf('%s | %s | %s | %s',$data['status'],$cost_time,$url,$return_data);
            $this->logfile->error($message);
        }
    }
    
    /**
     * 从接口获取数据，根据情况判断从数据库获取还是从memcache获取
     * @param string $path 接口请求path
     * @param unknown $data get|post请求数据
     * @param string $debug 接口的debug模式， 为true将会把数据原包返回
     * @param string $method 请求方式，默认POST
     * @param unknown $cookie 接口请求的cookie信息，用于需要登陆验证的接口
     * @param unknown $multi 文件信息
     * @param unknown $headers 附加的头文件信息
     * @author mochaokai@global28.com
     */
    private function get_response($api_url,$data,$method,$cookie,$multi,$headers){
        $url = $this->get_url($api_url).json_encode($data);
        //判断客户端是否支持memcached和memcached开关是否打开，memcached开关在memcached配置文件中
        if(class_exists('memcached') && C('mymemcached.switch')){
            $this->load->library('Mymemcache');
            $response = Mymemcache::get($url);
            if(!$response){
                $response = Http::Request($api_url,$data,$method,$cookie,$multi,$headers);
                Mymemcache::set($url, $response, C('mymemcached.time'));
                $all_key_arr = Mymemcache::get(C('mymemcached.all_keys'));
                if(!$all_key_arr){
                    $all_key_arr = [];
                }
                if(!in_array($url, $all_key_arr)){
                    $all_key_arr[] = $url;
                    Mymemcache::set(C('mymemcached.all_keys'), $all_key_arr, C('mymemcached.time'));
                }
                return $response;
            }else{
                return $response;
            }
        }else{
            return Http::Request($api_url,$data,$method,$cookie,$multi,$headers);
        }
    }
    
    /**
     * 对memcache的键进行规范化处理
     * @author mochaokai@global28.com
     * @param string $url
     * @return string $str
     */
    private function get_url($url){
        $str = 'http://';
        foreach (explode('/', $url) as $k => $v){
            if(!empty($v) && $k > 0){
                $str .= $v.'/';
            }
        }
        return $str;
    }
    
    /**
     * 订单处理，更改订单状态，乘车时间超过一天的订单设置为已完成，用户每次查看订单时都调用该函数；
     * 记录上一次更改订单状态的时间到缓存，本次调用该方法时判断当前时间与缓存中的时间，超过一天执行更改订单状态，否则不执行
     * @author chaokai@gz-zc.cn
     */
    public function change_order_status(){
        $this->load->driver('cache');
        $change_order_time = $this->cache->file->get('change_order_time');
        if(!$change_order_time){
            $this->cache->file->save('change_order_time', time(), 60*60*24*2);
        }
        $now_time = time();
        if(!$change_order_time || $now_time - $change_order_time > 60*60*24){
            $this->cache->file->increment('change_order_time', 60*60*24);
            $this->load->model(array('Model_order' => 'Morder'));
            $order_list = $this->Morder->get_lists('*', array('in' => array('order_status' => array(C('order.status.code.sending'), C('order.status.code.to_send')))));
            $sending_list = array();
            $to_send_list = array();
            if($order_list){
                foreach ($order_list as $k => $v){
                    $date_diff = date_diff(date_create($v['ride_time']), date_create(date('Y-m-d H:i:s')));
                    $diff_days = $date_diff->format('%a');
                    if($v['order_status'] == C('order.status.code.sending') && $diff_days >= 1){
                        $sending_list[] = $v['id'];
                    }else if($v['order_status'] == C('order.status.code.to_send') && $diff_days >= 1){
                        $to_send_list[] = $v['id'];
                    }
                }
            }
            //派车中的超时订单设置为取消
            if(!empty($sending_list)){
                $this->Morder->update_info(array('order_status' => C('order.status.code.cancel')), array('in' => array('id' => $sending_list)));
            }
            //待接送订单设置为已完成
            if(!empty($to_send_list)){
                $this->Morder->update_info(array('order_status' => C('order.status.code.done')), array('in' => array('id' => $to_send_list)));
            }
        }
    }
}













