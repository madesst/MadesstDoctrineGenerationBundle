<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madesst\DoctrineGenerationBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Types\Type;
use Madesst\DoctrineGenerationBundle\Generator\DoctrineEntityGenerator;

/**
 * Initializes a Doctrine entity inside a bundle.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class GenerateDoctrineEntityCommand extends \Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineEntityCommand
{
	protected $input;

	protected function configure()
	{
		parent::configure();
		$this->addOption('propel-style', null, InputOption::VALUE_NONE, '.');
	}

	/**
	 * @throws \InvalidArgumentException When the bundle doesn't end with Bundle (Example: "Bundle/MySampleBundle")
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		parent::execute($input, $output);
	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{
		$this->input = $input;
		parent::interact($input, $output);
	}

	protected function createGenerator()
	{
		$generator = new DoctrineEntityGenerator($this->getContainer()->get('filesystem'), $this->getContainer()->get('doctrine'));
		$generator->setPropelStyle($this->input->getOption('propel-style'));

		return $generator;
	}
}
