<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Borrow extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model([
            'Model_borrow' => 'Mborrow',
            'Model_price_plan' => 'Mprice_plan',
            'Model_full_region' => 'Mfull_region' 
        ]);
    
    }

    /**
     * 项目详情
     * @author yuanxiaolin@global28.com
     * @param number $id @ruturn json
     */
    public function info($project_id = 0, $plan_id = 0){

        $project_id = $this->input->get_post('project_id');
        $plan_id = $this->input->get_post('plan_id');
        
        $data = array();
        if(! empty(intval($project_id))){
            $where['id'] = $project_id;
            $borrow = $this->Mborrow->get_one('*', $where);
            
            if(! empty($borrow)){
                $country_info = $this->Mfull_region->get_one('id,name', [
                    'id' => $borrow['country_id'] 
                ]);
                $province_info = $this->Mfull_region->get_one('id,name', [
                    'id' => $borrow['privince_id'] 
                ]);
                
                $borrow['country_name'] = ! empty($country_info['name']) ? $country_info['name'] : '';
                $borrow['province_name'] = ! empty($province_info['name']) ? $province_info['name'] : '';
                if($borrow['city_id']){
                    $city_info = $this->Mfull_region->get_one('id,name', [
                        'id' => $borrow['city_id'] 
                    ]);
                    $borrow['city_name'] = $city_info['name'];
                }
            }
        }
        
        if(! empty(intval($plan_id))){
            $query['id'] = $plan_id;
            $plan = $this->Mprice_plan->get_one('*', $query);
        }
        if(! empty($borrow)){
            $data = $borrow;
        }
        if(! empty($plan)){
            $plan['plan_desc'] = json_decode($plan['plan_desc'], true);
            $data['price_plan'] = $plan;
        }
        
        $this->return_success($data);
    
    }

    /**
     * 项目列表
     * @author mochaokai
     */
    public function lists(){

        try{
            $limit = $this->input->post("limit");
            $begin = $this->input->post("begin_id") ?  : 0;
            $project_type = $this->input->post('project_type');
            $project_status = $this->input->post('project_status');
            $city_id = $this->input->post('city_id');
            $is_commend = $this->input->post('is_commend');
            $where = array(
                "is_del" => 1,
                'is_show' => 1 
            );
            if($project_status){
                $where['project_status'] = $project_status;
            }
            if($project_type){
                $where['project_type'] = $project_type;
            }
            if($city_id){
                if(in_array($city_id, C('special_citys'))){
                    $city_ids = $this->Mfull_region->get_child_ids($city_id);
                    $where ['in'] = array('city_id' => $city_ids);
                }else{
                    $where['city_id'] = $city_id;
                }
            }
            if($is_commend){
                $where['is_commend'] = $is_commend;
            }
            $order = array(
                'is_top' => 'desc',
                'update_time' => 'desc',
                'create_time' => 'desc' 
            );
            
            $borrow_lists = $this->Mborrow->get_lists('*', $where, $order, $limit, $begin);
            foreach($borrow_lists as $k => $v){
                $borrow_lists[$k]['tags'] = explode(' ', $v['tags']);
                $borrow_lists[$k]['img_url'] = get_full_img_url($v['img_url']);
                if ($v['country_id']){
                    $borrow_lists[$k]['country'] = $this->Mfull_region->get_one('name', array('id' => $v['country_id']));
                }
                
                if ($v['privince_id']){
                    $borrow_lists[$k]['privince'] = $this->Mfull_region->get_one('name', array('id' => $v['privince_id']));
                }
                if ($v['city_id']){
                    $borrow_lists[$k]['city'] = $this->Mfull_region->get_one('name', array('id' => $v['city_id']));
                }
            }
            
            $borrow_count = $this->Mborrow->count($where);
            
            $this->return_success($borrow_lists, $borrow_count);
        }
        catch(Exception $e){
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 获取项目中的城市列表
     * @author mochaokai
     */
    public function get_exist_city(){

        $special_citys = C('special_citys');
        $list = $this->Mborrow->get_lists('city_id', array(
            'is_del' => 1,
            'is_show' => 1 
        ), array(), 0, 0, array(
            'city_id' 
        ));
        $city_ids = array_column($list, 'city_id');
        if($list){
            $all_parent = array();
            $all_parent_info = $this->Mfull_region->get_lists(array(
                'id',
                'parent_id' 
            ), array(
                'in' => array(
                    'id' => $city_ids 
                ) 
            ));
            foreach($all_parent_info as $v){
                $all_parent[$v['id']] = $v['parent_id'];
            }
            
            foreach($list as $key => $v){
                if(isset($all_parent[$v['city_id']]) && $all_parent[$v['city_id']] == $special_citys['BJ']){
                    unset($list[$key]);
                    $list = array_merge(array(
                        array(
                            'city_id' => $special_citys['BJ'] 
                        ) 
                    ), $list);
                }
                if(isset($all_parent[$v['city_id']]) && $all_parent[$v['city_id']] == $special_citys['TJ']){
                    unset($list[$key]);
                    $list = array_merge(array(
                        array(
                            'city_id' => $special_citys['TJ'] 
                        ) 
                    ), $list);
                }
                if(isset($all_parent[$v['city_id']]) && $all_parent[$v['city_id']] == $special_citys['SH']){
                    unset($list[$key]);
                    $list = array_merge(array(
                        array(
                            'city_id' => $special_citys['SH'] 
                        ) 
                    ), $list);
                }
                if(isset($all_parent[$v['city_id']]) && $all_parent[$v['city_id']] == $special_citys['CQ']){
                    unset($list[$key]);
                    $list = array_merge(array(
                        array(
                            'city_id' => $special_citys['CQ'] 
                        ) 
                    ), $list);
                }
            }
        }
        $this->return_success($list);
    
    }

    /**
     * 项目信息
     * @author mochaokai
     */
    public function detail(){

        $id = $this->input->post('id');
        $id = intval($id);
        $where = array(
            'is_del' => 1,
            'is_show' => 1,
            'id' => $id 
        );
        $info = $this->Mborrow->get_one('*', $where);
        // 焦点图片
        if($info['img_text']){
            $info['img_text'] = explode(',', $info['img_text']);
            foreach($info['img_text'] as $key => $value){
                $info['img_text'][$key] = get_full_img_url($value);
            }
        }
        // 项目详情中的图片全地址获取
        $info['content'] = get_full_content_img_url($info['content']);
        $info['content2'] = get_full_content_img_url($info['content2']);
        
        // 剩余天数
        $info['diff_days'] = $this->diff_between_two_days($info['project_end_time'], date('Y-m-d H:i:s'));
        $this->return_success($info);
    
    }

    /**
     * 计算剩余天数
     */
    private function diff_between_two_days($day1, $day2){

        $second1 = strtotime($day1);
        $second2 = strtotime($day2);
        if($second1 > $second2){
            return round(($second1 - $second2) / 86400);
        }
        else{
            return 0;
        }
    
    }
}
