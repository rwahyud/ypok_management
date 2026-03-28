<?php

class EntityManager_9a5be93 extends \Doctrine\ORM\EntityManager implements \ProxyManager\Proxy\VirtualProxyInterface
{
    private $valueHolder26a29 = null;
    private $initializer0e84a = null;
    private static $publicPropertiesc4ee7 = [
        
    ];
    public function getConnection()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getConnection', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getConnection();
    }
    public function getMetadataFactory()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getMetadataFactory', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getMetadataFactory();
    }
    public function getExpressionBuilder()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getExpressionBuilder', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getExpressionBuilder();
    }
    public function beginTransaction()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'beginTransaction', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->beginTransaction();
    }
    public function getCache()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getCache', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getCache();
    }
    public function transactional($func)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'transactional', array('func' => $func), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->transactional($func);
    }
    public function wrapInTransaction(callable $func)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'wrapInTransaction', array('func' => $func), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->wrapInTransaction($func);
    }
    public function commit()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'commit', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->commit();
    }
    public function rollback()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'rollback', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->rollback();
    }
    public function getClassMetadata($className)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getClassMetadata', array('className' => $className), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getClassMetadata($className);
    }
    public function createQuery($dql = '')
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'createQuery', array('dql' => $dql), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->createQuery($dql);
    }
    public function createNamedQuery($name)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'createNamedQuery', array('name' => $name), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->createNamedQuery($name);
    }
    public function createNativeQuery($sql, \Doctrine\ORM\Query\ResultSetMapping $rsm)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'createNativeQuery', array('sql' => $sql, 'rsm' => $rsm), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->createNativeQuery($sql, $rsm);
    }
    public function createNamedNativeQuery($name)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'createNamedNativeQuery', array('name' => $name), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->createNamedNativeQuery($name);
    }
    public function createQueryBuilder()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'createQueryBuilder', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->createQueryBuilder();
    }
    public function flush($entity = null)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'flush', array('entity' => $entity), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->flush($entity);
    }
    public function find($className, $id, $lockMode = null, $lockVersion = null)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'find', array('className' => $className, 'id' => $id, 'lockMode' => $lockMode, 'lockVersion' => $lockVersion), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->find($className, $id, $lockMode, $lockVersion);
    }
    public function getReference($entityName, $id)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getReference', array('entityName' => $entityName, 'id' => $id), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getReference($entityName, $id);
    }
    public function getPartialReference($entityName, $identifier)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getPartialReference', array('entityName' => $entityName, 'identifier' => $identifier), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getPartialReference($entityName, $identifier);
    }
    public function clear($entityName = null)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'clear', array('entityName' => $entityName), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->clear($entityName);
    }
    public function close()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'close', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->close();
    }
    public function persist($entity)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'persist', array('entity' => $entity), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->persist($entity);
    }
    public function remove($entity)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'remove', array('entity' => $entity), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->remove($entity);
    }
    public function refresh($entity)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'refresh', array('entity' => $entity), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->refresh($entity);
    }
    public function detach($entity)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'detach', array('entity' => $entity), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->detach($entity);
    }
    public function merge($entity)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'merge', array('entity' => $entity), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->merge($entity);
    }
    public function copy($entity, $deep = false)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'copy', array('entity' => $entity, 'deep' => $deep), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->copy($entity, $deep);
    }
    public function lock($entity, $lockMode, $lockVersion = null)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'lock', array('entity' => $entity, 'lockMode' => $lockMode, 'lockVersion' => $lockVersion), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->lock($entity, $lockMode, $lockVersion);
    }
    public function getRepository($entityName)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getRepository', array('entityName' => $entityName), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getRepository($entityName);
    }
    public function contains($entity)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'contains', array('entity' => $entity), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->contains($entity);
    }
    public function getEventManager()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getEventManager', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getEventManager();
    }
    public function getConfiguration()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getConfiguration', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getConfiguration();
    }
    public function isOpen()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'isOpen', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->isOpen();
    }
    public function getUnitOfWork()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getUnitOfWork', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getUnitOfWork();
    }
    public function getHydrator($hydrationMode)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getHydrator', array('hydrationMode' => $hydrationMode), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getHydrator($hydrationMode);
    }
    public function newHydrator($hydrationMode)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'newHydrator', array('hydrationMode' => $hydrationMode), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->newHydrator($hydrationMode);
    }
    public function getProxyFactory()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getProxyFactory', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getProxyFactory();
    }
    public function initializeObject($obj)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'initializeObject', array('obj' => $obj), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->initializeObject($obj);
    }
    public function getFilters()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'getFilters', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->getFilters();
    }
    public function isFiltersStateClean()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'isFiltersStateClean', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->isFiltersStateClean();
    }
    public function hasFilters()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'hasFilters', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return $this->valueHolder26a29->hasFilters();
    }
    public static function staticProxyConstructor($initializer)
    {
        static $reflection;
        $reflection = $reflection ?? new \ReflectionClass(__CLASS__);
        $instance   = $reflection->newInstanceWithoutConstructor();
        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $instance, 'Doctrine\\ORM\\EntityManager')->__invoke($instance);
        $instance->initializer0e84a = $initializer;
        return $instance;
    }
    protected function __construct(\Doctrine\DBAL\Connection $conn, \Doctrine\ORM\Configuration $config, \Doctrine\Common\EventManager $eventManager)
    {
        static $reflection;
        if (! $this->valueHolder26a29) {
            $reflection = $reflection ?? new \ReflectionClass('Doctrine\\ORM\\EntityManager');
            $this->valueHolder26a29 = $reflection->newInstanceWithoutConstructor();
        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $this, 'Doctrine\\ORM\\EntityManager')->__invoke($this);
        }
        $this->valueHolder26a29->__construct($conn, $config, $eventManager);
    }
    public function & __get($name)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, '__get', ['name' => $name], $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        if (isset(self::$publicPropertiesc4ee7[$name])) {
            return $this->valueHolder26a29->$name;
        }
        $realInstanceReflection = new \ReflectionClass('Doctrine\\ORM\\EntityManager');
        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder26a29;
            $backtrace = debug_backtrace(false, 1);
            trigger_error(
                sprintf(
                    'Undefined property: %s::$%s in %s on line %s',
                    $realInstanceReflection->getName(),
                    $name,
                    $backtrace[0]['file'],
                    $backtrace[0]['line']
                ),
                \E_USER_NOTICE
            );
            return $targetObject->$name;
        }
        $targetObject = $this->valueHolder26a29;
        $accessor = function & () use ($targetObject, $name) {
            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();
        return $returnValue;
    }
    public function __set($name, $value)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, '__set', array('name' => $name, 'value' => $value), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        $realInstanceReflection = new \ReflectionClass('Doctrine\\ORM\\EntityManager');
        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder26a29;
            $targetObject->$name = $value;
            return $targetObject->$name;
        }
        $targetObject = $this->valueHolder26a29;
        $accessor = function & () use ($targetObject, $name, $value) {
            $targetObject->$name = $value;
            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();
        return $returnValue;
    }
    public function __isset($name)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, '__isset', array('name' => $name), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        $realInstanceReflection = new \ReflectionClass('Doctrine\\ORM\\EntityManager');
        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder26a29;
            return isset($targetObject->$name);
        }
        $targetObject = $this->valueHolder26a29;
        $accessor = function () use ($targetObject, $name) {
            return isset($targetObject->$name);
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = $accessor();
        return $returnValue;
    }
    public function __unset($name)
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, '__unset', array('name' => $name), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        $realInstanceReflection = new \ReflectionClass('Doctrine\\ORM\\EntityManager');
        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder26a29;
            unset($targetObject->$name);
            return;
        }
        $targetObject = $this->valueHolder26a29;
        $accessor = function () use ($targetObject, $name) {
            unset($targetObject->$name);
            return;
        };
        $backtrace = debug_backtrace(true, 2);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $accessor();
    }
    public function __clone()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, '__clone', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        $this->valueHolder26a29 = clone $this->valueHolder26a29;
    }
    public function __sleep()
    {
        $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, '__sleep', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
        return array('valueHolder26a29');
    }
    public function __wakeup()
    {
        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $this, 'Doctrine\\ORM\\EntityManager')->__invoke($this);
    }
    public function setProxyInitializer(\Closure $initializer = null) : void
    {
        $this->initializer0e84a = $initializer;
    }
    public function getProxyInitializer() : ?\Closure
    {
        return $this->initializer0e84a;
    }
    public function initializeProxy() : bool
    {
        return $this->initializer0e84a && ($this->initializer0e84a->__invoke($valueHolder26a29, $this, 'initializeProxy', array(), $this->initializer0e84a) || 1) && $this->valueHolder26a29 = $valueHolder26a29;
    }
    public function isProxyInitialized() : bool
    {
        return null !== $this->valueHolder26a29;
    }
    public function getWrappedValueHolderValue()
    {
        return $this->valueHolder26a29;
    }
}
