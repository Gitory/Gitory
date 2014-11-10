<?php

namespace Gitory\Gitory;

use Silex\Application as SilexApplication;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\ConfigCache;
use WhoopsSilex\WhoopsServiceProvider;
use Whoops\Handler\JsonResponseHandler;
use Gitory\Gitory\Config\YamlLoader;
use Whoops\Handler\PrettyPageHandler;

class Application extends SilexApplication
{
    use DI, Controllers, Routes, Commands;

    public function __construct($env = 'prod', $debug = false, $interface = 'api')
    {
        parent::__construct(['debug' => $debug]);

        if ($debug) {
            $this->register(new WhoopsServiceProvider);
            if (
                array_key_exists('HTTP_ACCEPT', $_SERVER) &&
                strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
            ) {
                $this['whoops']->pushHandler(new JsonResponseHandler);
            }
        }

        $this['env'] = $env;

        $this->init($this->config($env), $interface);
    }

    /**
     * @param  Array  $config
     * @param  string $interface
     */
    private function init(Array $config, $interface)
    {
        if (isset($config['whoops']['editor']) && $this['whoops.error_page_handler'] instanceof PrettyPageHandler) {
            $this['whoops.error_page_handler']->setEditor($config['whoops']['editor']);
        }

        $this->initDI($config['gitory'], $interface);

        $this->initControllers();

        $this->initRoutes();

        $this->initCommands();
    }

    /**
     * Load application config and replace application parameters
     * @param  string $env application environment name
     * @return Array
     */
    private function config($env)
    {
        $config = $this->loadConfig($env);

        array_walk_recursive($config, function (&$value, $key, $parameters) {
            $value = str_replace(array_keys($parameters), array_values($parameters), $value);
        }, [
            '%env%' => $env,
            '%debug%' => $this['debug'] ? 'true' : 'false',
            '%root_dir%' => __DIR__.'/../',
            '%cache_dir%' => __DIR__.'/../cache/'
        ]);

        return $config;
    }

    /**
     * Load application config
     * @param  string $env application environment name
     * @return array
     */
    private function loadConfig($env)
    {
        $cachePath = __DIR__.'/../cache/'.$env.'/config.php';
        $configPath = __DIR__.'/../config/';
        $locator = new FileLocator([$configPath]);

        $loaderResolver   = new LoaderResolver([new YamlLoader($locator)]);
        $delegatingLoader = new DelegatingLoader($loaderResolver);
        $configCache      = new ConfigCache($cachePath, $this['debug']);

        if ($configCache->isFresh()) {
            return unserialize(file_get_contents($configCache));
        }

        $configFilePath = $configPath.'config_'.$env.'.yml';

        list($config, $resources) = $delegatingLoader->load($configFilePath);

        $configCache->write(serialize($config), $resources);

        return $config;
    }
}
