<?php
/**
 *  ==================================================================
 *        文 件 名: IP.php
 *        概    要: IP地址解析工具类
 *        作    者: IT小强
 *        创建时间: 2019-07-23 19:27
 *        修改时间:
 *        copyright (c) 2016 - 2019 mail@xqitw.cn
 *  ==================================================================
 */

namespace itxq\util;

/**
 * IP地址解析工具类
 * Class IP
 * @package itxq\util
 */
class IP
{
    public const  BEGIN = 'begin_ip';
    public const  END = 'end_ip';
    public const  COUNTRY = 'country';
    public const  AREA = 'area';
    public const  IP_INFO_ALL = 0;
    public const IP_INFO = 1;
    public const IP_RANGE = 2;
    /**
     * @var array - 查询结果
     */
    protected $ipInfo = [];

    /**
     * IP数据库文件句柄
     * @var bool|resource
     */
    protected $fh;

    /**
     * 第一条索引
     * @var int
     */
    protected $first;

    /**
     * 最后一条索引
     * @var int
     */
    protected $last;

    /**
     * 索引总数
     * @var int
     */
    protected $total;

    /**
     * @var string 当前客户端IP
     */
    protected $realIP = '';

    /**
     * @var string IP数据存放路径
     */
    protected $ipDataPath = '';

    /**
     * IP constructor.
     * @param string $path
     */
    public function __construct(string $path = '')
    {
        if (!empty($path) && is_file($path)) {
            $this->ipDataPath = $path;
        } else {
            $this->ipDataPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'ip.dat';
        }
    }

    /**
     * 获取IP解析结果
     * @param string     $ip   ip地址
     * @param string|int $type 获取类型
     * @return array|string
     */
    public function getIpInfo(string $ip = '', $type = self::IP_INFO_ALL)
    {
        if (empty($ip)) {
            $ip = $this->ip();
        }
        try {
            $this->lookup($ip, $this->ipDataPath);
        } catch (\Exception $exception) {
            var_dump(10086);
            return null;
        }
        if (count($this->ipInfo) !== 4) {
            var_dump(10010);
            return null;
        }
        if ($type === self::IP_INFO_ALL) {
            $info = $this->ipInfo;
        } else if ($type === self::IP_INFO) {
            $info = $this->ipInfo[self::COUNTRY] . $this->ipInfo[self::AREA];
        } else if ($type === self::IP_RANGE) {
            $info = [$this->ipInfo[self::BEGIN], $this->ipInfo[self::END]];
        } else {
            $info = $this->ipInfo[$type] ?? null;
        }
        return $info;
    }

    /**
     * 获取客户端IP地址
     * @access public
     * @return string
     */
    public function ip(): string
    {
        if (!empty($this->realIP)) {
            return $this->realIP;
        }
        $server       = $_SERVER;
        $this->realIP = $server['REMOTE_ADDR'] ?? '';

        // 如果指定了前端代理服务器IP以及其会发送的IP头
        // 则尝试获取前端代理服务器发送过来的真实IP
        $proxyIp       = $this->config['proxy_server_ip'] ?? [];
        $proxyIpHeader = $this->config['proxy_server_ip_header'] ?? [];
        $tempIP        = '';
        if (count($proxyIp) > 0 && count($proxyIpHeader) > 0) {
            // 从指定的HTTP头中依次尝试获取IP地址
            // 直到获取到一个合法的IP地址
            foreach ($proxyIpHeader as $header) {
                $tempIP = $server[$header] ?? '';
                if (empty($tempIP)) {
                    continue;
                }
                $tempIP = trim(explode(',', $tempIP)[0]);
                if (!$this->isValidIP($tempIP)) {
                    $tempIP = null;
                } else {
                    break;
                }
            }
            // tempIP不为空，说明获取到了一个IP地址
            // 这时我们检查 REMOTE_ADDR 是不是指定的前端代理服务器之一
            // 如果是的话说明该 IP头 是由前端代理服务器设置的
            // 否则则是伪装的
            if ($tempIP) {
                $realIPBin = $this->ip2bin($this->realIP);
                foreach ($proxyIp as $ip) {
                    $serverIPElements = explode('/', $ip);
                    $serverIP         = $serverIPElements[0];
                    $serverIPPrefix   = $serverIPElements[1] ?? 128;
                    $serverIPBin      = $this->ip2bin($serverIP);
                    // IP类型不符
                    if (strlen($realIPBin) !== strlen($serverIPBin)) {
                        continue;
                    }
                    if (strncmp($realIPBin, $serverIPBin, (int)$serverIPPrefix) === 0) {
                        $this->realIP = $tempIP;
                        break;
                    }
                }
            }
        }
        if (!$this->isValidIP($this->realIP)) {
            $this->realIP = '0.0.0.0';
        }
        return $this->realIP;
    }

    /**
     * 检测是否是合法的IP地址
     * @param string $ip   IP地址
     * @param string $type IP地址类型 (ipv4, ipv6)
     * @return boolean
     */
    public function isValidIP(string $ip, string $type = ''): bool
    {
        switch (strtolower($type)) {
            case 'ipv4':
                $flag = FILTER_FLAG_IPV4;
                break;
            case 'ipv6':
                $flag = FILTER_FLAG_IPV6;
                break;
            default:
                $flag = null;
                break;
        }
        return (bool)filter_var($ip, FILTER_VALIDATE_IP, $flag);
    }

