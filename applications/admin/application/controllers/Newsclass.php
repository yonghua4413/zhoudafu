<?php 
    /**
    * 咨询类型控制器
    * @author songchi@gz-zc.cn
    */
defined('BASEPATH') or exit('No direct script access allowed');
class Newsclass extends MY_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model([
               'Model_user' => 'Muser',
               'Model_admins' => 'Madmins',
               'Model_news_class' => 'Mnews_class',
                        
        ]);
        $this->pageconfig = C('page.config_log');
        $this->load->library('pagination');
    }
    
    

    /**
     * 资讯类别列表
     */
    public function index() {
        $data = $this->data;
        $data['list'] = $this->Mnews_class->get_lists('id, parent_id, name, create_time, is_del', ['is_del' => 0], ['sort' => 'asc']);
        //递归处理菜单
        $data['list'] = $this->myloop($data['list']);
        $this->load->view("newsclass/index", $data);
    }
    
    private function myloop($data,$parent=0){
    
        $result = array();
        if($data)
        {
            foreach($data as $key=>$val)
            {
                if($val['parent_id']==$parent)
                {
                    $temp = $this->myloop($data,$val['id']);
                    if($temp) $val['child'] = $temp;
                    $result[] = $val;
                }
            }
    
        }
        return $result;
    }
    /**
     * 添加资讯分类
     */
    public function add() {
        $data = $this->data;
        if (IS_POST) {
            $name = trim($this->input->post("name", TRUE));
            if(empty($name)){
                $this->error("分类名称不能为空！", "/newsclass/add");
                exit();
            }
            $parent_id = (int) (trim($this->input->post("parent_id", TRUE)));
            if($this->check_cate_exists($name, $parent_id)){
                $this->error("分类名称已经存在！", "/newsclass/add");
                exit();
            }
            $post_data = array('name' => $name, 'parent_id' => $parent_id,'is_del' => 0);
            $post_data['create_user'] =  $data['userInfo']['id'];
            $post_data['create_time'] = $post_data['update_time'] = date('Y-m-d H:i:s');
            $id = $this->Mnews_class->create($post_data);
            if ($id) {
                $this->success("添加成功！", "/newsclass");
            } else {
                $this->error("添加失败！请重试", "/newsclass/add");
            }
        } else {
            $data['parent_class'] = $this->Mnews_class->get_lists("id, name", array('parent_id' => 0, 'is_del' => 0));
            $this->load->view("newsclass/add", $data);
        }
    }
    
    /*
     * 修改资讯分类
     */
    public function edit($id = 0) {
        $data = $this->data;
        if (IS_POST) {
            $post_data = $this->input->post();
            $post_data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->Mnews_class->update_info($post_data, array('id' => $post_data['id']));
            if ($res) {
                $this->success("修改成功！", "/newsclass");
            } else {
                $this->success("修改失败！请重试！");
            }
        } else {
            $data['id'] =(int) $id;
            $data['parent_class'] = $this->Mnews_class->get_lists("id, name", array('parent_id' => 0));
            $data['info'] = $this->Mnews_class->get_one("*", array('id' => $id));
            $this->load->view("newsclass/edit", $data);
        }
    }
    
    
    /*
     * 删除资讯分类
     */
    public function del_class($id) {
        $res = $this->Mnews_class->update_info(array('is_del' => 1), array('id' => $id));
        if ($res) {
            $this->success("操作成功！", "/newsclass");
        } else {
            $this->success("操作失败！请重试！");
        }
    }
    
    /**
     * 检测分类否存在
     * @author yonghua@gz-zc.cn
     * @param string $name 分类名称
     * @parent_id 父级分类
     * @return boolean
     */
    public function check_cate_exists($name ='', $id = '')
    {
        $res = $this->Mnews_class->get_one("id", array('name' => $name, 'parent_id' => $id));
        if($res){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    
}

