<?php

App::uses('BaseLog', 'Log/Engine');
App::uses('String', 'Utility');

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;

class MonologLog extends BaseLog
{
    public $defaults = [
        'channel' => 'monolog',
        'handlers' => [],
        'processors' => [],
    ];

    /**
     * @param array $config
     * @throws Exception
     */
    public function __construct($config = [])
    {
        parent::__construct(array_merge($this->defaults, $config));

        if (!class_exists('Monolog\Logger')) {
            throw new Exception('Missing the monolog/monolog composer package.');
        }

        $this->log = new Logger($this->_config['channel']);
        $this->__push($this->log, $this->_config['handlers']);
        $this->__push($this->log, $this->_config['processors'], 'Processor');
    }

    /**
     * @param string|int $type
     * @param string $message
     * @return void
     */
    public function write($type, $message)
    {
        // Support both Monolog 2.x (Logger constants) and 3.x (Level enum)
        if (class_exists('Monolog\Level')) {
            // Monolog 3.x
            $levels = [
                Level::Debug->value => 'debug',
                Level::Info->value => 'info',
                Level::Notice->value => 'notice',
                Level::Warning->value => 'warning',
                Level::Error->value => 'error',
                Level::Critical->value => 'critical',
                Level::Alert->value => 'alert',
                Level::Emergency->value => 'emergency',
            ];
        } else {
            // Monolog 2.x
            $levels = [
                Logger::DEBUG => 'debug',
                Logger::INFO => 'info',
                Logger::NOTICE => 'notice',
                Logger::WARNING => 'warning',
                Logger::ERROR => 'error',
                Logger::CRITICAL => 'critical',
                Logger::ALERT => 'alert',
                Logger::EMERGENCY => 'emergency',
            ];
        }

        if (is_numeric($type)) {
            if (isset($levels[$type])) {
                $type = $levels[$type];
            } else {
                $type = 'error'; // Cake's default level.
            }
        }

        $this->log->$type($message);
    }

    /**
     * @param Logger|FormatterInterface|ProcessorInterface|HandlerInterface $object
     * @param array $list
     * @param string $type
     * @return void
     */
    private function __push(
        Logger|FormatterInterface|ProcessorInterface|HandlerInterface $object,
        array $list,
        string $type = 'Handler',
    ): void {
        if (empty($list)) {
            if ($type === 'Handler') {
                $list = ['Stream' => [LOGS . 'monolog.log']];
            }
        }

        foreach ($list as $name => $params) {
            if (is_numeric($name)) {
                $name = $params;
                $params = [];
            }

            $this->__run($object, $name, $type, $params);
        }
    }

    /**
     * @param Logger|FormatterInterface|ProcessorInterface|HandlerInterface $object
     * @param string $name
     * @param string $type
     * @param array $params
     * @return void
     * @throws ReflectionException
     */
    private function __run(
        Logger|FormatterInterface|ProcessorInterface|HandlerInterface $object,
        string $name,
        string $type,
        array $params,
    ): void {
        $extras = ['formatters', 'processors'];

        $class = $name;
        if (!str_contains($class, $type)) {
            $class = "\Monolog\\$type\\$name$type";
        } elseif (isset($params['search'])) {
            if (!str_contains($params['search'], '.php')) {
                $params['search'] .= DS . $class . '.php';
            }
            require_once $params['search'];
            unset($params['search']);
        }

        if ($type === 'Handler') {
            foreach ($extras as $k) {
                if (isset($params[$k])) {
                    ${$k} = $params[$k];
                    unset($params[$k]);
                }
            }
        }

        $method = "push$type";
        if ($type === 'Formatter') {
            $method = 'setFormatter';
        }

        $params = array_values($params);

        $classReflector = new ReflectionClass($class);
        /** @var FormatterInterface|ProcessorInterface|HandlerInterface $_class */
        $_class = $classReflector->newInstanceArgs($params);
        $object->$method($_class);

        foreach ($extras as $k) {
            if (!empty(${$k})) {
                $this->__push($_class, (array)${$k}, ucfirst(substr($k, 0, strlen($k) - 1)));
            }
        }
    }
}
