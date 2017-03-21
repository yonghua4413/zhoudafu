<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 文章类别
 * @author mochaokai
 */
class Articleclass extends MY_Controller{
    
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_article_class' => 'Marticle_class'
        ));
    }
    
    public function get_class(){
        $parent_id = $this->input->post('parent_id');
        $order = $this->input->post('order');
        $field = $this->input->post('field');
        
        $where = array(
        	'is_del' => 1,
            'parent_id' => $parent_id
        );
        if(empty($field)){
            $field = '*';
        }
        if(!empty($order)){
            $result = $this->Marticle_class->get_lists($field, $where, $order);
            if(!empty($result)){
                $this->return_success($result);
            }else{
                $this->return_failed();
            }
        }else{
            $result = $this->Marticle_class->get_lists($field, $where);
            if(!empty($result)){
                $this->return_success($result);
            }else{
                $this->return_failed();
            }
        }
    }
}