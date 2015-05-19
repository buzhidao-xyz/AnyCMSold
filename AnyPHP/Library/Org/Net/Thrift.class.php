<?php
/**
 * Thrift基类
 * wangbaoqing@imooly.com
 * 2015-03-21
 */
namespace Org\Net;

use Thrift\ClassLoader\ThriftClassLoader;

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TSocket;
use Thrift\Transport\THttpClient;
use Thrift\Transport\TBufferedTransport;
use Thrift\Exception\TException;

class Thrift
{
	//Thrift类库物理路径
	protected $t_thrift_path;
	//client物理路径
	protected $t_client_path;
	
	//通信对象实例
	protected $rpc;

	//客户端服务
	private $_t_client;
	//默认通信方式
	private $_t_rpc = 'Socket';
	//服务器HOST
	private $_t_host;
	//服务器PORT
	private $_t_port;
	//服务器service api
	private $_t_sapi;

	public function __construct($client,$host,$port,$sapi=null,$rpc=null)
	{
		$this->_setConfig($client,$host,$port,$sapi,$rpc);

		$this->_setThriftPath();
		$this->_setClientPath();

		//加载Thrift类
		$this->_loadThrift();

		//加载Thrift client
		$this->_loadClient();
		//初始化Thrift
		$this->_initThrift();
	}

	/**
	 * 设置初始化配置参数
	 * @param string $sapi 服务地址/文件 例：php/server.php
	 */
	private function _setConfig($client,$host,$port,$sapi=null,$rpc=null)
	{
		$this->_t_client = $client;

		$this->_t_host = $host;
		$this->_t_port = $port;
		$this->_t_sapi = $sapi;

		$rpc ? $this->_t_rpc = $rpc : null;
	}

	/**
	 * 设置thrift物理路径
	 */
	private function _setThriftPath()
	{
		$this->t_thrift_path = VENDOR_PATH;
	}

	/**
	 * 设置client物理路径
	 */
	private function _setClientPath()
	{
		$this->t_client_path = LIB_PATH.'Org/Net/Thrift/';
	}

	/**
	 * 加载Thrift类
	 */
	private function _loadThrift()
	{
		require_once($this->t_thrift_path.'Thrift/ClassLoader/ThriftClassLoader.php');
	}

	/**
	 * 加载Thrift client
	 */
	private function _loadClient()
	{
		$loader = new ThriftClassLoader();
		//注册命名空间
		$loader->registerNamespace('Thrift', $this->t_thrift_path);
		//注册client service定义
		$loader->registerDefinition($this->_t_client, $this->t_client_path);
		$loader->register();
	}

	/**
	 * 初始化Thrift
	 */
	private function _initThrift()
	{
		//初始化远程客户端调用协议
		$rpc = null;
		switch ($this->_t_rpc) {
			case 'Socket':
				$rpc = new TSocket($this->_t_host, $this->_t_port);
				break;
			case 'Http':
				$rpc = new THttpClient($this->_t_host, $this->_t_port, $this->_t_sapi);
				break;
			default:
				break;
		}

		$transport = new TBufferedTransport($rpc, 1024, 1024);
		$protocol = new TBinaryProtocol($transport);

		$this->transport = $transport;
		$this->protocol = $protocol;
	}

	/**
	 * 开启rpc
	 */
	protected function open()
	{
		$this->transport->open();
	}
	
	/**
	 * 关闭rpc
	 */
	protected function close()
	{
		$this->transport->close();
	}
}