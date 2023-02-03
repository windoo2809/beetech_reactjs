<?php

namespace App\Dao\Log;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Processor\IntrospectionProcessor;
use Illuminate\Support\Facades\Auth;

class LogFormatter {
  private $dateFormat = 'Y-m-d H:i:s.v';

  public function __invoke($logger) {
    $timestamp = "[%datetime%]";
    $level = "[%level_name%]";
    $request = \Request::method() ." ". \Request::path();
    $user = "user:%extra.userid%";
    $message = "%message%";
    $function = "%extra.function%() in %extra.class%::class";

    $format = $timestamp ." ". $level ." ". $request ." ". $user ." ". $message ." @". $function .PHP_EOL;
    $lineFormatter = new LineFormatter($format, $this->dateFormat, true, true);

    $ip = new IntrospectionProcessor(Logger::DEBUG, ['Illuminate\\']);

    foreach ($logger->getHandlers() as $handler) {
      $handler->setFormatter($lineFormatter);
      $handler->pushProcessor($ip);
      $handler->pushProcessor([$this, 'addExtraFields']);
    }
  }

  public function addExtraFields(array $record) {
      $user = Auth::user();
      $record['extra']['userid'] = $user->user_id ?? "---";
      return $record;
  }
}  
