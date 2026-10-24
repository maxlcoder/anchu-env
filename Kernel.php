<?php


namespace Anchu\Env;

class Kernel
{
    public static function bootstrappers($bootstrappers)
    {
        $result = [];
        foreach ($bootstrappers as $boostrapper) {
            $result[] = $boostrapper;
            if (strpos($boostrapper, 'LoadEnvironmentVariables') !== false) {
                $result[] = \Anchu\Env\Bootstraps\Env::class;
            }
        }
        return $result;
    }
}