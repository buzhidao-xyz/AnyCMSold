<?php
/**
 * 管理员管理
 * buzhidao
 * 2015-08-03
 */
namespace Admin\Controller;

class ManagerController extends CommonController
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取管理员ID
    private function _getManagerID()
    {
        $managerID = mRequest('managerID');
        return $managerID;
    }

    //获取账户Account
    private function _getAccount()
    {
        $account = mRequest('account');
        return $account;
    }

    //获取账户password
    private function _getPassword()
    {
        $password = mRequest('password');
        return $password;
    }

    //获取账户password1
    private function _getPassword1()
    {
        $password1 = mRequest('password1');
        return $password1;
    }

    //超级管理员标识
    private function _getSuper()
    {
        $super = mRequest('super');
        return $super;
    }

    //获取关联员工ID
    private function _getUserID()
    {
        $userID = mRequest('userID');
        return $userID;
    }

    //获取员工名称
    private function _getUserName()
    {
        $username = mRequest('username');
        return $username;
    }

    //获取状态
    private function _getStatus()
    {
        $status = mRequest('status');
        return $status;
    }

    //获取角色信息
    private function _getRoleID()
    {
        $roleID = mRequest('roleID', false);
        return $roleID;
    }

    //获取管理员
    public function _getManager($start=0, $length=0)
    {
        //账户
        $account = $this->_getAccount();

        //获取管理员列表
        $result = D('Manager')->getManager(null, $account, $start, $length);
        $datatotal = $result['total'];
        $this->assign('datatotal', $datatotal);

        $datalist = array();
        if (is_array($result['data']) && !empty($result['data'])) {
            $autoindex = $start ? $start+1 : 1;
            foreach ($result['data'] as $manager) {
                $manager['autoindex'] = $autoindex++;

                $manager['supername'] = $manager['super'] ? '是' : '否';

                $datalist[] = $manager;
            }
        }
        $this->assign('datalist', $datalist);

        $param = array(
            'account'   => $account,
        );
        $this->assign('param', $param);
        //解析分页数据
        $this->_mkPagination($datatotal, $param);

        return array($datatotal, $datalist);
    }

    //管理员
    public function index()
    {
        list($start, $length) = $this->_mkPage();
        $this->_getManager($start, $length);

        $this->display();
    }

    //判断是否是系统初始化默认管理员
    private function _ckSystemManager($managerID=null)
    {
        //判断是否是系统初始化默认管理员
        $system_manager = C('SYSTEM_MANAGER');
        if ((is_string($managerID) && $managerID==$system_manager['managerid'])
            || (is_array($managerID) && in_array($system_manager['managerid'], $managerID))) {
            $this->ajaxReturn(1, '系统初始化默认管理员禁止操作！');
        }
    }

    //启用、禁用管理员
    public function enableManager()
    {
        $managerID = $this->_getManagerID();
        if (!$managerID) $this->ajaxReturn(1, '未知管理员！');

        $this->_ckSystemManager($managerID);

        $status = $this->_getStatus();
        $status = $status ? 1 : 0;

        $result = D('Manager')->enableManager($managerID, $status);
        if ($result) {
            $this->ajaxReturn(0, '操作成功！');
        } else {
            $this->ajaxReturn(1, '操作失败！');
        }
    }

    //新增管理员
    public function newManager()
    {
        $rolelist = D('Role')->getRole();
        $this->assign('rolelist', $rolelist['data']);

        $this->display('Manager/managerform');
    }

    //编辑管理员
    public function editManager()
    {
        $managerID = $this->_getManagerID();
        $this->assign('managerID', $managerID);
        $this->_ckSystemManager($managerID);

        $rolelist = D('Role')->getRole();
        $this->assign('rolelist', $rolelist['data']);

        $this->display('Manager/managerform');
    }

    //保存新增、编辑管理员信息
    public function saveManager()
    {
        $managerID = $this->_getManagerID();
        $this->_ckSystemManager($managerID);

        $account = $this->_getAccount();
        if (!Filter::F_Account($account)) {
            $this->ajaxReturn(1, "账号规则错误！");
        }
        $password = $this->_getPassword();
        if (!Filter::F_Password($password)) {
            $this->ajaxReturn(1, "密码规则错误！");
        }
        $password1 = $this->_getPassword1();
        if ($password1 !== $password) $this->ajaxReturn(1, '确认密码不一致！');

        //是否超级管理员
        $super = $this->_getSuper();
        //角色信息 数组
        $roleID = $this->_getRoleID();
        if (!$roleID) $this->ajaxReturn(1, '请选择角色信息！');

        $data = array(
            'account' => $account,
        );
    }

    //删除管理员
    public function deleteManager()
    {
        $managerID = mRequest('managerID', false);
        if (!$managerID || empty($managerID)) $this->ajaxReturn(1, '请选择至少一条数据！');

        $this->_ckSystemManager($managerID);

        !is_array($managerID) ? $managerID = array($managerID) : null;
        $result = D('Manager')->deleteManager($managerID);
        if ($result) {
            $this->ajaxReturn(0, '删除成功！');
        } else {
            $this->ajaxReturn(1, '删除失败！');
        }
    }

    //日志管理
    public function log()
    {
        $this->display();
    }

    //管理员登录日志
    public function loginLog()
    {
        $this->display();
    }

    //管理员操作日志
    public function operateLog()
    {
        $this->display();
    }
}