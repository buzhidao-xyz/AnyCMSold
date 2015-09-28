<?php
/**
 * Admin Module 通用基类
 * imbzd
 * 2015-05-11
 */
namespace Admin\Controller;

class CommonController extends BaseController
{
    //分页默认参数
    protected $_page     = 1;
    protected $_pagesize = 30;

    //管理员登录信息 session存储
    protected $managerinfo;

    public function __construct()
    {
        parent::__construct();

        //检查登录
        $this->_CKManagerLogon();

        //获取登录信息
        $this->GSManagerinfo();
    }

    /**
     * 检查登录状态
     */
    protected function _CKManagerLogon()
    {
        $managerinfo = session('managerinfo');
        //如果未登录 跳转到登录页Admin/Login
        if (!$managerinfo || !is_array($managerinfo)) {
            $this->_gotoLogin();
        }

        return true;
    }

    /**
     * 存取登录信息 session
     * @param int $isrefresh 是否刷新session 0:不刷新 1:刷新 默认1
     */
    protected function GSManagerinfo($managerinfo=array(),$isrefresh=1)
    {
        if (!is_array($managerinfo)) return false;

        $smanagerinfo = session('managerinfo');
        !is_array($smanagerinfo) ? $smanagerinfo = array() : null;
        if (!empty($managerinfo)) {
            $smanagerinfo = array_merge($smanagerinfo, $managerinfo);

            session('managerinfo',$smanagerinfo);
            //如果60秒内连续请求 不刷新sessionid
            $session_regenerate_expire_no = session('session_regenerate_expire_no');
            if (!$session_regenerate_expire_no) {
                //刷新sessionid
                $isrefresh ? session('[regenerate]') : null;
                session('session_regenerate_expire_no', 1, 60);
            }
        }

        $this->managerinfo = $smanagerinfo;
        $this->assign('managerinfo', $this->managerinfo);

        return is_array($smanagerinfo)&&!empty($smanagerinfo) ? $smanagerinfo : array();
    }

    /**
     * 注销登录信息session
     */
    protected function USManagerinfo()
    {
        session('managerinfo',null);
    }

    //获取页码 默认1
    protected function _getPage($page=0)
    {
        $_page = $page ? $page : $this->_page;
        $page = mGet('page');

        is_numeric($page)&&$page>0 ? $_page = $page : null;

        return $_page;
    }

    //获取每页记录数
    //默认每页记录数30
    protected function _getPagesize($pagesize=0)
    {
        $_pagesize = $pagesize ? $pagesize : $this->_pagesize;
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
    protected function _mkPage()
    {
        $page     = $this->_getPage();
        $pagesize = $this->_getPagesize();

        //开始行号
        $start     = ($page-1)*$pagesize;
        //数据长度
        $length    = $pagesize;

        //返回
        return array($start,$length);
    }

    //总页数
    protected function _mkPagination($total=0, $param=array())
    {
        if (!$total) return false;

        //page参数
        $page     = $this->_getPage();
        $pagesize = $this->_getPagesize();

        //请求参数
        $query_string = explode('&', $_SERVER['QUERY_STRING']);
        foreach ($query_string as $k=>$q) {
            $p = explode('=', $q);
            if ($p[0]=='page' || in_array($p[0], array_keys($param))) {
                unset($query_string[$k]);
            }
        }
        foreach ($param as $k=>$v) {
            $query_string[] = $k.'='.$v;
        }
        $query_string[] = 'page=';
        //页面url
        $url = $_SERVER['PHP_SELF'].'?'.implode('&', $query_string);

        //总页数
        $totalpage = ceil($total/$pagesize);
        $start = 1;
        $end = $totalpage<11 ? $totalpage : 11;
        if ($totalpage > 11) {
            if ($page <= 6) {
            } else if ($totalpage-$page <= 6) {
                $start = $totalpage-10;
                $end = $totalpage;
            } else {
                $start = $page-5;
                $end = $page+5;
            }
        }
        //列出的页码 当前页前后5页
        $listpage = range($start, $end);

        $prevpage = $page-1;
        $nextpage = $page+1;
        $pagination = array(
            'url' => $url,
            'totalpage' => $totalpage,
            'firstpage' => 1,
            'prevpage'  => $prevpage<0 ? 0 : $prevpage,
            'curtpage'  => $page,
            'nextpage'  => $nextpage>$totalpage ? 0 : $nextpage,
            'lastpage'  => $totalpage,
            'listpage'  => $listpage,
        );
        $this->assign('pagination', $pagination);
        return $pagination;
    }
}