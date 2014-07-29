<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

/**
 * Authentication adapter interface to be implemented by authentication methodes
 *
 * @author christophe massin
 * @package authLdap

 */

namespace oat\authLdap\model;

use core_kernel_users_Service;
use core_kernel_users_InvalidLoginException;
use oat\authLdap\model\LdapUser;
use oat\generisHard\models\hardsql\Exception;
use oat\oatbox\user\auth\LoginAdapter;
use Zend\Authentication\Adapter\Ldap;
use common_persistence_Manager;

/**
 * Adapter to authenticate users stored in the Ldap implementation
 *
 * @author Christophe Massin <christope@taotesting.com>
 *
 */
class LdapAdapter implements LoginAdapter
{
    /** @var  $username string */
    private $username;

    /** @var  $password string */
    private $password;

    /** @var $configuration array $configuration  */
    protected $configuration;

    /** @var $mapping array $mapping  */
    protected $mapping;

    /**
     * @var \Zend\Authentication\Adapter\Ldap
     */
    protected $adapter;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration) {
        $this->configuration = $configuration;

        $this->adapter = new Ldap();
        $this->adapter->setOptions($configuration['config']);
        $this->setMapping($configuration['mapping']);

    }

    /**
     * Set the credential
     *
     * @param string $login
     * @param string $password
     */
    public function setCredentials($login, $password){
        $this->username = $login;
        $this->password = $password;
    }

    public function authenticate() {


        $adapter = $this->getAdapter();

        $adapter->setUsername($this->getUsername());
        $adapter->setPassword($this->getPassword());
        $result = $adapter->authenticate();

        if($result->isValid()){

            $result = $adapter->getAccountObject();
            $params = get_object_vars($result);

            $user = new LdapUser($this->getMapping());

            $user->setUserRawParameters($params);
            return $user;

        } else {
            throw new core_kernel_users_InvalidLoginException();
        }


    }

    /**
     * @param \Zend\Authentication\Adapter\Ldap $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return \Zend\Authentication\Adapter\Ldap
     */
    public function getAdapter()
    {
        return $this->adapter;
    }


    /**
     * @param array $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }

    /**
     * @return array
     */
    public function getMapping()
    {
        return $this->mapping;
    }



    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }


}