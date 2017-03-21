<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * 文章获取
 * @author huangjialin
 */
class Articlenew extends MY_Controller{
    
    public function __construct(){
        parent::__construct();
        $this->load->model(array(
        	'Model_article_new' => 'Marticle_new',
            'Model_article_content' => 'Marticle_content',
            'Model_commend_article' => 'Mcommend_article',
        ));
    }
    
    /**
     * 根据文章id获取文章内容
     */
    public function get_info(){
        $id = intval($this->input->post('id'));
        if (!$id){
            $this->return_failed();
        }
        $where = array(
        	'is_publish' => 1,
            'is_del' => 1,
            'id' => $id
        );
        
        $info = $this->Marticle_new->get_one('*', $where);
        if ($info){
            $info['img_url'] = get_full_img_url($info['img_url']);
            $content = $this->Marticle_content->get_content($id);
            $info['content'] = stripslashes(replace_links(get_full_content_img_url($content['content'])));
        }
       
        if(!empty($info)){
            $this->return_success($info);
        }else{
            $this->return_failed();
        }
    }
    
    /**
     * 获取文章列表
     */
    public function get_list(){
        $where = $this->input->post('where');
        $limit = $this->input->post('limit');
        $offset = $this->input->post('offset');
        $order = $this->input->post('order');
        
        $default_where =  array('is_publish' => 1,'is_del' => 1);
        if (! is_null($where)){
            $where = array_merge($default_where, $where);
        }else{
            $where = $default_where;
        }
        
        if (is_null($limit)){
            $limit = 6;
        }
        
        if (is_null($offset)){
            $offset = 0;
        }
        
        $lists = $this->Marticle_new->get_lists('*', $where , $order, $limit, $offset);
        //echo $this->db->last_query();
        $data_count = $this->Marticle_new->count($where);
        
        if(!empty($lists)){
            $this->return_success($lists, $data_count);
        }else{
            $this->return_failed();
        }
        
    }
    
    /**
     * 获取推荐文章列表
     */
    public function get_commend_article_list(){
        $where = $this->input->post('where');
        $limit = $this->input->post('limit');
        $order = $this->input->post('order');
        $default_where =  array('is_publish' => 1,'is_del' => 1);
        if (! is_null($where)){
            $where = array_merge($default_where, $where);
        }else{
            $where = $default_where;
        }
        
        if (! is_null($limit)){
            $limit = 6;
        }
        $lists = $this->Mcommend_article->get_lists('*', $where , $order, $limit, 0);
        
        if(!empty($lists)){
            foreach ($lists as $key => $v){
                $article_info = $this->Marticle_new->get_one('*', array_merge($default_where, array('id' => $v['article_id'])));
                $lists[$key]['title'] = $article_info['title'];
                $lists[$key]['sub_title'] = $article_info['sub_title'];
                
            }
            $this->return_success($lists);
        }else{
            $this->return_failed();
        }
        
    }
    
    /**
     * 获取文章所有内容分页id
     */
    public function get_all_page(){
        $id = $this->input->post('id');
        if (! $id){
            $this->return_failed();
        }
        $info = $this->Marticle_new->get_one('id,parent_id', array('id' => $id));
        if (! $info){
            $this->return_failed();
        }
        $ids = array();
        $where = array( 'is_publish' => 1,'is_del' => 1);
        if (! $info['parent_id']){
            $where['parent_id'] = $id;
            $ids[1] = $id;
        }else{
            $where['parent_id'] = $info['parent_id'];
            $ids[1] = $info['parent_id'];
        }
        $lists = $this->Marticle_new->get_lists('id', $where, array('id' => 'ASC'));
        if ($lists){
            foreach ($lists as $v){
                $ids[] = $v['id'];
            }
        }
        if(! empty($ids)){
            $this->return_success($ids);
        }else{
            $this->return_failed();
        }
    }
    
    
}