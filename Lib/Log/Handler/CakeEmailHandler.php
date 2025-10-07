<?php

use Monolog\Handler\MailHandler;
use Monolog\Level;
use Monolog\Logger;

/**
 * CakeEmailHandler uses CakeEmail to send the emails.
 *
 * Use it like so:
 *
 * CakeLog::config('web', [
 *     'engine' => 'Monolog.MonologLogger',
 *     'channel' => 'web',
 *     'handlers' => [
 *         'CakeEmailHandler' => [
 *             "webmaster@domain.com",
 *             "ALERT: IMMEDIATE ACTION REQUIRED.",
 *             'default',
 *             'search' => CakePlugin::path('Monolog') . DS . 'Lib' . DS . 'Log' . DS . 'Handler',
 *         ],
 *     ],
 * ]);
 */
class CakeEmailHandler extends MailHandler
{
    protected $_to;
    protected $_subject;
    protected $_config;

    /**
     * @param array|string $to The receiver of the mail
     * @param string $subject The subject of the mail
     * @param string $from The CakeEmail configuration to use
     * @param Level|string|int $level The minimum logging level at which this handler will be triggered
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct(array|string $to, string $subject, string $config = 'default', int|Level|string $level = Logger::ERROR, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->_to = $to;
        $this->_subject = $subject;
        $this->_config = $config;
    }

    /**
     * @inheritDoc
     */
    public function send($content, array $records): void
    {
        $email = 'CakeEmail';
        if (Configure::check('Email.classname')) {
            $email = Configure::read('Email.classname');
        }
        $email::deliver($this->_to, $this->_subject, $content, $this->_config);
    }
}
