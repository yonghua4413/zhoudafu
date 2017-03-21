<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Hotel extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model([
            'Model_hotel' => 'Mhotel',
            'Model_price_plan' => 'Mprice_plan',
            'Model_region' => 'Mregion' 
        ]);
    
    }

    /**
     * 获取酒店详情
     * @author yuanxiaolin@global28.com
     * @param number $id @ruturn json
     */
    public function info($id = 0){

        $hotel_id = $this->input->post('hotel_id');
        if(! empty(intval($hotel_id))){
            $where['id'] = $hotel_id;
            $hotel_detail = $this->Mhotel->get_one('*', $where);
        }
        // 省/市
        $hotel_detail['privinde_name'] = $this->Mregion->getbyid($hotel_detail['privince_id']);
        $hotel_detail['city_name'] = $this->Mregion->getbyid($hotel_detail['city_id']);
        // 图片路径
        $hotel_detail['img_url'] = get_full_img_url($hotel_detail['img_url']);
        
        if($hotel_detail['img_text']){
            $hotel_detail['img_text'] = explode(',', $hotel_detail['img_text']);
            foreach($hotel_detail['img_text'] as $k => $v){
                $hotel_detail['img_text'][$k] = get_full_img_url($v);
            }
        }
        
        // 处理酒店详情编辑器里图片
        $img_url = C('domain.img_new.url') . '/' . C('domain.img_new.img_common_dir') . '/';
        if(! empty($hotel_detail['content'])){
            $hotel_detail['content'] = str_replace('/Uploads/image/', $img_url, $hotel_detail['content']);
        }
        if(! empty($hotel_detail['content2'])){
            $hotel_detail['content2'] = str_replace('/Uploads/image/', $img_url, $hotel_detail['content2']);
        }
        if(! empty($hotel_detail['content3'])){
            $hotel_detail['content3'] = str_replace('/Uploads/image/', $img_url, $hotel_detail['content3']);
        }
        if(! empty($hotel_detail['content4'])){
            $hotel_detail['content4'] = str_replace('/Uploads/image/', $img_url, $hotel_detail['content4']);
        }
        $this->return_success($hotel_detail);
    
    }

    /**
     * 获取酒店列表
     * @author mochaokai
     */
    public function lists(){

        $city_id = $this->input->post('city_id');
        $type = $this->input->post('type');
        $limit = $this->input->post('limit');
        $keywords = $this->input->post('keywords');
        $offset = $this->input->post('offset');
        $iscommend = $this->input->post('iscommend');
        $field = $this->input->post('field') ?  : '*';
        $where = array(
            'is_del' => 1,
            'is_show' => 1 
        );
        if(! empty($type)){
            $where['type'] = $type;
        }
        if(! empty($keywords)){
            $where['like'] = array(
                'title' => $keywords 
            );
        }
        // 热门
        if(! empty($iscommend)){
            $where['is_commend'] = $iscommend;
        }
        // 城市id
        if(! empty($city_id)){
            if(is_array($city_id)){
                $ids = array();
                foreach ($city_id as $k => $v){
                    if(in_array($v, C('old_special_citys'))){
                        $ids[] = $this->Mregion->get_child_ids($v);
                        unset($city_id[$k]);
                    }
                }
                foreach ($ids as $v){
                    $city_id = array_merge($v, $city_id);
                }
                $where['in'] = array(
                    'city_id' => $city_id 
                );
            }
            else{
                // 不是数组进行直辖市判断
                if(in_array($city_id, array(
                    52,
                    343,
                    321,
                    394 
                ))){
                    $where['in'] = array(
                        'city_id' => $this->Mregion->get_child_ids($city_id) 
                    );
                }
                else{
                    $where['city_id'] = $city_id;
                }
            }
        }
        $order = array(
            'sort' => 'desc' 
        );
        if(! empty($offset)){
            $lists = $this->Mhotel->get_lists($field, $where, $order, $limit, $offset);
        }
        else{
            $lists = $this->Mhotel->get_lists($field, $where, $order, $limit);
        }
        foreach($lists as $key => $value){
            if(array_key_exists('img_url', $value)){
                $lists[$key]['img_url'] = get_full_img_url($value['img_url']);
            }
        }
        $count = $this->Mhotel->count($where);
        $this->return_success($lists, $count);
    
    }
}
