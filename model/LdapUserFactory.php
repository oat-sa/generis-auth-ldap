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

class LdapUserFactory extends Configurable {
    
    public function createUser($rawData) {
        
        if (!isset($rawData['dn'])) {
            throw new \common_exception_InconsistentData('Missing DN for LDAP user');
        } else {
            $id = $rawData['dn'];
        }
        
        $data = array();
        foreach ($this->getRules() as $property => $rule) {
            $data[$property] = $this->map($rule, $rawData);
        }
        
        return new LdapUser($id, $data);
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
            ,PROPERTY_USER_FIRSTNAME    => self::attributeValue('givenName')
            ,PROPERTY_USER_LASTNAME     => self::attributeValue('sn')
            ,RDFS_LABEL                 => self::attributeValue('displayName')
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
