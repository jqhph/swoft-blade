# swoft-blade
Lavarel*5.6* blade模板引擎移植


### 安装
需要把composer源切为<code>https://packagist.laravel-china.org</code>，或国外源。 

运行：
<code>composer require lldca/swoft-blade</code>

如果安装后使用有问题，请运行 <code>composer update</code> 更新下最新的swoft组件。

### Swoft配置
在配置文件 <code>config/properties/app.php</code> 结尾处添加如下配置：

```php
'components' => [
    'custom' => [
        'Swoft\\Blade\\'
    ],
],
'blade-view' => [
    'path' => '@root/resources/views', // 默认模板路径
    'compiled' => '@root/runtime/views', // 编译模板缓存路径
    'namespaces' => [ // 视图命名空间
        
    ],
    'assets' => [ // 静态资源读取目录
        
    ],
    'read-assets' => true, // 开启加载静态资源
],
```

### 使用
```php

 /**
 * @RequestMapping("index")
 *
 * @return \Psr\Http\Message\ResponseInterface
 */
public function index()
{
    return blade('test::main.index', ['msg' => '测试'])
        ->toResponse()
        ->withAddedHeader('Content-Type', 'charset=utf-8');
}

```
