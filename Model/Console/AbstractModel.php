<?php
/**
 * PagBank Payment Magento Module.
 *
 * Copyright © 2023 PagBank. All rights reserved.
 *
 * @author    Bruno Elisei <brunoelisei@o2ti.com>
 * @license   See LICENSE for license details.
 */

namespace O2TI\FormattingCustomerBrazilian\Model\Console;

use O2TI\FormattingCustomerBrazilian\Logger\Logger;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractModel
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Output.
     *
     * @param OutputInterface $output
     *
     * @return void
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Console Write.
     *
     * @param string $text
     *
     * @return void
     */
    protected function write(string $text)
    {
        if ($this->output instanceof OutputInterface) {
            $this->output->write($text);
        }
    }

    /**
     * Console WriteLn.
     *
     * @param string $text
     *
     * @return void
     */
    protected function writeln($text)
    {
        if ($this->output instanceof OutputInterface) {
            $this->output->writeln($text);
        }

        $log = '/<(info|error)>(.*?)<\/\1>/s';
        if (preg_match_all($log, $text, $matches)) {
            foreach ($matches[2] as $match) {
                $this->logger->info($match);
            }
        }
    }
}
