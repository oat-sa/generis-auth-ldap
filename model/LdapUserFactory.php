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

use oat\oatbox\Configurable;
use oat\taoTestTaker\models\CrudService;
use oat\generis\model\user\UserRdf;


class LdapUserFactory extends Configurable {

    public function createUser($rawData) {

        if (!isset($rawData['dn'])) {
            throw new \common_exception_InconsistentData('Missing DN for LDAP user');
        } else {
            $id = $rawData['dn'];
        }

        $data = array();
        $userdata = array();


        foreach ($this->getRules() as $property => $rule) {
            $data[$property] = $this->map($rule, $rawData);
            $userdata[$property] = $data[$property][0];
        }


        $taouser = null;

        // check if login already exists - Create if not, and add the delivery role!
        // $userService = ServiceManager::getServiceManager()->get("tao/UserService");
        // $userService = \tao_models_classes_UserService::singleton();
        // $userService = \core_kernel_users_Service::singleton();
        // $userService = tao_models_classes_UserService::singleton();

        if (! \core_kernel_users_Service::loginExists($userdata[PROPERTY_USER_LOGIN])) {
           $crudservice = CrudService::singleton();
           $taouser = $crudservice->CreateFromArray( $userdata );

        } else {

           // Retrieve the specified user.
           $taouser = \core_kernel_users_Service::getOneUser( $userdata[PROPERTY_USER_LOGIN] );
        }

        return new LdapUser($taouser->getUri(), $data);
    }

    public function map($propertyConfig, $rawData) {
        $data = array();
        switch ($propertyConfig['type']) {
            case 'value' :
                $data = $propertyConfig['value'];
                break;
            case 'attributeValue' :
                if (isset($rawData[$propertyConfig['attribute']])) {
                    $value = $rawData[$propertyConfig['attribute']];
                    $data = is_array($value) ? $value : array($value);
                }
                break;
//            case 'conditionalvalue' :
//                if (isset($rawData[$propertyConfig['attribute']]) &&
//                    isset($rawData[$propertyConfig['attributematch']) ) {
//                    // iterate raw data looking for attribute = attribute match
//                    // set data = value property if determined to be true.
//                }
//                break;
            case 'callback' :
                if (isset($rawData[$propertyConfig['attribute']])) {
                    $callback = $propertyConfig['callable'];
                    if (is_callable($callback)) {
                        $data = call_user_func($callback, $rawData[$propertyConfig['attribute']]);
                    }
                }
                break;
            default :
                throw new \common_exception_InconsistentData('Unknown mapping: '.$propertyConfig['type']);
        }
        return $data;
    }

    public function getRules() {
        $rules = self::getDefaultConfig();
        foreach ($this->getOptions() as $key => $value) {
            $rules[$key] = $value;
        }
        return $rules;
    }

    static public function getDefaultConfig()
    {
        return array(
            PROPERTY_USER_ROLES         => self::rawValue(INSTANCE_ROLE_DELIVERY)
            ,PROPERTY_USER_UILG         => self::rawValue(DEFAULT_LANG)
            ,PROPERTY_USER_DEFLG        => self::rawValue(DEFAULT_LANG)
            ,PROPERTY_USER_TIMEZONE     => self::rawValue(TIME_ZONE)
            ,PROPERTY_USER_MAIL         => self::attributeValue('mail')
            ,PROPERTY_USER_FIRSTNAME    => self::attributeValue('givenname')
            ,PROPERTY_USER_LASTNAME     => self::attributeValue('sn')
            ,PROPERTY_USER_LOGIN        => self::attributeValue('dn')
            ,PROPERTY_USER_PASSWORD     => self::rawValue(CrudService::INVALID_PASSWORD)
            ,RDFS_LABEL                 => self::attributeValue('mail')
        );
    }

    static protected function rawValue($value) {
        return array(
            'type' => 'value',
            'value' => array($value)
        );
    }

    static protected function attributeValue($attributeName) {
        return array(
            'type' => 'attributeValue',
            'attribute' => $attributeName
        );
    }

    static protected function callback($callable, $attributeName) {
        return array(
            'type' => 'callback',
            'callable' => $callable,
            'attribute' => $attributeName
        );
    }
}
