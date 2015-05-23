<?php
/**
 * Admin Module Main Enter
 * imbzd
 * 2015-05-11
 */
namespace Admin\Controller;

class IndexController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
    }

    //主页
    public function index()
    {
        $this->display();
    }
}