<?php 
/**
* 资讯管理控制器
* @author yonghua@gz-zc.cn
*/
defined('BASEPATH') or exit('No direct script access allowed');
class News extends MY_Controller{

    public function __construct(){
        parent::__construct();
        $this->load->model([
             'Model_news' => 'Mnews',
             'Model_news_class' => 'Mnews_class',
             'Model_admins' => 'Madmins',
             'Model_user' => 'Muser'
        ]);
    }
    

    /**
     * 资讯列表页
     */
    public function index() {
        $data = $this->data;
        $pageconfig = C('page.config_log');
        $this->load->library('pagination');
        $page = (int)$this->input->get_post('per_page') ? : '1';

        $where = array();
        if ($this->input->get('title')) {
            $where['like']['title'] = $this->input->get('title');
            
        }

        if ($this->input->get('class_id')) {
            if ($this->input->get('is_has_child') == 0) {
                $where['news_class_id'] = (int)$this->input->get('class_id');
            } else {
                $where['parent_class_id'] = (int)$this->input->get('class_id');
            }
        }

        if ($this->input->get('is_del')) {
            $where['is_del'] = $this->input->get('is_del');
        } else {
            $where['is_del'] = 0;
        }

        $data['title'] = $this->input->get('title');
        $data['class_id'] = (int)$this->input->get('class_id');
        $data['is_has_child'] = $this->input->get('is_has_child');
        $data['is_del'] = $this->input->get('is_del');

        $data['news_list'] = $this->Mnews->get_lists("*", $where, array("publish_time" => "DESC"), $pageconfig['per_page'], ($page-1)*$pageconfig['per_page']);
        $data_count = $this->Mnews->count($where);
        $data['data_count'] = $data_count;
        $data['page'] = $page;

        //获取分页
        //判断是否带条件查询
        $urls= array();
        $class_id = (int) $this->input->get('class_id', TRUE);
        if(isset($class_id)){
            $urls['class_id'] = $class_id;
        }
        $is_del = (int) $this->input->get('is_del', TRUE);
        if(isset($is_del)){
            $urls['is_del'] = $is_del;
        }
        $is_has_child = (int) $this->input->get('is_has_child');
        if($is_has_child){
            $urls['is_has_child'] = $is_has_child;
        }
        $title = trim($this->input->get('title', TRUE));
        if(isset($title)){
            $urls['title'] = $title;
        }
        $pageconfig['base_url'] = "/news/index?".http_build_query($urls);
        $pageconfig['total_rows'] = $data_count;
        $this->pagination->initialize($pageconfig);
        $data['pagestr'] = $this->pagination->create_links(); // 分页信息

        $data['class_list'] = class_loop_list(class_loop($this->Mnews_class->get_lists("id, name, parent_id", array('is_del' => 0))));
        $data['news_class'] = array_column($data['class_list'], "name", "id");
        $admins = $this->Madmins->get_lists("id,name");
        $data['admins'] = array_column($admins,"name","id");
        $this->load->view("news/index", $data);
    }


