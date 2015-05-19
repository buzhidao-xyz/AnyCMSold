<?php
/**
 * modifyed by wangbaoqing
 * 2014-08-29
 * 参考代码：http://blog.csdn.net/heiyeshuwu/article/details/5050885 作者：黑夜路人V
 * try {
 *     $http = Http::factory('http://www.baidu.com', Http::TYPE_SOCK );
 *     echo $http->get();
 *     $http = Http::factory('http://127.0.0.1/test/i.php', Http::TYPE_SOCK );
 *     echo $http->post('', array('user'=>'我们', 'nick'=>'ASSADF@#!32812989+-239%ASDF'), '', array('aa'=>'bb', 'cc'=>'dd'));
 * } catch (Exception $e) {
 *     echo $e->getMessage();
 * }
 */
namespace Org\Net;

use Any\Exception;

/**
 * Http 工具类
 * 提供一系列的Http方法
 * @author    liu21st <liu21st@gmail.com>
 */
class Http {
    /**
     * @var 使用 CURL
     */
    const TYPE_CURL = 1;
    /**
     * @var 使用 Socket
     */
    const TYPE_SOCK = 2;
    /**
     * @var 使用 Stream
     */
    const TYPE_STREAM = 3;
    private function __construct() {
    }
    private function __clone() {
    }
    /**
     * 采集远程文件
     * @access public
     * @param string $remote 远程文件名
     * @param string $local 本地保存文件名
     * @return mixed
     */
    static public function curlDownload($remote, $local) {
        $cp = curl_init($remote);
        $fp = fopen($local, "w");
        curl_setopt($cp, CURLOPT_FILE, $fp);
        curl_setopt($cp, CURLOPT_HEADER, 0);
        curl_exec($cp);
        curl_close($cp);
        fclose($fp);
    }
    /**
     * 使用 fsockopen 通过 HTTP 协议直接访问(采集)远程文件
     * 如果主机或服务器没有开启 CURL 扩展可考虑使用
     * fsockopen 比 CURL 稍慢,但性能稳定
     * @static
     * @access public
     * @param string $url 远程URL
     * @param array $conf 其他配置信息
     *        int   limit 分段读取字符个数
     *        string post  post的内容,字符串或数组,key=value&形式
     *        string cookie 携带cookie访问,该参数是cookie内容
     *        string ip    如果该参数传入,$url将不被使用,ip访问优先
     *        int    timeout 采集超时时间
     *        bool   block 是否阻塞访问,默认为true
     * @return mixed
     */
    static public function fsockopenDownload($url, $conf = array()) {
        $return = '';
        if (!is_array($conf)) return $return;
        $matches = parse_url($url);
        !isset($matches['host']) && $matches['host'] = '';
        !isset($matches['path']) && $matches['path'] = '';
        !isset($matches['query']) && $matches['query'] = '';
        !isset($matches['port']) && $matches['port'] = '';
        $host = $matches['host'];
        $path = $matches['path'] ? $matches['path'] . ($matches['query'] ? '?' . $matches['query'] : '') : '/';
        $port = !empty($matches['port']) ? $matches['port'] : 80;
        $conf_arr = array(
            'limit' => 0,
            'post' => '',
            'cookie' => '',
            'ip' => '',
            'timeout' => 15,
            'block' => TRUE,
        );
        foreach (array_merge($conf_arr, $conf) as $k => $v) $ {
            $k
        } = $v;
        if ($post) {
            if (is_array($post)) {
                $post = http_build_query($post);
            }
            $out = "POST $path HTTP/1.0\r\n";
            $out.= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out.= "Accept-Language: zh-cn\r\n";
            $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
            $out.= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out.= "Host: $host\r\n";
            $out.= 'Content-Length: ' . strlen($post) . "\r\n";
            $out.= "Connection: Close\r\n";
            $out.= "Cache-Control: no-cache\r\n";
            $out.= "Cookie: $cookie\r\n\r\n";
            $out.= $post;
        } else {
            $out = "GET $path HTTP/1.0\r\n";
            $out.= "Accept: */*\r\n";
            //$out .= "Referer: $boardurl\r\n";
            $out.= "Accept-Language: zh-cn\r\n";
            $out.= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
            $out.= "Host: $host\r\n";
            $out.= "Connection: Close\r\n";
            $out.= "Cookie: $cookie\r\n\r\n";
        }
        $fp = @fsockopen(($ip ? $ip : $host) , $port, $errno, $errstr, $timeout);
        if (!$fp) {
            return '';
        } else {
            stream_set_blocking($fp, $block);
            stream_set_timeout($fp, $timeout);
            @fwrite($fp, $out);
            $status = stream_get_meta_data($fp);
            if (!$status['timed_out']) {
                while (!feof($fp)) {
                    if (($header = @fgets($fp)) && ($header == "\r\n" || $header == "\n")) {
                        break;
                    }
                }
                $stop = false;
                while (!feof($fp) && !$stop) {
                    $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                    $return.= $data;
                    if ($limit) {
                        $limit-= strlen($data);
                        $stop = $limit <= 0;
                    }
                }
            }
            @fclose($fp);
            return $return;
        }
    }
    /**
     * 下载文件
     * 可以指定下载显示的文件名，并自动发送相应的Header信息
     * 如果指定了content参数，则下载该参数的内容
     * @static
     * @access public
     * @param string $filename 下载文件名
     * @param string $showname 下载显示的文件名
     * @param string $content  下载的内容
     * @param integer $expire  下载内容浏览器缓存时间
     * @return void
     */
    static public function download($filename, $showname = '', $content = '', $expire = 180) {
        if (is_file($filename)) {
            $length = filesize($filename);
        } elseif (is_file(UPLOAD_PATH . $filename)) {
            $filename = UPLOAD_PATH . $filename;
            $length = filesize($filename);
        } elseif ($content != '') {
            $length = strlen($content);
        } else {
            E($filename . L('下载文件不存在！'));
        }
        if (empty($showname)) {
            $showname = $filename;
        }
        $showname = basename($showname);
        if (!empty($filename)) {
            $finfo = new finfo(FILEINFO_MIME);
            $type = $finfo->file($filename);
        } else {
            $type = "application/octet-stream";
        }
        //发送Http Header信息 开始下载
        header("Pragma: public");
        header("Cache-control: max-age=" . $expire);
        //header('Cache-Control: no-store, no-cache, must-revalidate');
        header("Expires: " . gmdate("D, d M Y H:i:s", time() + $expire) . "GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s", time()) . "GMT");
        header("Content-Disposition: attachment; filename=" . $showname);
        header("Content-Length: " . $length);
        header("Content-type: " . $type);
        header('Content-Encoding: none');
        header("Content-Transfer-Encoding: binary");
        if ($content == '') {
            readfile($filename);
        } else {
            echo ($content);
        }
        exit();
    }
    /**
     * 显示HTTP Header 信息
     * @return string
     */
    static function getHeaderInfo($header = '', $echo = true) {
        ob_start();
        $headers = getallheaders();
        if (!empty($header)) {
            $info = $headers[$header];
            echo ($header . ':' . $info . "\n");;
        } else {
            foreach ($headers as $key => $val) {
                echo ("$key:$val\n");
            }
        }
        $output = ob_get_clean();
        if ($echo) {
            echo (nl2br($output));
        } else {
            return $output;
        }
    }
    /**
     * HTTP Protocol defined status codes
     * @param int $num
     */
    static function sendHttpStatus($code) {
        static $_status = array(
            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',
            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found', // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',
            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );
        if (isset($_status[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        }
    }
    /**
     * HTTP工厂操作方法
     *
     * @param string $url 需要访问的URL
     * @param int $type 需要使用的HTTP类
     * @return object
     */
    public static function Init($url = '', $type = self::TYPE_SOCK) {
        if ($type == '') {
            $type = self::TYPE_SOCK;
        }
        switch ($type) {
            case self::TYPE_CURL:
                if (!function_exists('curl_init')) {
                    throw new Exception(__CLASS__ . " PHP CURL extension not install");
                }
                $obj = Http_Curl::getInstance($url);
                break;

            case self::TYPE_SOCK:
                if (!function_exists('fsockopen')) {
                    throw new Exception(__CLASS__ . " PHP function fsockopen() not support");
                }
                $obj = Http_Sock::getInstance($url);
                break;

            case self::TYPE_STREAM:
                if (!function_exists('stream_context_create')) {
                    throw new Exception(__CLASS__ . " PHP Stream extension not install");
                }
                $obj = Http_Stream::getInstance($url);
                break;

            default:
                throw new Exception("http access type $type not support");
        }
        return $obj;
    }
    /** 
     * 生成一个供Cookie或HTTP GET Query的字符串
     *
     * @param array $data 需要生产的数据数组，必须是 Name => Value 结构
     * @param string $sep 两个变量值之间分割的字符，缺省是 &
     * @return string 返回生成好的Cookie查询字符串
     */
    public static function makeQuery($data, $sep = '&') {
        $encoded = '';
        while (list($k, $v) = each($data)) {
            $encoded.= ($encoded ? "$sep" : "");
            $encoded.= rawurlencode($k) . "=" . rawurlencode($v);
        }
        return $encoded;
    }
} //类定义结束

/** 
 * 使用CURL 作为核心操作的HTTP访问类
 *
 * @desc CURL 以稳定、高效、移植性强作为很重要的HTTP协议访问客户端，必须在PHP中安装 CURL 扩展才能使用本功能
 */
class Http_Curl {
    /**
     * @var object 对象单例
     */
    static $_instance = NULL;
    /**
     * @var string 需要发送的cookie信息
     */
    private $cookies = '';
    /** 
     * @var array 需要发送的头信息
     */
    private $header = array();
    /** 
     * @var string 需要访问的URL地址
     */
    private $uri = '';
    /** 
     * @var array 需要发送的数据
     */
    private $vars = array();
    /**
     * curl最大执行时间 秒
     */
    private $timeout = 5;
    /**
     * 构造函数
     *
     * @param string $configFile 配置文件路径
     */
    private function __construct($url) {
        $this->uri = $url;
    }
    /**
     * 保证对象不被clone
     */
    private function __clone() {
    }
    /** 
     * 获取对象唯一实例
     *
     * @param string $configFile 配置文件路径
     * @return object 返回本对象实例
     */
    public static function getInstance($url = '') {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($url);
        }
        $url ? self::$_instance->uri = $url : null;
        return self::$_instance;
    }
    /** 
     * 设置需要发送的HTTP头信息
     *
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *       或单一的一条类似于 'Host: example.com' 头信息字符串
     * @return void
     */
    public function setHeader($header) {
        if (empty($header)) {
            return;
        }
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                $this->header[] = is_numeric($k) ? trim($v) : (trim($k) . ": " . trim($v));
            }
        } elseif (is_string($header)) {
            $this->header[] = $header;
        }
    }
    /** 
     * 设置Cookie头信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setCookie($cookie) {
        if (empty($cookie)) {
            return;
        }
        if (is_array($cookie)) {
            $this->cookies = Http::makeQuery($cookie, ';');
        } elseif (is_string($cookie)) {
            $this->cookies = $cookie;
        }
    }
    /** 
     * 设置要发送的数据信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param array 设置需要发送的数据信息，一个类似于 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setVar($vars) {
        if (empty($vars)) {
            return;
        }
        if (is_array($vars)) {
            $this->vars = $vars;
        } else {
            $varss = json_decode($vars,true);
            if (is_array($varss)) $this->vars = $vars;
        }
    }
    /** 
     * 设置要请求的URL地址
     *
     * @param string $url 需要设置的URL地址
     * @return void
     */
    public function setUrl($url) {
        if ($url != '') {
            $this->uri = $url;
        }
    }
    /** 
     * 发送HTTP GET请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function get($url = '', $vars = array() , $header = array() , $cookie = '', $timeout = 5, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('GET', $timeout, $options);
    }
    /** 
     * 发送HTTP POST请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function post($url = '', $vars = array() , $header = array() , $cookie = '', $timeout = 0, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('POST', $timeout, $options);
    }
    /** 
     * 发送HTTP请求核心函数
     *
     * @param string $method 使用GET还是POST方式访问
     * @param array $vars 需要另外附加发送的GET/POST数据
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return string 返回服务器端读取的返回数据
     */
    public function send($method = 'GET', $timeout = 0, $options = array()) {
        !$timeout ? $timeout = $this->timeout : null;
        //处理参数是否为空
        if ($this->uri == '') {
            throw new Exception(__CLASS__ . ": Access url is empty");
        }
        //初始化CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        //设置特殊属性
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //处理GET请求参数
        if ($method == 'GET' && !empty($this->vars)) {
            $query = Http::makeQuery($this->vars);
            $parse = parse_url($this->uri);
            $sep = isset($parse['query']) ? '&' : '?';
            $this->uri.= $sep . $query;
        }
        //处理POST请求数据
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->vars);
        }
        //设置cookie信息
        if (!empty($this->cookies)) {
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
        }
        //设置HTTP缺省头
        if (empty($this->header)) {
            $this->header = array(
                'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)',
                //'Accept-Language: zh-cn',
                //'Cache-Control: no-cache',
                
            );
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        //发送请求读取输数据
        curl_setopt($ch, CURLOPT_URL, $this->uri);

        $result = curl_exec($ch);
        $error = null;
        if (($err = curl_error($ch))) {
            curl_close($ch);
            // throw new Exception(__CLASS__ . " error: " . $err);
            $error = 'curl_timeout';
        }
        curl_close($ch);
        return array(
            'error' => $error,
            'result'  => $result
        );
    }
}
/** 
 * 使用 Socket操作(fsockopen) 作为核心操作的HTTP访问接口
 *
 * @desc Network/fsockopen 是PHP内置的一个Sokcet网络访问接口，必须安装/打开 fsockopen 函数本类才能工作，
 *    同时确保其他相关网络环境和配置是正确的
 */
