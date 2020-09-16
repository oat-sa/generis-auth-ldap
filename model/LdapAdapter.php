<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
y * as published by the Free Software Foundation; under version 2
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
    const OPTION_ADAPTER_CONFIG = 'config';

    const OPTION_USER_MAPPING = 'mapping';

    /** @var  $username string */
    private $username;

    /** @var  $password string */
    private $password;

    /** @var $configuration array $configuration  */
    protected $configuration;

    /**
     * @var \Zend\Authentication\Adapter\Ldap
     */
    protected $adapter;

    /**
     * Create an adapter from the configuration
     *
     * @param array $configuration
     * @return oat\authLdap\model\LdapAdapter
     */
    public static function createFromConfig(array $configuration) {
        $adapter = new self();
        $adapter->setOptions($configuration);
        return $adapter;
    }

    /**
     * Instantiates Zend Ldap adapter
     */
    public function __construct() {
        $this->adapter = new Ldap();
    }

    public function setOptions(array $options) {
        $this->configuration = $options;
        $this->adapter->setOptions($options['config']);
    }

    public function getOption($name) {
        return $this->configuration[$name];
    }

    public function hasOption($name) {
        return isset($this->configuration[$name]);
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


            $mapping = $this->hasOption(self::OPTION_USER_MAPPING)
                ? $this->getOption(self::OPTION_USER_MAPPING)
                : array();
            $factory = new LdapUserFactory($mapping);
            $user = $factory->createUser($params);

            return $user;

        } else {
            throw new core_kernel_users_InvalidLoginException('User "'.$this->getUsername().'" failed LDAP authentication.');
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
