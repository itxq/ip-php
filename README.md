纯真IP地址解析类
===============

[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.1-8892BF.svg)](http://www.php.net/)
[![Latest Stable Version](https://poser.pugx.org/itxq/ip-php/version)](https://packagist.org/packages/itxq/ip-php)
[![Total Downloads](https://poser.pugx.org/itxq/ip-php/downloads)](https://packagist.org/packages/itxq/ip-php)
[![Latest Unstable Version](https://poser.pugx.org/itxq/ip-php/v/unstable)](//packagist.org/packages/itxq/ip-php)
[![License](https://poser.pugx.org/itxq/ip-php/license)](https://packagist.org/packages/itxq/ip-php)
[![composer.lock available](https://poser.pugx.org/itxq/ip-php/composerlock)](https://packagist.org/packages/itxq/ip-php)

### 开源地址：

[【GitHub:】https://github.com/itxq/ip-php](https://github.com/itxq/ip-php)


### 扩展安装：

+ 方法一：composer命令 `composer require itxq/ip-php`

+ 方法二：直接下载压缩包，然后进入项目中执行 composer命令 `composer update` 来生成自动加载文件

### 引用扩展：

+ 当你的项目不支持composer自动加载时，可以使用以下方式来引用该扩展包

```
// 引入扩展（具体路径请根据你的目录结构自行修改）
require_once __DIR__ . '/vendor/autoload.php';
```

### 使用示例：

```php

use itxq\util\IP;

// 实例化时可指定IP数据库文件路径
$IP = new IP();

// 获取全部信息（返回数组）eg：
// array(4) {
//     ["begin_ip"]=>
//   string(8) "1.80.0.0"
//     ["end_ip"]=>
//   string(12) "1.80.155.255"
//     ["address"]=>
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
