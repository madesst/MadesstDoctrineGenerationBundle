<?php
/**
 * Created by PhpStorm.
 * User: madesst
 * Date: 24.07.13
 * Time: 14:13
 */

namespace Madesst\DoctrineGenerationBundle\Command;

use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineEntityGenerator as BaseDoctrineEntityGenerator;
use Madesst\DoctrineGenerationBundle\Mapping\ClassMetadata;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bridge\Doctrine\RegistryInterface;

class DoctrineEntityGenerator extends BaseDoctrineEntityGenerator
{
	private $filesystem;
	private $registry;

	protected $propel_style = false;

	public function __construct(Filesystem $filesystem, RegistryInterface $registry)
	{
		$this->filesystem = $filesystem;
		$this->registry = $registry;
		parent::__construct($filesystem, $registry);
	}

	public function setPropelStyle($propel_style)
	{
		$this->propel_style = $propel_style;
	}

	public function generate(BundleInterface $bundle, $entity, $format, array $fields, $withRepository)
	{
		// configure the bundle (needed if the bundle does not contain any Entities yet)
		$config = $this->registry->getEntityManager(null)->getConfiguration();
		$config->setEntityNamespaces(array_merge(
			array($bundle->getName() => $bundle->getNamespace().'\\Entity'),
			$config->getEntityNamespaces()
		));

		$entityClass = $this->registry->getEntityNamespace($bundle->getName()).'\\'.$entity;
		$entityPath = $bundle->getPath().'/Entity/'.str_replace('\\', '/', $entity).'.php';
		if (file_exists($entityPath)) {
			throw new \RuntimeException(sprintf('Entity "%s" already exists.', $entityClass));
		}

		$class = new ClassMetadata($entityClass);
		if ($withRepository) {
			$class->customRepositoryClassName = $entityClass.'Repository';
		}
		$class->mapField(array('fieldName' => 'id', 'type' => 'integer', 'id' => true));
		$class->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_AUTO);
		foreach ($fields as $field) {
			$class->mapField($field);
		}

		$entityGenerator = $this->getEntityGenerator();
		if ('annotation' === $format) {
			$entityGenerator->setGenerateAnnotations(true);
			$mappingPath = $mappingCode = false;
		} else {
			$cme = new ClassMetadataExporter();
			$exporter = $cme->getExporter('yml' == $format ? 'yaml' : $format);
			$mappingPath = $bundle->getPath().'/Resources/config/doctrine/'.str_replace('\\', '.', $entity).'.orm.'.$format;

			if (file_exists($mappingPath)) {
				throw new \RuntimeException(sprintf('Cannot generate entity when mapping "%s" already exists.', $mappingPath));
			}

			$mappingCode = $exporter->exportClassMetadata($class);
			$entityGenerator->setGenerateAnnotations(false);
		}

		$this->generateEntityClass($entityGenerator, $class, $bundle);

		if ($mappingPath) {
			$this->filesystem->mkdir(dirname($mappingPath));
			file_put_contents($mappingPath, $mappingCode);
		}

		if ($withRepository) {
			$path = $bundle->getPath().str_repeat('/..', substr_count(get_class($bundle), '\\'));
			$this->getRepositoryGenerator()->writeEntityRepositoryClass($class->customRepositoryClassName, $path);
		}
	}

	protected function generateEntityClass(EntityGenerator $entityGenerator, ClassMetadata $class, BundleInterface $bundle)
	{
		$bundle_namespace = str_replace('\\', DIRECTORY_SEPARATOR, $bundle->getNamespace());
		$entityDir = str_replace(DIRECTORY_SEPARATOR.$bundle_namespace, '', $bundle->getPath());

		if($this->propel_style)
		{
			$user_m = $class->getUserClassMetadata();

			$class->modifyForBaseClass();
			$entityGenerator->setRegenerateEntityIfExists(false);
			$entityGenerator->setUpdateEntityIfExists(true);
			$entityGenerator->generate(array($class), $entityDir);

			$entityGenerator->setClassToExtend($class->getName());
			$entityGenerator->setUpdateEntityIfExists(true);
			$entityGenerator->setRegenerateEntityIfExists(false);

			$entityGenerator->generate(array($user_m), $entityDir);
			$entityGenerator->setClassToExtend('');
		}
		else
		{
			$entityGenerator->generate(array($class), $entityDir);
		}
	}

	protected function getEntityGenerator()
	{
		$entityGenerator = parent::getEntityGenerator();
		$entityGenerator->setFieldVisibility(EntityGenerator::FIELD_VISIBLE_PROTECTED);
		return $entityGenerator;
	}
}