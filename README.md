# IP地址解析工具类

```php

use itxq\util\IP;

require_once __DIR__ . '/../vendor/autoload.php';

// 实例化时可指定IP数据库文件路径
$IP = new IP();

// 获取全部信息（返回数组）eg：
// array(4) {
//     ["begin_ip"]=>
//   string(8) "1.80.0.0"
//     ["end_ip"]=>
//   string(12) "1.80.155.255"
//     ["country"]=>
//   string(18) "陕西省西安市"
//     ["area"]=>
//   string(6) "电信"
// }
$ipInfoAll = $IP->getIpInfo('1.80.96.23', IP::IP_INFO_ALL);

// 只获取地址信息（返回字符串）eg：贵州省贵阳市南明区电信
$ipInfo = $IP->getIpInfo('1.80.96.23', IP::IP_INFO);

// 获取IP所在IP段（返回数组）eg：['1.80.0.0','1.80.155.255']
$ipRange = $IP->getIpInfo('1.80.96.23', IP::IP_RANGE);

```
