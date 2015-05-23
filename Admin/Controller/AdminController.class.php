<?php
/**
 * 管理业务逻辑
 * 注册、登录、登出等
 * imbzd
 * 2015-05-22
 */
namespace Admin\Controller;

use Org\Util\Filter;

class AdminController extends BaseController
{
    //对象初始化
    public function __construct()
    {
        parent::__construct();
    }

    //获取账号 规则：字母开始 数字字母 长度5-20
    private function _getAccount()
    {
        $account = mRequest("account");
        if (!Filter::F_CharNoShort($account)) {
            $this->ajaxReturn(1, "账号或密码错误！");
        }

        return $account;
    }

    //登录 AJAX
    public function login()
    {
        $this->display();
    }

    //执行登录检查逻辑 AJAX登录
    public function loginck()
    {
        $account = $this->_getAccount();
    }

    //登出
    public function logout()
    {
        
    }
}