<?php
/**
 * Admin Module 通用基类
 * imbzd
 * 2015-05-11
 */
namespace Admin\Controller;

class CommonController extends BaseController
{
    //用户登录信息 session存储
    protected $userinfo;

    public function __construct()
    {
        parent::__construct();

        //获取登录用户信息
        $this->userinfo = $this->GSUserInfo();
    }

    /**
     * 存取用户信息 session
     * @param int $isrefresh 是否刷新session 0:不刷新 1:刷新 默认1
     */
    protected function GSUserInfo($userinfo=array(),$isrefresh=1)
    {
        if (!is_array($userinfo)) return false;

        $suserinfo = session('userinfo');
        if (!empty($userinfo)) {
            $suserinfo = array_merge($suserinfo, $userinfo);

            session('userinfo',$suserinfo);
            //如果60秒内连续请求 不刷新sessionid
            $session_regenerate_expire_no = session('session_regenerate_expire_no');
            if (!$session_regenerate_expire_no) {
                //刷新sessionid
                $isrefresh ? session('[regenerate]') : null;
                session('session_regenerate_expire_no', 1, 60);
            }
        }

        return is_array($suserinfo)&&!empty($suserinfo) ? $suserinfo : array();
    }

    /**
     * 注销用户登录信息session
     */
    protected function USUserInfo()
    {
        session('userinfo',null);
    }

    /**
     * 判断用户是否已登录
     */
    protected function CKUserLogon($appreturnflag=0)
    {
        if (!$this->userinfo || !is_array($this->userinfo)) {
            return $appreturnflag ? $this->appReturn(1,L('user_logon_unknow')) : false;
        }

        return true;
    }

    //获取页码
    //默认页码1
    protected function _getPage($page=0)
    {
        $_page = $page ? $page : 1;
        $page = mGet('page');

        is_numeric($page)&&$page>0 ? $_page = $page : null;

        return $_page;
    }

    //获取每页记录数
    //默认每页记录数30
    protected function _getPagesize($pagesize=0)
    {
        $_pagesize = $pagesize ? $pagesize : 30;
        $pagesize  = mGet('pagesize');

        is_numeric($pagesize)&&$pagesize>0 ? $_pagesize = $pagesize : null;

        return $_pagesize;
    }

    /**
     * 分页预处理
     * @access private
     * @param void
     * @return void
     */
    protected function _mkPage($total=0)
    {
        $page     = $this->_getPage();
        $pagesize = $this->_getPagesize();

        //开始行号
        $start     = ($page-1)*$pagesize;
        //数据长度
        $length    = $pagesize;
        //总页数
        $pagecount = ceil($total/$pagesize);

        //返回
        return array($start,$pagesize,$pagecount);
    }
}