class Http_Sock {
    /** 
     * @var object 对象单例
     */
    static $_instance = NULL;
    /** 
     * @var string 需要发送的cookie信息
     */
    private $cookies = '';
    /** 
     * @var array 需要发送的头信息
     */
    private $header = array();
    /** 
     * @var string 需要访问的URL地址
     */
    private $uri = '';
    /** 
     * @var array 需要发送的数据
     */
    private $vars = array();
    /** 
     * 构造函数
     *
     * @param string $configFile 配置文件路径
     */
    private function __construct($url) {
        $this->uri = $url;
    }
    /** 
     * 保证对象不被clone
     */
    private function __clone() {
    }
    /** 
     * 获取对象唯一实例
     *
     * @param string $configFile 配置文件路径
     * @return object 返回本对象实例
     */
    public static function getInstance($url = '') {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($url);
        }
        return self::$_instance;
    }
    /** 
     * 设置需要发送的HTTP头信息
     *
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *       或单一的一条类似于 'Host: example.com' 头信息字符串
     * @return void
     */
    public function setHeader($header) {
        if (empty($header)) {
            return;
        }
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                $this->header[] = is_numeric($k) ? trim($v) : (trim($k) . ": " . trim($v));
            }
        } elseif (is_string($header)) {
            $this->header[] = $header;
        }
    }
    /** 
     * 设置Cookie头信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setCookie($cookie) {
        if (empty($cookie)) {
            return;
        }
        if (is_array($cookie)) {
            $this->cookies = Http::makeQuery($cookie, ';');
        } elseif (is_string($cookie)) {
            $this->cookies = $cookie;
        }
    }
    /** 
     * 设置要发送的数据信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param array 设置需要发送的数据信息，一个类似于 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setVar($vars) {
        if (empty($vars)) {
            return;
        }
        if (is_array($vars)) {
            $this->vars = $vars;
        }
    }
    /** 
     * 设置要请求的URL地址
     *
     * @param string $url 需要设置的URL地址
     * @return void
     */
    public function setUrl($url) {
        if ($url != '') {
            $this->uri = $url;
        }
    }
    /** 
     * 发送HTTP GET请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function get($url = '', $vars = array() , $header = array() , $cookie = '', $timeout = 5, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('GET', $timeout);
    }
    /** 
     * 发送HTTP POST请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function post($url = '', $vars = array() , $header = array() , $cookie = '', $timeout = 5, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('POST', $timeout);
    }
    /** 
     * 发送HTTP请求核心函数
     *
     * @param string $method 使用GET还是POST方式访问
     * @param array $vars 需要另外附加发送的GET/POST数据
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return string 返回服务器端读取的返回数据
     */
    public function send($method = 'GET', $timeout = 5, $options = array()) {
        //处理参数是否为空
        if ($this->uri == '') {
            throw new Exception(__CLASS__ . ": Access url is empty");
        }
        //处理GET请求参数
        if ($method == 'GET' && !empty($this->vars)) {
            $query = Http::makeQuery($this->vars);
            $parse = parse_url($this->uri);
            $sep = isset($parse['query']) && ($parse['query'] != '') ? '&' : '?';
            $this->uri.= $sep . $query;
        }
        //处理POST请求数据
        $data = '';
        if ($method == 'POST' && !empty($this->vars)) {
            $data = Http::makeQuery($this->vars);
            $this->setHeader('Content-Type: application/x-www-form-urlencoded');
            $this->setHeader('Content-Length: ' . strlen($data));
        }
        //解析URL地址
        $url = parse_url($this->uri);
        $host = $url['host'];
        $port = isset($url['port']) && ($url['port'] != '') ? $url['port'] : 80;
        $path = isset($url['path']) && ($url['path'] != '') ? $url['path'] : '/';
        $path.= isset($url['query']) ? "?" . $url['query'] : '';

        //组织HTTP请求头信息
        $header1 = &$this->header;
        array_unshift($header1, $method . " " . $path . " HTTP/1.1");
        $this->setHeader('User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)');

        if (!preg_match("/^[/d]{1,3}/.[/d]{1,3}/.[/d]{1,3}/.[/d]{1,3}$/", $host)) {
            $this->setHeader("Host: " . $host);
        }
        if ($this->cookies != '') {
            $this->setHeader("Cookie: " . $this->cookies);
        }
        $this->setHeader("Connection: Close");
        //'Accept-Language: zh-cn',
        //'Cache-Control: no-cache',
        //构造请求信息
        $header = '';
        foreach ($this->header as $h) {
            $header.= $h . "/r/n";
        }
        $header.= "/r/n";
        if ($method == 'POST' && $data != '') {
            $header.= $data . "/r/n";
        }

        //连接服务器发送请求数据
        $ip = gethostbyname($host);
        $errno = &$errno;
        $errstr = &$errstr;
        if (!($fp = fsockopen($ip, $port, $errno, $errstr, $timeout))) {
            throw new Exception(__CLASS__ . ": Can't connect $host:$port, errno:$errno,message:$errstr");
        }

        fputs($fp, $header);
        $lineSize = 1024;
        //处理301,302跳转页面访问
        $line = fgets($fp, $lineSize);
        $first = preg_split("//s/", trim($line));
        if (isset($first[1]) && in_array($first[1], array(
            '301',
            '302'
        ))) {
            while (!feof($fp)) {
                $line = fgets($fp, $lineSize);
                $second = preg_split("//s/", trim($line));
                if (ucfirst(trim($second[0])) == 'Location:' && $second[1] != '') {
                    $this->header = array();
                    return $this->get(trim($second[1]));
                }
            }
        }
        //正常读取返回数据
        $buf = '';
        $inheader = 1;
        while (!feof($fp)) {
            if ($inheader && ($line == "/n" || $line == "/r/n")) {
                $inheader = 0;
            }
            $line = fgets($fp, $lineSize);
            if ($inheader == 0) {
                $buf.= $line;
            }
        }
        fclose($fp);
        return $buf;
    }
}
/** 
 * 使用文件流操作函数为核心操作的HTTP访问接口
 *
 * @desc stream_* 和 fopen/file_get_contents 是PHP内置的一个流和文件操作接口，必须打开 fsockopen 函数本类才能工作，
 *    同时确保其他相关网络环境和配置是正确的，包括 allow_url_fopen 等设置
 */
