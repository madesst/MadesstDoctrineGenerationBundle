<?php
/**
 * Created by PhpStorm.
 * User: madesst
 * Date: 23.07.13
 * Time: 19:16
 */

namespace Madesst\DoctrineGenerationBundle\Mapping;

use Doctrine\Bundle\DoctrineBundle\Mapping\ClassMetadataCollection;
use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory as BaseDisconnectedMetadataFactory;

class DisconnectedMetadataFactory extends BaseDisconnectedMetadataFactory
{
	protected function getClassMetadataFactoryClass()
	{
		return 'Madesst\\DoctrineGenerationBundle\\ORM\\Tools\\DisconnectedClassMetadataFactory';
	}

	/**
	 * Find and configure path and namespace for the metadata collection.
	 *
	 * @param ClassMetadataCollection $metadata
	 * @param string|null             $path
	 *
	 * @throws \RuntimeException When unable to determine the path
	 */
	public function findNamespaceAndPathForMetadata(ClassMetadataCollection $metadata, $path = null)
	{
		$all = $metadata->getMetadata();
		if (class_exists($all[0]->name, false)) {
			$r = new \ReflectionClass($all[0]->name);
			$path = $this->getBasePathForClass($r->getName(), $r->getNamespaceName(), dirname($r->getFilename()));
		} elseif (!$path) {
			throw new \RuntimeException(sprintf('Unable to determine where to save the "%s" class (use the --path option).', $all[0]->name));
		}

		$metadata->setPath($path);
		$metadata->setNamespace(isset($r) ? $r->getNamespaceName() : $all[0]->name);
	}

	/**
	 * Get a base path for a class
	 *
	 * @param string $name      class name
	 * @param string $namespace class namespace
	 * @param string $path      class path
	 *
	 * @return string
	 * @throws \RuntimeException When base path not found
	 */
	private function getBasePathForClass($name, $namespace, $path)
	{
		$namespace = str_replace('\\', '/', $namespace);
		$search = str_replace('\\', '/', $path);
		$destination = str_replace('/' . $namespace, '', $search, $c);

		if ($c != 1) {
			throw new \RuntimeException(sprintf('Can\'t find base path for "%s" (path: "%s", destination: "%s").', $name, $path, $destination));
		}

		return $destination;
	}
}