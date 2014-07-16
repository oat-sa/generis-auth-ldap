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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

/**
 * Authentication user for key value db access
 *
 * @author christophe massin
 * @package authLdap

 */


namespace oat\authLdap\model;
use common_user_User;
use core_kernel_classes_Resource;
use core_kernel_classes_Property;
use common_Logger;
use SebastianBergmann\Exporter\Exception;

class LdapUser extends common_user_User {

    /** @var  array of configuration */
    protected $configuration;

    /**
     * @var array
     */
    protected $userRawParameters;

    /**
     * @var array
     */
    protected $userExtraParameters = array();

    /**
     * @var string
     */
    protected $identifier;

    /** @var  array $roles */
    protected $roles;

    /**
     * Array that contains the language code as a single string
     *
     * @var array
     */
    protected $languageUi = array(DEFAULT_LANG);

    /**
     * Array that contains the language code as a single string
     *
     * @var array
     */
    protected $languageDefLg = array(DEFAULT_LANG);

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
     * Sets the language URI
     *
     * @param string $languageDefLgUri
     */
    public function setLanguageDefLg($languageDefLgUri)
    {
        $languageResource = new core_kernel_classes_Resource($languageDefLgUri);

        $languageCode = $languageResource->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE));
        if($languageCode) {
            $this->languageDefLg = array((string)$languageCode);
        }

        return $this;
    }

    /**
     * Returns the language code
     *
     * @return array
     */
    public function getLanguageDefLg()
    {
        return $this->languageDefLg;
    }

    /**
     * @param array $userExtraParameters
     */
    public function setUserExtraParameters(array $userExtraParameters)
    {
        $this->userExtraParameters = $userExtraParameters;
    }

    /**
     * @return array
     */
    public function getUserExtraParameters()
    {
        return $this->userExtraParameters;
    }



    /**
     * @param rray $userRawParameters
     * @return AuthKeyValueUser
     */
    public function setUserRawParameters(array $userRawParameters)
    {
        $this->userRawParameters = $userRawParameters;

        return $this;
    }

    /**
     * @return array
     */
    public function getUserRawParameters()
    {
        return $this->userRawParameters;
    }


    /**
     * @param mixed $language
     */
    public function setLanguageUi($languageUri)
    {
        $languageResource = new core_kernel_classes_Resource($languageUri);

        $languageCode = $languageResource->getUniquePropertyValue(new core_kernel_classes_Property(RDF_VALUE));
        if($languageCode) {
            $this->languageUi = array((string)$languageCode);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getLanguageUi()
    {
        return $this->languageUi;
    }


    /**
     * @return string
     */
    public function getIdentifier(){
        return $this->identifier;
    }

    /**
     * @param $identifier
     * @return $this
     */
    public function setIdentifier($identifier){
        $this->identifier = $identifier;

        return $this;
    }


    /**
     * @param $property string
     * @return array|null
     */
    public function getPropertyValues($property)
    {
        $returnValue = null;

        $userParameters = $this->getUserRawParameters();

        if( !empty($userParameters) && array_key_exists($property, $userParameters))
        {
            switch ($property) {
                case PROPERTY_USER_DEFLG :
                    $returnValue = $this->getLanguageDefLg();
                    break;
                case PROPERTY_USER_UILG :
                    $returnValue = $this->getLanguageUi();
                    break;
                case PROPERTY_USER_ROLES :
                    $returnValue = $this->getRoles();
                    break;
                default:
                    $returnValue = array($userParameters[$property]);
            }
        } else {
            $extraParameters = $this->getUserExtraParameters();
            // the element has already been accessed
            if(!empty($extraParameters) && array_key_exists($property, $extraParameters)){
                if(!is_array($extraParameters[$property])){
                    $returnValue = array($extraParameters[$property]);
                } else {
                    $returnValue = $extraParameters[$property];
                }

            } else {
                // not already accessed, we are going to get it.
                $serviceUser = new AuthKeyValueUserService();
                $parameter = $serviceUser->getUserParameter($userParameters[PROPERTY_USER_LOGIN], $property);

                $config = $this->getConfiguration();
                if(isset($config['max_size_cached_element'])){
                    if( strlen(base64_encode(serialize($parameter))) < $config['max_size_cached_element'] ) {
                        $extraParameters[$property] = $parameter;
                        $this->setUserExtraParameters($extraParameters);
                    }
                } else {
                    throw new Exception('Missing configuration element max_sized_cached_element');
                }


                $returnValue = array($parameter);
            }

        }

        return $returnValue;
    }


    /**
     * Function that will refresh the parameters.
     */
    public function refresh() {
    }


    /**
     * @return array
     */
    public function getRoles() {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles ) {
        $this->roles = $roles;

        return $this;
    }

}