class Http_Stream {
    /** 
     * @var object 对象单例
     */
    static $_instance = NULL;
    /** 
     * @var string 需要发送的cookie信息
     */
    private $cookies = '';
    /** 
     * @var array 需要发送的头信息
     */
    private $header = array();
    /** 
     * @var string 需要访问的URL地址
     */
    private $uri = '';
    /** 
     * @var array 需要发送的数据
     */
    private $vars = array();
    /** 
     * 构造函数
     *
     * @param string $configFile 配置文件路径
     */
    private function __construct($url) {
        $this->uri = $url;
    }
    /** 
     * 保证对象不被clone
     */
    private function __clone() {
    }
    /** 
     * 获取对象唯一实例
     *
     * @param string $configFile 配置文件路径
     * @return object 返回本对象实例
     */
    public static function getInstance($url = '') {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($url);
        }
        return self::$_instance;
    }
    /** 
     * 设置需要发送的HTTP头信息
     *
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *       或单一的一条类似于 'Host: example.com' 头信息字符串
     * @return void
     */
    public function setHeader($header) {
        if (empty($header)) {
            return;
        }
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                $this->header[] = is_numeric($k) ? trim($v) : (trim($k) . ": " . trim($v));
            }
        } elseif (is_string($header)) {
            $this->header[] = $header;
        }
    }
    /** 
     * 设置Cookie头信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setCookie($cookie) {
        if (empty($cookie)) {
            return;
        }
        if (is_array($cookie)) {
            $this->cookies = Http::makeQuery($cookie, ';');
        } elseif (is_string($cookie)) {
            $this->cookies = $cookie;
        }
    }
    /** 
     * 设置要发送的数据信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param array 设置需要发送的数据信息，一个类似于 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setVar($vars) {
        if (empty($vars)) {
            return;
        }
        if (is_array($vars)) {
            $this->vars = $vars;
        }
    }
    /** 
     * 设置要请求的URL地址
     *
     * @param string $url 需要设置的URL地址
     * @return void
     */
    public function setUrl($url) {
        if ($url != '') {
            $this->uri = $url;
        }
    }
    /** 
     * 发送HTTP GET请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function get($url = '', $vars = array() , $header = array() , $cookie = '', $timeout = 5, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('GET', $timeout);
    }
    /** 
     * 发送HTTP POST请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function post($url = '', $vars = array() , $header = array() , $cookie = '', $timeout = 5, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('POST', $timeout);
    }
    /** 
     * 发送HTTP请求核心函数
     *
     * @param string $method 使用GET还是POST方式访问
     * @param array $vars 需要另外附加发送的GET/POST数据
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return string 返回服务器端读取的返回数据
     */
    public function send($method = 'GET', $timeout = 5, $options = array()) {
        //处理参数是否为空
        if ($this->uri == '') {
            throw new Exception(__CLASS__ . ": Access url is empty");
        }
        $parse = parse_url($this->uri);
        $host = $parse['host'];
        //处理GET请求参数
        if ($method == 'GET' && !empty($this->vars)) {
            $query = Http::makeQuery($this->vars);
            $sep = isset($parse['query']) && ($parse['query'] != '') ? '&' : '?';
            $this->uri.= $sep . $query;
        }
        //处理POST请求数据
        $data = '';
        if ($method == 'POST' && !empty($this->vars)) {
            $data = Http::makeQuery($this->vars);
        }
        //设置缺省头
        $this->setHeader('User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)');
        if (!preg_match("/^[/d]{1,3}/.[/d]{1,3}/.[/d]{1,3}/.[/d]{1,3}$/", $host)) {
            $this->setHeader("Host: " . $host);
        }
        if ($this->cookies != '') {
            $this->setHeader("Cookie: " . $this->cookies);
        }
        $this->setHeader("Connection: Close");
        //'Accept-Language: zh-cn',
        //'Cache-Control: no-cache',
        //构造头信息
        $opts = array(
            'http' => array(
                'method' => $method,
                'timeout' => $timeout,
            )
        );
        if ($data != '') {
            $opts['http']['content'] = $data;
        }
        $opts['http']['header'] = '';
        foreach ($this->header as $h) {
            $opts['http']['header'].= $h . "/r/n";
        }
        //print_r($opts);exit;
        //读取扩展设置选项
        if (!empty($options)) {
            isset($options['proxy']) ? $opts['http']['proxy'] = $options['proxy'] : '';
            isset($options['max_redirects']) ? $opts['http']['max_redirects'] = $options['max_redirects'] : '';
            isset($options['request_fulluri']) ? $opts['http']['request_fulluri'] = $options['request_fulluri'] : '';
        }
        //发送数据返回
        $context = stream_context_create($opts);
        if (($buf = file_get_contents($this->uri, null, $context)) === false) {
            throw new Exception(__CLASS__ . ": file_get_contents(" . $this->uri . ") fail");
        }
        return $buf;
    }
}

