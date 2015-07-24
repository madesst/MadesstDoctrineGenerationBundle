<?php
/**
 * Created by PhpStorm.
 * User: madesst
 * Date: 23.07.13
 * Time: 19:12
 */

namespace Madesst\DoctrineGenerationBundle\ORM\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata as BaseClassMetadata;

class ClassMetadata extends BaseClassMetadata
{
	public function getUserClassMetadata()
	{
		return new ClassMetadata($this->getName(), $this->namingStrategy);
	}

	public function modifyForBaseClass()
	{
		$basename = substr($this->getName(), strrpos($this->getName(), '\\') + 1);

		if (!$this->namespace)
		{
			$this->namespace = substr($this->getName(), 0, strrpos($this->getName(), '\\'));
		}

		$this->namespace = $this->namespace.'\Base';
		$this->name = $this->namespace.'\\'.$basename;
	}
}