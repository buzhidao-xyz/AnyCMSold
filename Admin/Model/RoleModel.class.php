<?php
/**
 * 角色模型
 * buzhidao
 * 2015-7-30
 */

namespace Admin\Model;

class RoleModel extends CommonModel
{
    public function __construct()
    {
        parent::__construct();
    }

    //获取角色信息
    public function getRole($roleid=null, $rolename=null, $start=0, $length=0)
    {
        $where = array();
        if ($roleid) $where['roleid'] = is_array($roleid) ? array('in', $roleid) : $roleid;
        if ($rolename) $where['rolename'] = array('like', '%'.$rolename.'%');

        //获取角色信息
        $total = M('role')->where($where)->count();
        $DBObj = M('role')->where($where);
        if ($length) $DBObj = $DBObj->limit($start, $length);
        $result = $DBObj->select();

        $data = is_array($result) ? $result : array();
        return array('total'=>$total, 'data'=>$data);
    }

    //获取角色信息 通过roleid
    public function getRoleByID($roleid=null)
    {

    }

    //获取角色关联的菜单信息 多个角色
    public function getRoleNode($roleids=array())
    {
        if (!is_array($roleids) || empty($roleids)) return false;

        $result = M('role_node')->where(array('roleid'=>array('in', $roleids)))->order(array('roleid asc'))->select();

        return is_array($result)&&!empty($result) ? $result : array();
    }
}