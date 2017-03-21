<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 首页
 * @author yonghua@gz-zc.cn
 */
class Home extends MY_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
            
        ));
    }
    
    /**
     * 首页；乘客首页
     * @author chaokai@gz-zc.cn
     */
    public function index(){
        $data = $this->data;
        $this->load->view('home/index', $data);
    }
    
    
    
}