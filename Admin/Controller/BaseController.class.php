<?php
/**
 * Admin Module 基类
 * imbzd
 * 2015-05-11
 */
namespace Admin\Controller;

use Any\Controller;
use Org\Util\Log;

class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        //获取php://input数据
        $this->_getPhpinput();

        //加载语言包
        $this->_loadLang();

        //输出系统配置
        $this->_assignConfig();
        //输出系统参数
        $this->_assignSystem();
        //输出框架参数
        $this->_assignAny();

        //记录请求日志
        $this->_accessLog();
    }

    //获取php://input数据
    private function _getPhpinput()
    {
        $phpinput = file_get_contents("php://input");
        $phpinputdata = json_decode($phpinput, true);
        !is_array($phpinputdata) ? $phpinputdata = array() : null;

        $_REQUEST = array_merge($_REQUEST, $phpinputdata, array('phpinput'=>$phpinput));
    }

    /**
     * 加载语言包
     */
    private function _loadLang()
    {
        $lang = C('DEFAULT_LANG');

        //加载公共语言包
        include(LANG_PATH.$lang.'.php');
        L($lang);
        //加载控制器语言包
        include(LANG_PATH.$lang.'/'.CONTROLLER_NAME.'.php');
        L($lang);
    }

    /**
     * 输出系统配置
     */
    private function _assignConfig()
    {
        $SERVER = array();

        //服务器HOST
        $HOST = C('HOST');
        $SERVER['HOST'] = $HOST;
        $this->assign('SERVER', $SERVER);

        //系统初始化默认管理员
        $SYSTEM_MANAGER = C('SYSTEM_MANAGER');
        $this->assign('system_manager', $SYSTEM_MANAGER);
    }

    //输出系统参数
    private function _assignSystem()
    {
        $SYSTEM = array(
            'systemtitle' => array(
                'name'  => '系统名称',
                'key'   => 'systemtitle',
                'value' => 'AnyCMS',
            ),
        );
        $this->assign('SYSTEM', $SYSTEM);
    }

    //输出框架参数
    private function _assignAny()
    {
        $ANY = array(
            '__APP__' => __APP__,
        );
        $this->assign('ANY', $ANY);
    }

    /**
     * 记录请求日志
     */
    private function _accessLog()
    {
        Log::record('access',array(
            'ModuleName'  => MODULE_NAME,
            'ServerIp'    => $_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'],
            'ClientIp'    => get_client_ip(),
            'DateTime'    => date('Y-m-d H:i:s', TIMESTAMP),
            'TimeZone'    => 'UTC'.date('O',TIMESTAMP),
            'Method'      => $_SERVER['REQUEST_METHOD'],
            'URL'         => $_SERVER['REQUEST_URI'],
            'Protocol'    => $_SERVER['SERVER_PROTOCOL'],
            'RequestData' => $_REQUEST,
        ));
    }

    /**
     * 检查请求类型 是否get/post
     * @param string $quest 请求类型 get/post/put/delete
     */
    protected function CKQuest($quest=null)
    {
        if (!$quest) return false;

        $flag = true;
        switch ($quest) {
            case 'get':
                if (!IS_GET) $flag = false;
                break;
            case 'post':
                if (!IS_POST) $flag = false;
                break;
            case 'put':
                if (!IS_PUT) $flag = false;
                break;
            case 'delete':
                if (!IS_DELETE) $flag = false;
                break;
            default:
                break;
        }
        if (!$flag) $this->appReturn(1,L('quest_error'));

        return true;
    }

    /**
     * AJAX返回数据
     * @param int $error 是否产生错误信息 0没有错误信息 1有错误信息
     * @param string $msg 如果有错 msg为错误信息
     * @param array $data 返回的数据 多维数组
     * @return json 统一返回json数据
     */
    protected function ajaxReturn($error=0,$msg=null,$data=array())
    {
        if ($error && !$msg) {
            $error = 1;
            $msg   = L('ajaxreturn_error_msg');
            $data  = array();
        }

        if (!$error && !is_array($data)) {
            $error = 1;
            $msg = L('ajaxreturn_error_msg');
            $data = array();
        }

        //APP返回
        $return = array(
            'error' => $error,
            'msg'   => $msg,
            'data'  => $data
        );

        $type = 'json';
        switch ($type) {
            case 'json':
                header('Content-Type: application/json');
                $return = json_encode($return);
                break;
            default:
                header('Content-Type: application/json');
                $return = json_encode($return);
                break;
        }

        echo $return;
        exit;
    }

    /**
     * 页面返回数据 展示提示信息
     * @param int $error 是否产生错误信息 0没有错误信息 1有错误信息 大于1为其他错误码
     * @param string $msg 如果有错 msg为错误信息
     * @param array $data 返回的数据 多维数组
     */
    protected function pageReturn($error=0,$msg=null,$data=array())
    {
        if ($error && !$msg) {
            $error = 1;
            $msg   = L('pagereturn_error_msg');
            $data  = array();
        }

        if (!$error && !is_array($data)) {
            $error = 1;
            $msg = L('pagereturn_error_msg');
            $data = array();
        }

        //page数据
        $pageReturn = array(
            'error' => $error,
            'msg'   => $msg,
            'data'  => $data
        );
        $this->assign('pagereturn', $pageReturn);

        $this->display('Public/pagereturn');
        exit;
    }

    //goto登录页
    protected function _gotoLogin($goto=true)
    {
        $location = __APP__.'?s=Admin/Login';
        if ($goto) {
            header('Location:'.$location);
            exit;
        } else {
            return $location;
        }
    }

    //goto登出页
    //bool $goto 是否跳转 true:自动跳转 false:不跳转返回location
    protected function _gotoLogout($goto=true)
    {
        $location = __APP__.'?s=Admin/Logout';
        if ($goto) {
            header('Location:'.$location);
            exit;
        } else {
            return $location;
        }
    }

    //跳转到系统首页
    protected function _gotoIndex($goto=true)
    {
        $location = __APP__.'?s=Index/index';
        if ($goto) {
            header('Location:'.$location);
            exit;
        } else {
            return $location;
        }
    }
}