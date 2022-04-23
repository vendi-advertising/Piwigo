<?php

require_once __DIR__.'/include/autoload.php';

$app = \App\PWKernel::getInstance();
$entityManager = $app->getEntityManager();

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
