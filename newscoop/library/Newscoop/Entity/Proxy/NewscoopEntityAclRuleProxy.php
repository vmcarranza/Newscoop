<?php

namespace Newscoop\Entity\Proxy;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class NewscoopEntityAclRuleProxy extends \Newscoop\Entity\Acl\Rule implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    private function _load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    
    public function getId()
    {
        $this->_load();
        return parent::getId();
    }

    public function setType($type)
    {
        $this->_load();
        return parent::setType($type);
    }

    public function getType()
    {
        $this->_load();
        return parent::getType();
    }

    public function setRole(\Newscoop\Entity\Acl\Role $role)
    {
        $this->_load();
        return parent::setRole($role);
    }

    public function setResource(\Newscoop\Entity\Acl\Resource $resource = NULL)
    {
        $this->_load();
        return parent::setResource($resource);
    }

    public function getResource()
    {
        $this->_load();
        return parent::getResource();
    }

    public function getResourceId()
    {
        $this->_load();
        return parent::getResourceId();
    }

    public function setAction(\Newscoop\Entity\Acl\Action $action = NULL)
    {
        $this->_load();
        return parent::setAction($action);
    }

    public function getAction()
    {
        $this->_load();
        return parent::getAction();
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'type', 'role', 'resource', 'action');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields AS $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        
    }
}