<?php
App::uses('MonologLog', 'Monolog.Log/Engine');

class MonologLogTest extends CakeTestCase
{
    public $logs = null;

    public function setUp(): void
    {
        $this->logs = LOGS;
        $this->rotate = sprintf('rotate-%s-%s-%s', date('Y'), date('m'), date('d'));
        $this->tearDown();
    }

    public function tearDown(): void
    {
        $files = [
            'error',
            'monolog',
            $this->rotate,
        ];
        foreach ($files as $file) {
            if (file_exists($this->logs . $file . '.log')) {
                unlink($this->logs . $file . '.log');
            }
        }
    }

    public function testWritingWithDefaultHandler(): void
    {
        $filename = $this->logs . 'monolog.log';
        $log = new MonologLog();
        $log->write('warning', 'Test warning');
        $this->assertTrue(file_exists($filename));

        $result = file_get_contents($filename);
        $this->assertMatchesRegularExpression('/^\[2[0-9]{3}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]+[+-][0-9]{2}:[0-9]{2}\] monolog\.WARNING: Test warning \[\] \[\]/', $result);
    }

    public function testWritingWithCustomHandlers(): void
    {
        $options = [
            'channel' => 'database',
            'handlers' => [
                'Stream' => [$this->logs . 'error.log'],
                'RotatingFile' => [$this->logs . 'rotate.log', 0, 400, false],
            ],
            'processors' => ['Web'],
        ];

        $log = new MonologLog($options);

        $log->write('warning', 'Test warning');
        $this->assertTrue(file_exists($this->logs . 'error.log'));
        $this->assertFalse(file_exists($this->logs . $this->rotate . '.log'));

        $this->tearDown();

        $log->write('critical', 'Test critical');
        $this->assertFalse(file_exists($this->logs . 'error.log'));
        $this->assertTrue(file_exists($this->logs . $this->rotate . '.log'));
    }

    public function testWritingWithSimilarConfigThanCake(): void
    {
        $options = [
            'channel' => 'app',
            'handlers' => [
                'Stream' => [
                    $this->logs . 'error.log',
                    'formatters' => [
                        'Line' => ["%datetime% %level_name%: %message%\n"],
                    ],
                ],
            ],
        ];

        $log = new MonologLog($options);

        $log->write('warning', 'Test warning');
        $this->assertTrue(file_exists($this->logs . 'error.log'));

        $result = file_get_contents($this->logs . 'error.log');
        $this->assertMatchesRegularExpression('/^2[0-9]{3}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}\.[0-9]+[+-][0-9]{2}:[0-9]{2} WARNING: Test warning/', $result);
    }
}
