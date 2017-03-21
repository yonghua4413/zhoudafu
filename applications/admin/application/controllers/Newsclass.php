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
    
        $where = array();
        if ($this->input->get('name')) {
            $where['like']['name'] = $this->input->get('name');
        }
    
        if ($this->input->get('parent_id')) {
            $where['parent_id'] = $this->input->get('parent_id');
        }
    
        if ($this->input->get('is_del')) {
            $where['is_del'] = $this->input->get('is_del');
        } else {
            $where['is_del'] = 0;
        }
    
        $data['name'] = $this->input->get('name');
        $data['parent_id'] = $this->input->get('parent_id');
        $data['is_del'] = $this->input->get('is_del');
    
        $pageconfig = C('page.config_log');
        $this->load->library('pagination');
        //获取当前页页码
        $page = $this->input->get_post('per_page') ? : '1';
        $data['lists'] = $this->Mnews_class->get_lists("*", $where, array('create_time' => 'desc'), $pageconfig['per_page'], ($page-1)*$pageconfig['per_page']);
        $data_count = $this->Mnews_class->count($where);
        $data['data_count'] = $data_count;
        $data['page'] = $page;
    
        //获取分页
        //构造带条件查询的分页
        $urls = array();
        if(isset($data['parent_id'])){
            $urls['parent_id'] = $data['parent_id'];
        }
        if(isset($data['is_del'])){
            $urls['is_del'] = $data['is_del'];
        }
        $name = trim($this->input->get('name', TRUE));
        if(isset($name)){
            $urls['name'] = $name;
        }
        $pageconfig['base_url'] = "/News/class_list?".http_build_query($urls);
        $pageconfig['total_rows'] = $data_count;
        $this->pagination->initialize($pageconfig);
        $data['pagestr'] = $this->pagination->create_links(); // 分页信息
    
        //父级分类
        $data['parent_lists'] = $this->Mnews_class->get_lists("id, name", array('parent_id' => 0, 'is_del' => 0));
        $data['parent_class'] = array_column($data['parent_lists'], 'name', 'id');
        $this->load->view("news/class", $data);
    }
    
    
    /**
     * 添加资讯分类
     */
    public function add_class() {
        $data = $this->data;
    
        if (IS_POST) {
            $name = trim($this->input->post("name", TRUE));
            if(empty($name)){
                $this->error("分类名称不能为空！", "/news/class_list");
                exit();
            }
            $parent_id = (int) (trim($this->input->post("parent_id", TRUE)));
            if($this->check_cate_exists($name, $parent_id)){
                $this->error("分类名称已经存在！", "/news/class_list");
                exit();
            }
            $post_data = array('name' => $name, 'parent_id' => $parent_id,'is_del' => 0);
            $post_data['create_user'] = $post_data['update_user'] = 1;
            $post_data['create_time'] = $post_data['update_time'] = date('Y-m-d H:i:s');
            $id = $this->Mnews_class->create($post_data);
            if ($id) {
                $this->success("添加成功！", "/news/class_list");
            } else {
                $this->error("添加失败！请重试", "/news/class_list");
            }
        } else {
            $data['parent_class'] = $this->Mnews_class->get_lists("id, name", array('parent_id' => 0, 'is_del' => 0));
            $this->load->view("news/add_class", $data);
        }
    }
    
    /*
     * 修改资讯分类
     */
    public function edit_class($id) {
        $data = $this->data;
        if (IS_POST) {
            $post_data = $this->input->post();
            $post_data['update_user'] = $_SESSION['USER']['id'] ? $_SESSION['USER']['id'] : 0;
            $post_data['update_time'] = date('Y-m-d H:i:s');
            $res = $this->Mnews_class->update_info($post_data, array('id' => $post_data['id']));
            if ($res) {
                $this->success("修改成功！", "/news/class_list");
            } else {
                $this->success("修改失败！请重试！", "/news/class_list");
            }
        } else {
            $data['id'] = $id;
            $data['parent_class'] = $this->Mnews_class->get_lists("id, name", array('parent_id' => 0));
            $data['info'] = $this->Mnews_class->get_one("*", array('id' => $id));
            $this->load->view("news/add_class", $data);
        }
    }
    
    
    /*
     * 删除资讯分类
     */
    public function del_class($id, $state) {
        $res = $this->Mnews_class->update_info(array('is_del' => $state), array('id' => $id));
        if ($res) {
            $this->success("操作成功！", "/news/class_list");
        } else {
            $this->success("操作失败！请重试！", "/news/class_list");
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

