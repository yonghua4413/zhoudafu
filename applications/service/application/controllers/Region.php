<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Region extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model(array(
            'Model_region' => 'Mregion' 
        ));
    
    }

    /**
     * 获取热门城市列表
     * @author mochaokai
     */
    public function get_hot_city(){

        $is_hot = $this->input->post('is_hot') ?  : 1;
        $lists = $this->Mregion->get_lists('*', array(
            'is_hot' => $is_hot 
        ));
        if(! empty($lists)){
            $this->return_success($lists);
        }
        else{
            $this->return_failed();
        }
    
    }

    /**
     * 获取海外城市列表
     * @author mochaokai
     */
    public function get_sea_city(){

        $is_hot = $this->input->post('is_hot');
        $where = array(
            'city_type' => 2 
        );
        if(! empty($is_hot)){
            $where['is_hot'] = 1;
        }
        $lists = $this->Mregion->get_lists('*', $where);
        if(! empty($lists)){
            $this->return_success($lists);
        }
        else{
            $this->return_failed();
        }
    
    }

    /**
     * 获取所有城市列表
     * @author mochaokai
     */
    public function get_all_city(){

        $lists = $this->Mregion->get_lists('*');
        $region = [];
        if(! empty($lists)){
            foreach($lists as $k => $v){
                $region[$v['id']] = $v;
            }
            $this->return_success($region);
        }
        else{
            $this->return_failed();
        }
    
    }

    /**
     * 根据城市名获取id
     * @author mochaokai
     */
    public function getbyname(){

        $name = $this->input->post('name');
        $city = [
            52 => '北京',
            343 => '天津',
            321 => '上海',
            394 => '重庆' 
        ];
        if(in_array($name, $city)){
            $city_ids = $this->Mregion->get_child_ids(array_search($name, $city));
        }
        else{
            $city_ids = $this->Mregion->get_one('id', array(
                'name' => $name 
            ));
            if(! empty($city_ids)){
                $city_ids[] = $city_ids['id'];
                array_shift($city_ids);
            }
        }
        $this->return_success($city_ids);
    
    }

    /**
     * ription 获取城市地区信息
     * @param number $city_type 城市类型，国内 1，国外 2
     * @param number $is_hot 是否推荐城市,推荐 1，非推荐 0
     * @param number $page 页码
     * @param number $offset 起始页
     * @param number $type 地区类型,省/市
     * @author mochaokai
     */
    public function get_city(){

        $city_type = (int)$this->input->post('city_type');
        $is_hot = (int)$this->input->post('is_hot');
        $page = (int)$this->input->post('page');
        $offset = (int)$this->input->post('offset');
        $city_id = $this->input->post('city_id');
        $type = (int)$this->input->post('type');
        $field = $this->input->post('field');
        $where = array();
        if(!empty($city_type)){
            $where['city_type'] = $city_type;
        }
        if(!empty($is_hot)){
            $where['is_hot'] = $is_hot;
        }
        if(!empty($type)){
            $where['type'] = $type;
        }
        if(empty($field)){
            $field = '*';
        }
        if(! empty($city_id)){
            if(is_array($city_id)){
                $where['in'] = array(
                    'id' => $city_id 
                );
            }
            else{
                $where['id'] = $city_id;
            }
        }
        if(! empty($offset) && ! empty($page)){
            $result = $this->Mregion->get_lists($field, $where, [
                'is_top' => 'desc',
                'id' => 'desc' 
            ], $page, $offset);
        }
        else{
            $result = $this->Mregion->get_lists($field, $where, [
                'is_top' => 'desc',
                'id' => 'desc' 
            ]);
        }
        if(! empty($result)){
            $this->return_success($result);
        }
        else{
            $this->return_failed();
        }
    
    }
}