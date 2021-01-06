### 前言

thinkphp5命令行模式工具类，系统规定的命令配置太繁琐了，来个直接一点的封装一下。

### 说明

`php think test`

`php think test a`

### 用例

```php
<?php

namespace app\spider;

use Think5Cli\Cli;

class Test extends Cli
{
	// 必须配置 命令名
    protected $cli_name = 'test';
	// 默认参数
    protected function _exec()
    {
    	echo "hello";
    }
    // 其他参数 使用php think test arg1 调用
    protected function _a() {
    	echo "a method";
    }
}
```

