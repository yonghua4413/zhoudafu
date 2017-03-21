<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 咨询model
 * 
 * @author yonghua 
 *
 */
class Model_news extends MY_Model {

    private $_table = 't_news';

    public function __construct() {
        parent::__construct($this->_table);
    }
    
    
    
}