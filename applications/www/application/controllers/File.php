<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class File extends MY_Controller {
	public function __construct(){
        parent::__construct();
	}
    public function upload(){
        $return_msg = array();
        $config = array(
    		'upload_path'   => '../../Uploads/files/',
    		'allowed_types' => '*',
    		'max_size'     => 1024*5,
    		'encrypt_name' => TRUE,
    		'remove_spaces'=> TRUE,
        );
        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload('fileData')){
            !$this->upload->is_image() && $this->return_failed('图片类型出错');
            !$this->upload->is_allowed_filetype() && $this->return_failed('图片类型不被允许');
            !$this->upload->is_allowed_filesize() && $this->return_failed('图片大小超过限制');
            !$this->upload->is_allowed_dimensions() && $this->return_failed('图片宽高超过限制');
            $this->return_failed($this->upload->display_errors());
        }else{
            $data = $this->upload->data();
            unset($data['file_path'], $data['full_path']);
            $data['url'] = $this->data['domain']['img']['url'].'/files/'.$data['file_name'];
            $data['disk_url'] = '/Uploads/files/'.$data['file_name'];
            $this->return_success($data);
        }
    }
}
