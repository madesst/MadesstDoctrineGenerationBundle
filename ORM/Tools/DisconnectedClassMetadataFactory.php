<?php

namespace Madesst\DoctrineGenerationBundle\ORM\Tools;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory as BaseDisconnectedClassMetadataFactory;
use Madesst\DoctrineGenerationBundle\ORM\Mapping\ClassMetadata;

class DisconnectedClassMetadataFactory extends BaseDisconnectedClassMetadataFactory
{
	protected $em;

	public function setEntityManager(EntityManager $em)
	{
		parent::setEntityManager($em);
		$this->em = $em;
	}

	protected function newClassMetadataInstance($className)
	{
		return new ClassMetadata($className, $this->em->getConfiguration()->getNamingStrategy());
	}
}