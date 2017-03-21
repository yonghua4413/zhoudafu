<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Comments extends MY_Controller{

    public function __construct(){

        parent::__construct();
        $this->load->model([
            'Model_customer' => 'Mcustomer',
            'Model_user' => 'Muser',
            'Model_borrow_comment' => 'Mcomment' 
        ]);
    
    }

    /**
     * 查询项目评论列表接口
     * @author yuanxiaolin@global28.com
     * @param $pd 项目ID @page 当前页码
     * @param $size 每页显示条数
     * @ruturn return_type
     */
    public function lists(){

        try
        {
            $pid = $this->input->get_post('pid');
            $page = $this->input->get_post('page') ?  : 1;
            $size = $this->input->get_post('size') ?  : 10;
            $where['is_show'] = 1;
            if(empty($pid))
            {
                throw new Exception('pid required');
            }
            else
            {
                $where['borrow_id'] = $pid;
            }
            
            // 获取评论信息
            $order['create_time'] = 'desc';
            $comments = $this->Mcomment->get_lists('*', $where, $order, $size, ($page - 1) * $size);
            $count = $this->Mcomment->count($where);
            if(! empty($comments))
            {
                $user_ids = array_column($comments, 'user_id');
            }
            
            // 获取用户信息
            if(! empty($user_ids))
            {
                $query['in']['id'] = $user_ids;
                $user_infos = $this->Muser->get_lists('id as user_id,name as user_name,tel as mobile,is_real_name_auth as real_auth,portrait', $query);
            }
            
            if(! empty($comments) && ! empty($user_infos))
            {
                $user_infos = $this->group_tool($user_infos, 'user_id');
                
                foreach($comments as $key => $value)
                {
                    if(! empty($user_infos[$value['user_id']]))
                    {
                        $comments[$key]['user_info'] = $user_infos[$value['user_id']];
                    }
                }
            }
            
            $this->return_success($comments, $count ?  : 0);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }

    /**
     * 获取评论总数接口
     * @author yuanxiaolin@global28.com
     * @throws Exception @ruturn return_type
     */
    public function count(){

        try
        {
            $pid = $this->input->get_post('pid');
            $where['is_show'] = 1;
            if(empty($pid))
            {
                throw new Exception('pid required');
            }
            else
            {
                $where['borrow_id'] = $pid;
            }
            $count = $this->Mcomment->count($where);
            $this->return_success($count);
        }
        catch(Exception $e)
        {
            $this->return_failed($e->getMessage());
        }
    
    }
    
    /**
     * 增加评论
     * @author mochaokai
     */
    public function add(){
        $data = $this->input->post('data');
        $result = $this->Mcomment->create($data);
        if($result){
            $this->return_success($result);
        }else{
            $this->return_failed();
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
                $result[$k] = $value;
            }
        }
        return $result;
    
    }
}
