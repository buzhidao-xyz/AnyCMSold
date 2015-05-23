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

        //配置文件输出到模板
        $this->_assignConfig();

        //加载语言包
        $this->_loadLang();

        //记录请求日志
        $this->_accessLog();
    }

    /**
     * 配置文件输出到模板
     */
    private function _assignConfig()
    {
        //HTTP_HOST
        $this->assign('HTTP_HOST',C('HTTP_HOST'));
        //JS静态文件服务器
        $this->assign('JS_FILE_SERVER',C('JS_FILE_SERVER'));
        //css静态文件服务器
        $this->assign('CSS_FILE_SERVER',C('CSS_FILE_SERVER'));
        //.svg .eot .ttf .woff字体文件服务器
        $this->assign('FONT_FILE_SERVER',C('FONT_FILE_SERVER'));
        //图片文件服务器
        $this->assign('IMAGE_SERVER',C('IMAGE_SERVER'));
    }

    /**
     * 加载语言包
     */
    private function _loadLang()
    {
        $lang = C('LANG_DEFAULT');

        //加载公共语言包
        include(LANG_PATH.$lang.'.php');
        L($lang);
        //加载控制器语言包
        include(LANG_PATH.$lang.'/'.CONTROLLER_NAME.'.php');
        L($lang);
    }

    /**
     * 记录请求日志
     */
    private function _accessLog()
    {
        Log::record('access',array(
            'ModuleName' => MODULE_NAME,
            'ServerIp'   => $_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'],
            'ClientIp'   => get_client_ip(),
            'DateTime'   => date('Y-m-d H:i:s', TIMESTAMP),
            'TimeZone'   => 'UTC'.date('O',TIMESTAMP),
            "Method"     => $_SERVER['REQUEST_METHOD'],
            "URL"        => $_SERVER['REQUEST_URI'],
            "Protocol"   => $_SERVER['SERVER_PROTOCOL'],
            "RequestData"=> $_REQUEST,
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
            $msg   = L('appreturn_error_msg');
            $data  = array();
        }

        if (!$error && !is_array($data)) {
            $error = 1;
            $msg = L('appreturn_error_data');
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
}