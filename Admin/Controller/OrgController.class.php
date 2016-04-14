<?php
/**
 * 第三方库接口逻辑 短信、验证码等
 * 2015-06-30
 * buzhdiao
 */
namespace Admin\Controller;

class OrgController extends BaseController
{
    //vcodekey
    private $_vcode_key = array(
        'vcode_admin_login' => 'VCODE_ADMIN_LOGIN',
    );

    //初始化
    public function __construct(){}

    //生成验证码-管理员登录
    public function VCodeAdminLogin($vcode=null)
    {
        $vcodekey = $this->_vcode_key['vcode_admin_login'];

        $Verify = new \Any\Verify(array(
            'codeSet'  => 'ABCDEFGHJKLMNPQRTUVWXY',
            'useCurve' => false,
            'fontSize' => 15,
            'imageW'   => 110,
            'imageH'   => 32,
            'length'   => 4,
            'fontttf'  => '2.ttf',
        ));

        if ($vcode !== null) {
            return $Verify->check($vcode, $this->_vcode_key);
        } else {
            $Verify->entry($vcodekey);
        }
    }
}