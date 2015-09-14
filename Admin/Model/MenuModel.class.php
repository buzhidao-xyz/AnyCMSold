<?php
/**
 * 菜单模型
 * buzhidao
 * 2015-8-1
 */
namespace Admin\Model;

class MenuModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取组菜单
    public function getGroup($groupid=null, $show=1)
    {
        $where = array();
        if ($groupid) $where['groupid'] = is_array($groupid) ? array('in', $groupid) : $groupid;
        if ($show) $where['show'] = $show;

        $total = M('menu_group')->where($where)->count();
        $data = M('menu_group')->where($where)->order(array('groupid asc'))->select();
        !is_array($data) ? $data = array() : null;

        return array('total'=>$total, 'data'=>$data);
    }

    //获取组菜单 通过groupid
    public function getGroupByID($groupid=null)
    {
        if (!$groupid) return false;

        $result = $this->getGroup($groupid);

        return $result['total'] ? array_pop($result['data']) : array();
    }

    //获取节点菜单
    public function getNode($nodeid=null, $pnodeid=null, $groupid=null, $show=1)
    {
        $where = array();
        if ($nodeid) $where['nodeid'] = is_array($nodeid) ? array('in', $nodeid) : $nodeid;
        if ($pnodeid) $where['pnodeid'] = $pnodeid;
        if ($groupid) $where['groupid'] = $groupid;
        if ($show) $where['show'] = $show;

        $total = M('menu_node')->where($where)->count();
        $data = M('menu_node')->where($where)->order(array('pnodeid asc,nodeid asc'))->select();
        !is_array($data) ? $data = array() : null;

        return array('total'=>$total, 'data'=>$data);
    }

    //获取节点菜单 通过nodeid
    public function getNodeByID($nodeid=null)
    {
        if (!$nodeid) return false;

        $result = $this->getNode($nodeid);

        return $result['total'] ? array_pop($result['data']) : array();
    }
}