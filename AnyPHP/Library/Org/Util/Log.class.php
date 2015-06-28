<?php
/**
 * 日志工具
 * 2015-5-13
 * imbzd
 *
 * 日志类型：access error runtime operate
 * 日志文件：Runtime/Logs/ModuleName/ModuleName_年月日_logtype.log
 * 日志格式：
 * access 请求访问日志
 * [Front:127.0.0.1] 192.168.1.1 - - [2015-04-09 15:53:08 UTC+8] GET - - "log/read?p=v" HTTP/1.1
 * {
 *	"key":"value"
 * }
 * error 异常错误日志
 * [Front:127.0.0.1] [2015-04-09 15:53:08 UTC+8] WARN - - 异常或错误信息
 * runtime 运行时日志
 * [Front:127.0.0.1] [2015-04-09 15:53:08 UTC+8] 日志信息
 * operate 功能操作日志
 * [Front:127.0.0.1] [2015-04-09 15:53:08 UTC+8] [admin:192.168.1.1] 角色管理 - - 操作内容
 */
namespace Org\Util;

class Log
{
	//日志类型 枚举
	static private $_logtype_list = array(
		'access'  => 'access',   //请求访问日志
		'error'   => 'error',    //错误日志
		'runtime' => 'runtime',  //运行时日志
		'operate' => 'operate',  //功能操作日志
	);
	//日志类型
	static private $_logtype;

	public function __construct($logtype=null)
	{
		$this->_init($logtype);
	}

	//初始化
	static private function _init($logtype=null)
	{
		$this->_setLogtype($logtype);
	}

	//logotype
	static private function _setLogtype($logtype=null)
	{
		$logtype&&isset(self::$_logtype_list[$logtype]) ? self::$_logtype = self::$_logtype_list[$logtype] : null;
	}

	//生成logfile
	static private function _gLogfile($ModuleName=null)
	{
		if (!$ModuleName) return false;

		$logpath = LOG_PATH . $ModuleName . '/';
		$logfile = $ModuleName . '_' . date('Ymd',TIMESTAMP) . '_' . self::$_logtype . '.log';

		mkdir($logpath, 0777, true);

		return $logpath . $logfile;
	}

	/**
	 * 记录日志
	 */
	static public function record($logtype=null, $logparam=array())
	{
		self::_setLogtype($logtype);

		$logfunction = ucfirst(self::$_logtype).'Log';
		if (method_exists('\Org\Util\Log', $logfunction)) self::$logfunction($logparam);
	}

	/**
	 * 记录请求日志
	 * @param array $logparam 日志参数
	 * array(
	 *     "ModuleName" => "Admin",
	 *     "ServerIp"   => "127.0.0.1",
	 *     "ClientIp"   => "192.168.1.1",
	 *     "DateTime"   => "2015-05-17 09:43:10",
	 *     "TimeZone"   => "UTC+8",
	 *     "Method"     => "GET",
	 *     "URL"        => "log/read?p=v",
	 *     "Protocol"   => "HTTP/1.1",
	 *     "RequestData"=> array()
	 * )
	 */
	static public function AccessLog($logparam=array())
	{
		//日志文件
        $logfile = self::_gLogfile($logparam['ModuleName']);
        if (!$logfile) return false;

        //日志内容
		$logcontent  = null;
		$logcontent .= '[' . $logparam['ModuleName'] . ':' . $logparam['ServerIp'] . '] ';
		$logcontent .= $logparam['ClientIp'] . ' - - ' . '[' . $logparam['DateTime'] . ' ' . $logparam['TimeZone'] . '] ';
		$logcontent .= $logparam['Method'] . ' - - "' . $logparam['URL'] . '" ' . $logparam['Protocol'];
		$logcontent .= "\r\n";
		$logcontent .= trim(var_export(json_encode($logparam['RequestData']), true), '\'');
		$logcontent .= "\r\n";

        file_put_contents($logfile, $logcontent, FILE_APPEND);

        return true;
	}

	/**
	 * 记录异常错误日志
	 * @param array $logparam 日志参数
	 * array(
	 *     "ModuleName" => "Admin",
	 *     "ServerIp"   => "127.0.0.1",
	 *     "DateTime"   => "2015-05-17 09:43:10",
	 *     "TimeZone"   => "UTC+8",
	 *     "Degree"     => "WARN",
	 *     "Content"    => "日志内容/错误信息"
	 * )
	 * Degree (
	 *     "DEBUG"  //调试
	 *     "NOTICE" //提醒
	 *     "WARN"   //警告
	 *     "ERROR"  //错误
	 *     "FATAL"  //严重错误
	 * )
	 */
	static public function ErrorLog($logparam=null)
	{
		//日志文件
        $logfile = self::_gLogfile($logparam['ModuleName']);
        if (!$logfile) return false;

        //日志内容
        $logcontent  = null;
		$logcontent .= '[' . $logparam['ModuleName'] . ':' . $logparam['ServerIp'] . '] ';
		$logcontent .= '[' . $logparam['DateTime'] . ' ' . $logparam['TimeZone'] . '] ';
		$logcontent .= $logparam['Degree'] . ' - - ' . $logparam['Content'];
		$logcontent .= "\r\n";

        file_put_contents($logfile, $logcontent, FILE_APPEND);

        return true;
	}

	/**
	 * 记录运行时日志
	 * @param array $logparam 日志参数
	 * array(
	 *     "ModuleName" => "Admin",
	 *     "ServerIp"   => "127.0.0.1",
	 *     "DateTime"   => "2015-05-17 09:43:10",
	 *     "TimeZone"   => "UTC+8",
	 *     "Content"    => "运行时数据信息"
	 * )
	 */
	static public function RuntimeLog($logparam=null)
	{
		//日志文件
        $logfile = self::_gLogfile($logparam['ModuleName']);
        if (!$logfile) return false;

        //日志内容
        $logcontent  = null;
		$logcontent .= '[' . $logparam['ModuleName'] . ':' . $logparam['ServerIp'] . '] ';
		$logcontent .= '[' . $logparam['DateTime'] . ' ' . $logparam['TimeZone'] . '] ';
		$logcontent .= is_array($logparam['Content']) ? trim(var_export(json_encode($logparam['Content']), true), '\'') : $logparam['Content'];
		$logcontent .= "\r\n";

        file_put_contents($logfile, $logcontent, FILE_APPEND);

        return true;
	}

	/**
	 * 记录功能操作日志
	 * @param array $logparam 日志参数
	 * array(
	 *     "ModuleName" => "Admin",
	 *     "ServerIp"   => "127.0.0.1",
	 *     "DateTime"   => "2015-05-17 09:43:10",
	 *     "TimeZone"   => "UTC+8",
	 *     "Account"    => "admin",
	 *     "ClientIp"   => "192.168.1.1",
	 *     "Content"    => "操作内容"
	 * )
	 */
	static public function OperateLog($logparam=null)
	{
		//日志文件
        $logfile = self::_gLogfile($logparam['ModuleName']);
        if (!$logfile) return false;

        //日志内容
        $logcontent  = null;
		$logcontent .= '[' . $logparam['ModuleName'] . ':' . $logparam['ServerIp'] . '] ';
		$logcontent .= '[' . $logparam['DateTime'] . ' ' . $logparam['TimeZone'] . '] ';
		$logcontent .= '[' . $logparam['Account'] . ':' . $logparam['ClientIp'] . '] ';
		$logcontent .= $logparam['Content'];
		$logcontent .= "\r\n";

        file_put_contents($logfile, $logcontent, FILE_APPEND);

        return true;
	}
}