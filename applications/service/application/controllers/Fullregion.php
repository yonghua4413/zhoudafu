<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * 完整地区列表
 * @author mochaokai
 */
class Fullregion extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model(array(
            'Model_full_region' => 'Mfull_region' 
        ));
    
    }

    public function get_list(){
        // 城市地区列表
        $id = $this->input->post('id');
        $regions = $this->Mfull_region->get_lists('id,name', [
            'in' => array(
                'id' => $id 
            ) 
        ]);
        $region = array();
        
        foreach($regions as $key => $value){
            $region[$value['id']] = $value['name'];
        }
        $this->return_success($region);
    
    }

    /**
     * ription 根据id或name获取记录
     * @author mochaokai
     */
    public function get_by(){

        try{
            $id = $this->input->post('id');
            $name = $this->input->post('name');
            $field = $this->input->post('field');
            $page = (int)$this->input->post('page');
            $offset = (int)$this->input->post('offset')?:0;
            if(empty($field)){
                $field = '*';
            }
            if(empty($id) && empty($name)){
                throw new Exception('参数错误');
            }
            if(! empty($id)){
                if(is_array($id)){
                    if(!empty($page)){
                        $result = $this->Mfull_region->get_lists($field, array('in' => ['id' => $id]), ['id' => 'desc'], $page, $offset );
                    }else{
                        $result = $this->Mfull_region->get_lists($field, array('in' => ['id' => $id]), ['id' => 'desc']);
                    }
                }else{
                    $result = $this->Mfull_region->get_one($field, array(
                        'id' => $id 
                    ));
                }
            }
            else{
                $result = $this->Mfull_region->get_one('*', array(
                    'name' => $name 
                ));
            }
            $this->return_success($result);
        }
        catch(Exception $e){
            $this->return_failed($e->getMessage());
        }
    
    }
}