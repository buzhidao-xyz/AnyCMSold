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
    //登录结果
    private $_loginlog_result = array(
        'FAILED'  => 0,
        'SUCCESS' => 1,
    );

    //对象初始化
    public function __construct()
    {
        parent::__construct();
    }

    //检测如果已登录 则跳转到主页面
    private function _checkAdminLogon()
    {
        $managerinfo = session('managerinfo');
        if (is_array($managerinfo) && !empty($managerinfo)) {
            $this->_gotoIndex();
        }
    }

    //获取账号 规则：字母开始 字母数字下划线 长度5-20
    private function _getAccount()
    {
        $account = mRequest("account");
        if (!Filter::F_Account($account)) {
            $this->ajaxReturn(1, "账号或密码错误！");
        }

        return $account;
    }

    /**
     * 获取密码 规则：字母数字开始 字母数字下划线!@#$% 长度5-20
     */
    private function _getPassword()
    {
        $password = mRequest('password');
        if (!Filter::F_Password($password)) {
            $this->ajaxReturn(1, "账号或密码错误！");
        }

        return $password;
    }

    //获取验证码
    private function _CKVCode()
    {
        $vcode = mRequest('vcode');
        if (!CR('Org')->CKVcode($vcode)) {
            $this->ajaxReturn(1, "验证码错误！");
        }
        return true;
    }

    //登录 AJAX
    public function login()
    {
        $this->_checkAdminLogon();

        $this->assign('extr_page', 1);
        $this->display();
    }

    //执行登录检查逻辑 AJAX登录
    public function loginck()
    {
        $this->_checkAdminLogon();
        
        $account = $this->_getAccount();
        $password = $this->_getPassword();

        //检查验证码
        $this->_CKVCode();

        //查询管理员信息
        $managerInfo = D('Manager')->getManagerByAccount($account);

        //逻辑判断 如果登录失败
        if (!is_array($managerInfo) || empty($managerInfo)
         || !isset($managerInfo['password']) || !isset($managerInfo['mkey'])
         || $managerInfo['status'] == 0
         || D('Manager')->passwordEncrypt($password, $managerInfo['mkey']) != $managerInfo['password']) {
            $managerid = isset($managerInfo['managerid']) ? $managerInfo['managerid'] : 0;
            //记录登录日志
            D('Manager')->saveManagerLoginLog(array(
                'managerid' => $managerid,
                'account'   => $account,
                'logintime' => TIMESTAMP,
                'loginip'   => $ip,
                'result'    => $this->_loginlog_result['FAILED'],
                'browser'   => $_SERVER['HTTP_USER_AGENT'],
            ));

            $this->ajaxReturn(1, L('LOGIN_ERROR'));
        }

        //登录成功
        $managerid = $managerInfo['managerid'];
        
        //更新管理员账户信息
        $ip = get_client_ip(1,true);
        D('Manager')->saveManager($managerid, array(
            'lastlogintime' => TIMESTAMP,
            'lastloginip'   => $ip,
            'logincount'    => array('exp', 'logincount+1'),
            'updatetime'    => TIMESTAMP,
        ));
        
        //记录登录日志
        D('Manager')->saveManagerLoginLog(array(
            'managerid' => $managerid,
            'account'   => $account,
            'logintime' => TIMESTAMP,
            'loginip'   => $ip,
            'result'    => $this->_loginlog_result['SUCCESS'],
            'browser'   => $_SERVER['HTTP_USER_AGENT'],
        ));

        //获取权限菜单信息
        $access = $this->_getManagerAccess($managerid, $managerInfo['super']);

        //session缓存管理员信息
        session('managerinfo', array(
            'managerid'     => $managerid,
            'account'       => $managerInfo['account'],
            'super'         => $managerInfo['super'],
            'lastlogintime' => $managerInfo['lastlogintime'],
            'lastloginip'   => $managerInfo['lastloginip'],
            'logincount'    => $managerInfo['logincount'],
            'access'        => $access,
        ));

        $this->ajaxReturn(0, L('LOGIN_SUCCESS'), array(
            'location' => $this->_gotoIndex(false),
        ));
    }

    //登出
    public function logout()
    {
        session('managerinfo',null);

        session_destroy();

        $this->_gotoLogin();
    }

    /**
     * 获取管理员访问权限菜单
     * @param  string  $managerid 管理员id
     * @param  integer $super     是否超级管理员 如果是直接获取全部功能菜单 0否 1是
     * @return array              节点菜单 已格式化
     */
    private function _getManagerAccess($managerid=null, $super=0)
    {
        if (!$managerid) return array();

        //权限菜单
        $access = array();

        //管理员角色-节点菜单
        $groupids = array();
        $nodeids = array();

        //如果是超级管理员-不需要获取角色-直接获取全部菜单信息
        //如果不是超级管理员-获取管理员角色-角色关联的菜单信息
        if ($super !== 1) {
            $roleids = array();
            $managerrole = D('Manager')->getManagerRole($managerid);
            if (is_array($managerrole)&&!empty($managerrole)) {
                foreach ($managerrole as $mrole) {
                    $roleids[] = $mrole['roleid'];
                }
            }

            //根据管理员的角色id 获取角色-菜单信息
            $rolenodelist = D('Role')->getRoleNode($roleids);
            if (is_array($rolenodelist)&&!empty($rolenodelist)) {
                foreach ($rolenodelist as $node) {
                    $node['groupid']&&!in_array($node['groupid'], $groupids) ? $groupids[] = $node['groupid'] : null;
                    $node['nodeid']&&!in_array($node['nodeid'], $nodeids) ? $nodeids[] = $node['nodeid'] : null;
                }
            }
        }
        
        //获取组菜单
        $grouplist = D('Menu')->getGroup($groupids);
        if ($grouplist['total']) {
            foreach ($grouplist['data'] as $group) {
                $group['nodelist'] = array();
                $access[$group['groupid']] = $group;
            }
        }
        
        //获取节点菜单
        $nodelist = D('Menu')->getNode($nodeids);
        //组合节点菜单 支持三级
        $accessnode = array();
        if ($nodelist['total']) {
            foreach ($nodelist['data'] as $node) {
                $node['nodelist'] = array();
                //一级节点
                $accessnode[$node['nodeid']] = $node;

                $pnodeid = $node['pnodeid'];
                if ($pnodeid) {
                    //二级节点
                    $accessnode[$pnodeid]['nodelist'][$node['nodeid']] = $node;

                    $ppnodeid = $accessnode[$pnodeid]['pnodeid'];
                    if (isset($accessnode[$ppnodeid])) {
                        //三级节点
                        $accessnode[$ppnodeid]['nodelist'][$pnodeid] = $accessnode[$pnodeid];
                    }
                }
            }
        }
        
        //组合组-节点菜单
        foreach ($accessnode as $d) {
            if ($d['groupid']) $access[$d['groupid']]['nodelist'][$d['nodeid']] = $d;
        }

        return $access;
    }
}