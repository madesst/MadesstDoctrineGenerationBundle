# MadesstDoctrineGenerationBundle

## О бандле

MadesstDoctrineGenerationBundle вносит изменения в стандартный процесс генерации доктриной сущностей (entities) и
позволяет получить структуру классов как в пропеле: User extends Base/User, где весь сгенерированный код содержится
в базовом классе, а вы работаете с чистым и незахлмаленным классом.
Простой пример:

```php
// src/Company/SomeBundle/Entity/User.php
class User extends \Smartstart\SpecialBundle\Entity\Base\User
{
	public function getUsername()
	{
		return $this->getFirstname().' '.$this->getLastname();
	}
}
```

```php
// src/Company/SomeBundle/Entity/Base/User.php
class User
{
    /**
     * @var integer
     */
    protected $id;

    /**
     * @var string
     */
    protected $firstname;

    /**
     * @var string
     */
    protected $lastname;

    // ...
    // И так далее, обычный сгенерерированный доктриной класс
```


Бандл еще очень сырой, поэтому возможны отклонения в его поведении, прошу информировать меня о таких случаях

## Установка

Добавьте бандл в ваш `composer.json` (для symfony >=2.3):

```json
{
    "require": {
        "madesst/doctrine-generation-bundle": "1.1"
    }
}
```
или (для symfony 2.2):

```json
{
    "require": {
        "madesst/doctrine-generation-bundle": "1.0"
    }
}
```

или (для symfony 2.1):

```json
{
    "require": {
        "madesst/doctrine-generation-bundle": "0.9"
    }
}
```

И зарегистрируйте бандл в `app/AppKernel.php`, после SensioGeneratorBundle:

```php
// app/AppKernel.php
	public function registerBundles()
	{
		if (in_array($this->getEnvironment(), array('dev', 'test'))) {
			// ...
			$bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
			$bundles[] = new Madesst\DoctrineGenerationBundle\MadesstDoctrineGenerationBundle();
		}
	}
```

Теперь вам стал доступен дополнительный ключ --propel-style в консольных коммандах doctrine:generate:entities и doctrine:generate:entity,
который как раз и управляет способом генерации:

```bash
$ app/console doctrine:generate:entities СompanySomeBundle --propel-style
Generating entities for bundle "СompanySomeBundle"
  > backing up User.php to User.php~
  > generating Сompany\SomeBundle\Entity\Base\User
  > generating Сompany\SomeBundle\Entity\User
```

## License

Released under the MIT License, see LICENSE.
