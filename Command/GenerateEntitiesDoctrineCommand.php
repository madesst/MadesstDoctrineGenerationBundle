<?php

/*
 * This file is part of the Doctrine Bundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project, Benjamin Eberlei <kontakt@beberlei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Madesst\DoctrineGenerationBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\GenerateEntitiesDoctrineCommand as BaseGenerateEntitiesDoctrineCommand;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\EntityRepositoryGenerator;
use Madesst\DoctrineGenerationBundle\Mapping\DisconnectedMetadataFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate entity classes from mapping information
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class GenerateEntitiesDoctrineCommand extends BaseGenerateEntitiesDoctrineCommand
{
	protected function configure()
	{
		parent::configure();
		$this->addOption('propel-style', null, InputOption::VALUE_NONE, '.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

		try {
			$bundle = $this->getApplication()->getKernel()->getBundle($input->getArgument('name'));

			$output->writeln(sprintf('Generating entities for bundle "<info>%s</info>"', $bundle->getName()));
			$metadata = $manager->getBundleMetadata($bundle);
		} catch (\InvalidArgumentException $e) {
			$name = strtr($input->getArgument('name'), '/', '\\');

			if (false !== $pos = strpos($name, ':')) {
				$name = $this->getContainer()->get('doctrine')->getEntityNamespace(substr($name, 0, $pos)).'\\'.substr($name, $pos + 1);
			}

			if (class_exists($name)) {
				$output->writeln(sprintf('Generating entity "<info>%s</info>"', $name));
				$metadata = $manager->getClassMetadata($name, $input->getOption('path'));
			} else {
				$output->writeln(sprintf('Generating entities for namespace "<info>%s</info>"', $name));
				$metadata = $manager->getNamespaceMetadata($name, $input->getOption('path'));
			}
		}

		$generator = $this->getEntityGenerator();

		$backupExisting = !$input->getOption('no-backup');
		$generator->setBackupExisting($backupExisting);

		$repoGenerator = new EntityRepositoryGenerator();
		foreach ($metadata->getMetadata() as $m) {
			if ($backupExisting) {
				$basename = substr($m->name, strrpos($m->name, '\\') + 1);
				$output->writeln(sprintf('  > backing up <comment>%s.php</comment> to <comment>%s.php~</comment>', $basename, $basename));
			}
			// Getting the metadata for the entity class once more to get the correct path if the namespace has multiple occurrences
			try {
				$entityMetadata = $manager->getClassMetadata($m->getName(), $input->getOption('path'));
			} catch (\RuntimeException $e) {
				// fall back to the bundle metadata when no entity class could be found
				$entityMetadata = $metadata;
			}

			if ($input->getOption('propel-style'))
			{
				$user_m = $m->getUserClassMetadata();

				$m->modifyForBaseClass();
				$generator->setBackupExisting(false);
				$generator->setRegenerateEntityIfExists(false);
				$generator->setUpdateEntityIfExists(true);
				$output->writeln(sprintf('  > generating <comment>%s</comment>', $m->name));
				$generator->generate(array($m), $entityMetadata->getPath());

				$generator->setClassToExtend($m->getName());
				$generator->setBackupExisting($backupExisting);
				$generator->setUpdateEntityIfExists(true);
				$generator->setRegenerateEntityIfExists(false);

				$output->writeln(sprintf('  > generating <comment>%s</comment>', $user_m->name));
				$generator->generate(array($user_m), $entityMetadata->getPath());
				$generator->setClassToExtend('');
			} else
			{
				$output->writeln(sprintf('  > generating <comment>%s</comment>', $m->name));
				$generator->generate(array($m), $entityMetadata->getPath());
			}

			if ($m->customRepositoryClassName && false !== strpos($m->customRepositoryClassName, $metadata->getNamespace())) {
				$repoGenerator->writeEntityRepositoryClass($m->customRepositoryClassName, $metadata->getPath());
			}
		}
	}

	protected function getEntityGenerator()
	{
		$entityGenerator = parent::getEntityGenerator();
		$entityGenerator->setFieldVisibility(EntityGenerator::FIELD_VISIBLE_PROTECTED);
		return $entityGenerator;
	}
}
