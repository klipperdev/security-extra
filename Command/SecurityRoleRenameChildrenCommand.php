<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\SecurityExtra\Command;

use Klipper\Component\SecurityExtra\Batch\RoleRename;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for rename a role in users, groups and organization users.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class SecurityRoleRenameChildrenCommand extends Command
{
    /**
     * @var RoleRename
     */
    private $batch;

    /**
     * Constructor.
     */
    public function __construct(RoleRename $batch)
    {
        parent::__construct();

        $this->batch = $batch;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('security:role:rename:children')
            ->setDescription('Rename a role in users, groups and organization users.')
            ->addArgument('old-name', InputArgument::REQUIRED, 'The old name of role')
            ->addArgument('new-name', InputArgument::REQUIRED, 'The new name of role')
            ->addArgument('organization-id', InputArgument::OPTIONAL, 'The organization id of role')
            ->addOption('batch', null, InputOption::VALUE_REQUIRED, 'The batch size')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $old = $input->getArgument('old-name');
        $new = $input->getArgument('new-name');
        $orgId = $input->getArgument('organization-id');
        $batchSize = $input->getOption('batch');

        $res = $this->batch->rename($old, $new, $orgId, $batchSize);

        if (OutputInterface::VERBOSITY_VERBOSE === $output->getVerbosity()) {
            $msg = sprintf('Renamed role "%s" by "%s"', $old, $new);

            if (null !== $orgId) {
                $msg .= sprintf(' for organization id "%s"', $orgId);
            }

            $msg .= ': '.($res->isValid() ? '<info>SUCCESS</info>' : '<error>ERROR</error>');

            $output->writeln($msg);
        }
    }
}
