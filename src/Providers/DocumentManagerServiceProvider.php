<?php

namespace Codegrapple\Doctrine\MongoDB\Providers;

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class DocumentManagerServiceProvider extends ServiceProvider
{
    private $config;

    public function __construct(Application $app)
    {
        parent::__construct($app);
        $this->config = $this->loadConfig();
    }

    protected function loadConfig(): array
    {
        return config('document-manager');
    }

    public function register()
    {
        $this->app->singleton(DocumentManager::class, function () {
            $config = $this->configure();
            $connection = $this->connect($config);

            return DocumentManager::create($connection, $config);
        });

        $this->app->alias(DocumentManager::class, 'DocumentManager');
    }

    private function configure(): Configuration
    {
        $config = new Configuration();

        if ($proxies = array_get($this->config, 'proxies')) {
            if ($path = array_get($proxies, 'path')) {
                $config->setProxyDir($path);
            }

            if ($namespace = array_get($proxies, 'namespace')) {
                $config->setProxyNamespace($namespace);
            }

            $config->setAutoGenerateProxyClasses(array_get($proxies, 'auto_generate', false));
        }

        if ($hydrators = array_get($this->config, 'hydrators')) {
            if ($path = array_get($hydrators, 'path')) {
                $config->setHydratorDir($path);
            }

            if ($namespace = array_get($hydrators, 'namespace')) {
                $config->setHydratorNamespace($namespace);
            }

            $config->setAutoGenerateProxyClasses(array_get($hydrators, 'auto_generate', false));
        }

        $config->setMetadataDriverImpl(AnnotationDriver::create(array_get($this->config, 'paths', [])));

        AnnotationDriver::registerAnnotationClasses();

        return $config;
    }

    private function connect(Configuration $config): Connection
    {
        $dbConfig = config('database.connections.'.$this->config['connection']);

        $options = array_get($dbConfig, 'options', []);
        $driverOptions = array_get($dbConfig, 'driver_options', []);
        if (! isset($options['username']) && ! empty($dbConfig['username'])) {
            $options['username'] = $dbConfig['username'];
        }
        if (! isset($options['password']) && ! empty($dbConfig['password'])) {
            $options['password'] = $dbConfig['password'];
        }

        $hosts = is_array($dbConfig['host']) ? $dbConfig['host'] : [$dbConfig['host']];
        foreach ($hosts as &$host) {
            if (strpos($host, ':') === false && ! empty($dbConfig['port'])) {
                $host = $host.':'.$dbConfig['port'];
            }
        }
        $auth_database = isset($dbConfig['options']) && ! empty($dbConfig['options']['database']) ? $dbConfig['options']['database'] : null;
        $server = 'mongodb://'.implode(',', $hosts).($auth_database ? '/'.$auth_database : '');
        $config->setDefaultDB($dbConfig['database']);
        $connection = new Connection($server, $options, $config, null, $driverOptions);

        return $connection;
    }
}