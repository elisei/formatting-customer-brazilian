<?php

namespace O2TI\FormattingCustomerBrazilian\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * Logging level for custom logger.
     *
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * Custom File name.
     *
     * @var string
     */
    protected $fileName = '/var/log/formatting_legacy_brazilian_data.log';
}
