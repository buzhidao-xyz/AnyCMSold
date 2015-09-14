<?php
/**
 * 角色管理
 * buzhidao
 * 2015-08-03
 */
namespace Admin\Controller;

class RoleController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
    }

    //角色
    public function index()
    {
        $this->display();
    }
}