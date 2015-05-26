<?php
/**
 * 搜索引擎API层
 * imbzd
 * 2015-03-21
 */
namespace Org\Net;

use \SearchThrift\SearchRequest;
use \SearchThrift\SearchServiceClient;

class SearchThrift extends Thrift
{
	//Thrift Client
	private $_client = 'SearchThrift';
	//Thrift Host
	private $_host;
	//Thrift Port
	private $_port;
	//Thrift sapi
	private $_sapi;

	public function __construct()
	{
		//加载配置文件
		$this->_loadConfig();

		//初始化Thrift Client
		parent::__construct($this->_client,$this->_host,$this->_port,$this->_sapi);

		//开启连接
		$this->open();
	}

	/**
	 * 加载配置文件
	 */
	private function _loadConfig()
	{
		require_once(APP_PATH.MODULE_NAME.'/Config/thrift.config.php');

		$this->_host = $SearchService['host'];
		$this->_port = $SearchService['port'];
		$this->_sapi = $SearchService['sapi'];
	}

	/**
	 * 搜索接口
	 */
	public function search()
	{
		$SearchRequest = new SearchRequest(
			'Keyword' => ''
		);

		$SearchClient = new SearchServiceClient($this->protocol);
		$result = $SearchClient->Search($SearchRequest);

		return $result;
	}

	/**
	 * 析构方法
	 */
	public function __destruct()
	{
		//关闭连接
		$this->close();
	}
}