    /**
     * 发布资讯
     */
    public function add() {
    	$data = $this->data;
        if (IS_POST) {
            $post_data = $this->input->post();

            //写入文章资讯表t_news
            unset($post_data['file'], $post_data['SEO_title'], $post_data['SEO_keywords'], $post_data['SEO_description'], $post_data['rich_text_img']);
            $post_data['is_show'] = 1;
            $post_data['parent_class_id'] = $this->Mnews_class->get_one('parent_id', array('id' => $post_data['news_class_id']))['parent_id'];
            $post_data['create_user'] = $post_data['update_user'] = $_SESSION['USER']['id'] ? $_SESSION['USER']['id'] : 0;
            $post_data['create_time'] = $post_data['update_time'] = date('Y-m-d H:i:s');
            $post_data['content'] = htmlspecialchars_decode($post_data['content']); //把富文本自动转义的标签反转义后存入
            $post_data['content'] = strip_content_domain_text($post_data['content']);
            $id = $this->Mnews->create($post_data);
            if ($id) {
                //写入关键字表t_keywords
                $keywords_data['object_id'] = $id;
                $keywords_data['title'] = $this->input->post('SEO_title');
                $keywords_data['keywords'] = $this->input->post('SEO_keywords');
                $keywords_data['description'] = $this->input->post('SEO_description');
                $keywords_data['type'] = 1;
                $post_data['create_user'] = $keywords_data['update_user'] = $_SESSION['USER']['id'] ? $_SESSION['USER']['id'] : 0;
                $keywords_data['create_time'] = $keywords_data['update_time'] = date('Y-m-d H:i:s');
                $this->Mkeywords->create($keywords_data);

                $this->success("发布成功","/news");
            } else {
                $this->success("发布失败","/news");
            }
        } else {
            $data['news_class'] = class_loop_list(class_loop($this->Mnews_class->get_lists("id, name, parent_id", array('is_del' => 0))));
            $this->load->view("news/add", $data);
        }
    }



    /* 
     * 编辑资讯
     */
    public function edit($id) {
        $data = $this->data;
        $data['id'] = $id;

        if (IS_POST) {
            $post_data = $this->input->post();

            //保存文章资讯
            unset($post_data['file'], $post_data['SEO_title'], $post_data['SEO_keywords'], $post_data['SEO_description'], $post_data['rich_text_img']);
            $post_data['parent_class_id'] = $this->Mnews_class->get_one('parent_id', array('id' => $post_data['news_class_id']))['parent_id'];
            $post_data['update_user'] = $_SESSION['USER']['id'] ? $_SESSION['USER']['id'] : 0;
            $post_data['update_time'] = date('Y-m-d H:i:s');
            $post_data['content'] = htmlspecialchars_decode($post_data['content']); //把富文本自动转义的标签反转义后存入
            $post_data['content'] = strip_content_domain_text($post_data['content']);
            
            $res = $this->Mnews->update_info($post_data, array("id" => $post_data['id']));
            if ($res) {
                //保存SEO关键字
                $keywords_data['title'] = $this->input->post('SEO_title');
                $keywords_data['keywords'] = $this->input->post('SEO_keywords');
                $keywords_data['description'] = $this->input->post('SEO_description');
                $keywords_data['update_user'] = $_SESSION['USER']['id'] ? $_SESSION['USER']['id'] : 0;
                $keywords_data['update_time'] = date('Y-m-d H:i:s');

                $keywords = $this->Mkeywords->get_lists("*", array("object_id" => $post_data['id']));
                if ($keywords) {
                    $this->Mkeywords->update_info($keywords_data, array("object_id" => $post_data['id']));
                } else {
                    $post_data['create_user'] = $_SESSION['USER']['id'] ? $_SESSION['USER']['id'] : 0;
                    $post_data['create_time'] = date('Y-m-d H:i:s');
                    $keywords_data['object_id'] = $id;
                    $keywords_data['type'] = 1;
                    $this->Mkeywords->create($keywords_data);
                }
                
                $this->success("修改成功","/news");
            } else {
                $this->success("修改失败，请重试！","/news");
            }
        } else {
            $data['info'] = $this->Mnews->get_one("*", array('id' => $id));
            $data['info']['content'] = get_full_content_img_url($data['info']['content']);
            $data['news_class'] = class_loop_list(class_loop($this->Mnews_class->get_lists("id, name, parent_id",array('is_del' => 0))));
            $data['keywords'] = $this->Mkeywords->get_one("*", array('object_id' => $data['info']['id']));
            $this->load->view("news/edit", $data);
        }
    }

    /*
     * 删除和取消删除资讯
     */
    public function del($id, $state) {
        $res = $this->Mnews->update_info(array('is_del' => $state), array('id' => $id));
        if ($res) {
            $this->success("操作成功！", "/news");
        } else {
            $this->success("操作失败！请重试！", "/news");
        }
    }   
}

