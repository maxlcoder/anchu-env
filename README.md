<p align="center">
    <a href="https://swiftmailer.symfony.com/" target="_blank" rel="external">
        <img src="https://laravel.com/img/logotype.min.svg" height="68px">
    </a>
    <h1 align="center">Laravel .env manager</h1>
    <br>
</p>

```shell
composer require hzanchu/env
```

## 概况
此包的主要作用就是合理管理.env文件，将.env文件分解为如下6个文件，并对`env/`目录下的配置文件加密后纳入git管理，减少上线时对`.env`的手动配置：
- `.env`：不纳入git管理，保存少量配置；
- `env/.env.all`：所有环境的公共配置存放在这里；
- `env/.env.local`：本地环境有关的配置存放在这里；
- `env/.env.dev`：开发环境有关的配置存放在这里；
- `env/.env.pre`：测试环境有关的配置存放在这里；
- `env/.env.live`：生产环境有关的配置存放在这里；

`.env`中存储加密时使用的key，要保证在所有环境中，加密key都是一致的，您需要在`.env`文件中添加如下配置：

```shell
# 加密KEy
ENV_KEY=xxxx
```

> 注意：如果相同配置出现在多个env文件中，优先级：.env > .env.* > .env.all

## 版本说明

### tag: 3.*

- 这个版本采用laraval自己的加密算法进行加密解密（crypt）；
- 需要在.env中配置APP_KEY：`APP_KEY=Env::generateKey('AES-256-CBC')`
- 生成APP_KEY的$cipher需要在`config/app.php`中配置：`'cipher' => 'AES-256-CBC'`

### tag: 2.*

- 这个版本采用openssl加密算法进行加密；
- 加入了解密命令行，命令为：`php artisan env:decode`；

### tag: 1.*

这个版本采用自定义的加密算法，实现加密；

## 1. 配置系统

### 1.1 获取`env/.env.*`配置

对.env.*的获取，必须在获取.env之后，且在loadConfig文件之前，因此需要调整如下代码：
`app/Http/Kernel.php`、`app/Console/Kernel.php`:

```php
<?php
namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected function bootstrappers()
    {
        $boostrapers = parent::bootstrappers();
        return \Anchu\Env\Kernel::bootstrappers($boostrapers);
    }
}
```

### 1.2 命令行加密命令

要使用此包提供的加密服务，需要将加密命令注册到系统中，如下：
`app/Console/Kernel.php`：

```php
<?php

namespace App\Console;

use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Anchu\Env\Commands\EncodeEnv;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        EncodeEnv::class
    ];
}
```

## 2、加密

您可以在`.env`及`/env/.env.*`文件以明文的方式添加配置，但是在提交代码到git前，需要运行加密命令对配置文件进行加密：

```shell
php artisan env:encode
```

上述命令会完成如下两个步骤：

- 对`.env`文件的`PASSWORD`相关的配置进行加密；
- 对`env/.env.*`的所有配置文件都进行加密；

## 3、解密
在获取到所有.env文件之后，需要对已经loadConfig的配置进行解密，解密操作在如下文件中执行：
`app/Providers/AppServiceProvider.php`:

```php
<?php

namespace App\Providers;

use Anchu\Env\Env;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private function recoverConfig()
    {
        Env::decode();
    }
}
```

