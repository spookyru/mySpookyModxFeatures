<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

// Load the classes
$modx->addPackage('ModxApiTalk\Model', $namespace['path'] . 'src/', null, 'ModxApiTalk\\');

$modx->services->add('ModxApiTalk', function ($c) use ($modx) {
    return new ModxApiTalk\ModxApiTalk($modx);
});
// Автозагрузка PSR-4 классов из src/
spl_autoload_register(function ($class) use ($namespace) {
  $prefix = 'ModxApiTalk\\';
  if (strpos($class, $prefix) === 0) {
      $relativeClass = substr($class, strlen($prefix));
      $classPath = str_replace('\\', '/', $relativeClass);
      $filePath = $namespace['path'] . 'src/' . $classPath . '.php';

      if (file_exists($filePath)) {
          require_once $filePath;
      } else {
          $this->modx->log(modX::LOG_LEVEL_ERROR, '[ModxApiTalk] Class not found: ' . $filePath);
      }
  }
});