    /**
     * 将IP地址转换为二进制字符串
     * @param string $ip
     * @return string
     */
    public function ip2bin(string $ip): string
    {
        if ($this->isValidIP($ip, 'ipv6')) {
            $IPHex = str_split(bin2hex(inet_pton($ip)), 4);
            foreach ($IPHex as $key => $value) {
                $IPHex[$key] = intval($value, 16);
            }
            $IPBin = vsprintf('%016b%016b%016b%016b%016b%016b%016b%016b', $IPHex);
        } else {
            $IPHex = str_split(bin2hex(inet_pton($ip)), 2);
            foreach ($IPHex as $key => $value) {
                $IPHex[$key] = intval($value, 16);
            }
            $IPBin = vsprintf('%08b%08b%08b%08b', $IPHex);
        }
        return $IPBin;
    }

    /**
     * 查询IP信息
     * @param string $ip   ip地址
     * @param string $path IP数据库文件路径
     */
    protected function lookup(string $ip, string $path): void
    {
        if (!$this->isValidIP($ip)) {
            $this->ipInfo = [];
            return;
        }
        $this->ini($path);
        $ip = pack('N', (int)ip2long($ip));
        //二分查找
        $l = 0;
        $r = $this->total;
        while ($l <= $r) {
            $m = floor(($l + $r) / 2); //计算中间索引
            fseek($this->fh, $this->first + $m * 7);
            $beginip = strrev(fread($this->fh, 4)); //中间索引的开始IP地址
            fseek($this->fh, $this->getLong3());
            $endip = strrev(fread($this->fh, 4)); //中间索引的结束IP地址
            if ($ip < $beginip) { //用户的IP小于中间索引的开始IP地址时
                $r = $m - 1;
            } else if ($ip > $endip) { //用户的IP大于中间索引的结束IP地址时
                $l = $m + 1;
            } else { //用户IP在中间索引的IP范围内时
                $findip = $this->first + $m * 7;
                break;
            }
        }
        //查询国家地区信息
        fseek($this->fh, $findip);
        $location[self::BEGIN] = long2ip($this->getLong4()); //用户IP所在范围的开始地址
        $offset                = $this->getLong3();
        fseek($this->fh, $offset);
        $location[self::END] = long2ip($this->getLong4()); //用户IP所在范围的结束地址
        $byte                = fread($this->fh, 1); //标志字节
        switch (ord($byte)) {
            case 1:  //国家和区域信息都被重定向
                $countryOffset = $this->getLong3(); //重定向地址
                fseek($this->fh, $countryOffset);
                $byte = fread($this->fh, 1); //标志字节
                switch (ord($byte)) {
                    case 2: //国家信息被二次重定向
                        fseek($this->fh, $this->getLong3());
                        $location[self::COUNTRY] = $this->getInfo();
                        fseek($this->fh, $countryOffset + 4);
                        $location[self::AREA] = $this->getArea();
                        break;
                    default: //国家信息没有被二次重定向
                        $location[self::COUNTRY] = $this->getInfo($byte);
                        $location[self::AREA]    = $this->getArea();
                        break;
                }
                break;
            case 2: //国家信息被重定向
                fseek($this->fh, $this->getLong3());
                $location[self::COUNTRY] = $this->getInfo();
                fseek($this->fh, $offset + 8);
                $location[self::AREA] = $this->getArea();
                break;
            default: //国家信息没有被重定向
                $location[self::COUNTRY] = $this->getInfo($byte);
                $location[self::AREA]    = $this->getArea();
                break;
        }
        //gb2312 to utf-8（去除无信息时显示的CZ88.NET）
        foreach ($location as $k => $v) {
            $location[$k] = str_replace('CZ88.NET', '', iconv('gb2312', 'utf-8', $v));
        }
        $this->ipInfo = $location;
    }

    /**
     * 初始化
     * @param string $path
     */
    protected function ini(string $path): void
    {
        $this->fh    = fopen($path, 'rb');
        $this->first = $this->getLong4();
        $this->last  = $this->getLong4();
        $this->total = ($this->last - $this->first) / 7; //每条索引7字节
    }

    /**
     * 查询地区信息
     * @return string
     */
    protected function getArea(): string
    {
        $byte = fread($this->fh, 1); //标志字节
        switch (ord($byte)) {
            case 0:
                $area = '';
                break; //没有地区信息
            case 2:
            case 1: //地区被重定向
                fseek($this->fh, $this->getLong3());
                $area = $this->getInfo();
                break;
            default:
                $area = $this->getInfo($byte);
                break; //地区没有被重定向
        }
        return $area;
    }

    /**
     * 查询信息
     * @param string $data
     * @return string
     */
    protected function getInfo(string $data = ''): string
    {
        $char = fread($this->fh, 1);
        while (ord($char) !== 0) { //国家地区信息以0结束
            $data .= $char;
            $char = fread($this->fh, 1);
        }
        return $data;
    }

    protected function getLong4()
    {
        //读取little-endian编码的4个字节转化为长整型数
        $result = unpack('Vlong', fread($this->fh, 4));
        return $result['long'];
    }

    protected function getLong3()
    {
        //读取little-endian编码的3个字节转化为长整型数
        $result = unpack('Vlong', fread($this->fh, 3) . chr(0));
        return $result['long'];
    }

    /**
     * 析构函数
     */
    public function __destruct()
    {
        @fclose($this->fh);
    }
}