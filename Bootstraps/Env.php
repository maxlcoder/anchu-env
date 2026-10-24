<?php
namespace Anchu\Env\Bootstraps;

use Anchu\Env\Env as EnvUtil;
use Illuminate\Contracts\Foundation\Application;

class Env
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        EnvUtil::loadEnv();
    }

}
