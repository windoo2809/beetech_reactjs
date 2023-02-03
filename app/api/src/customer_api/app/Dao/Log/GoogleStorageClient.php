<?php 

namespace App\Dao\Log;

use App\Common\CodeDefinition;
use Google\Cloud\Storage\StorageClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;
use Illuminate\Support\Facades\Auth;

class GoogleStorageClient
{
    private $dateFormat = 'Y-m-d H:i:s';
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('google_cloud_storage');

        $storage = new StorageClient([
            'keyFile' => json_decode(file_get_contents($config['auth']), true)
        ]);
        $storage->registerStreamWrapper();

        foreach (CodeDefinition::MONOLOG_LEVEL as $logLevel) {
            $path = 'gs://'. $config['bucket'] . '/';
            $fileName = $path . $config['system_name'] .'_'. date('Ymd') .'.log';

            $handler = new StreamHandler($fileName, $logLevel, false);
            $logger->pushHandler($handler);
        }

        return $logger;
    }
}
