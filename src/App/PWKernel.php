<?php

namespace App;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class PWKernel
{
    private static $instance = null;

    private function __construct()
    {

    }

    public static function getInstance(): PWKernel
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getEntityManager(): EntityManager
    {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $isDevMode = true;
        $proxyDir = null;
        $cache = null;
        $useSimpleAnnotationReader = false;
        $config = Setup::createAnnotationMetadataConfiguration(array(PHPWG_ROOT_PATH."/src/App"), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);

        // or if you prefer yaml or XML
        // $config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);
        // $config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);

        $conn = [
            'dbname' => 'pw',
            'user' => 'pw',
            'password' => 'pw',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        ];

        // obtaining the entity manager
        return EntityManager::create($conn, $config);
    }
}