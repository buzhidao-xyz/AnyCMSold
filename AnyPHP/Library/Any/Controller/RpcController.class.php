<?php
/**
 * AnyPHP RPC控制器类
 */
namespace Any\Controller;

class RpcController {

    protected $allowMethodList  =   '';
    protected $debug            =   false;

   /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
        //导入类库
        Vendor('phpRPC.phprpc_server');
        //实例化phprpc
        $server     =   new \PHPRPC_Server();
        if($this->allowMethodList){
            $methods    =   $this->allowMethodList;
        }else{
            $methods    =   get_class_methods($this);
            $methods    =   array_diff($methods,array('__construct','__call','_initialize'));   
        }
        $server->add($methods,$this);

        if(APP_DEBUG || $this->debug ) {
            $server->setDebugMode(true);
        }
        $server->setEnableGZIP(true);
        $server->start();
        echo $server->comment();
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args){}
}
