<?php

namespace O2TI\FormattingCustomerBrazilian\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use O2TI\FormattingCustomerBrazilian\Model\Console\Formatting;

class BrazilianFormatting extends Command
{
    /**
     * Command for bin.
     */
    public const COMMAND = 'o2ti:customer:brazilian_formatting';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var Formatting
     */
    protected $format;

    /**
     * Construct.
     *
     * @param State      $state
     * @param Formatting $format
     */
    public function __construct(
        State $state,
        Formatting $format
    ) {
        $this->state = $state;
        $this->format = $format;
        parent::__construct();
    }

    /**
     * Configure.
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName(self::COMMAND);
        $desc = 'Formatting legacy clients for Brazilian standards';
        $this->setDescription($desc);
    }

    /**
     * Execute.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $this->format->setOutput($output);
        
        return $this->format->execute();
    }
}
