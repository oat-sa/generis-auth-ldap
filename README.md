generis-auth-ldap
=================

An LDAP implementation of the Tao 3.0 user authentication

Requirement
=====================
In order to use this system, you need to have an ldap server installed. It should have user in it. 
Test have been maded with openldap. 
I recommend a graphical client to use with, like phpldap admin 
You can correct the bug of the 1.2.2-5ubuntu1 with the following process : 
http://forums.debian.net/viewtopic.php?f=5&t=111508 




Installation 
============================

This system can be added to a projet as a library. You need to add this parameter to your composer.json 

    "minimum-stability" : "dev",
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/oat-sa/generis-auth-ldap"
        }
    ],
    "require": {
        "oat-sa/generis-auth-ldap": "*"
    },

Once it's done, run a composer update. 

------------------------------

To enable them, you need to go to /config/generis/auth.conf.php and add these lines 

    array(
        'driver' => 'oat\authLdap\model\LdapAdapter',
        'config' => array(
            array(
                'host' => '127.0.0.1',
                'accountDomainName' => 'test.com',
                'username' => 'cn=admin,dc=test,dc=com',
                'password' => 'admin',
                'baseDn' => 'OU=organisation,dc=test,dc=com',
                'bindRequiresDn' => 'true',
            )
        )
    ),

here the domain is test.com All the parameters are in a separate dc in ldap

These are the configuration of the connection to the ldap server. 

Then the login will try to use this library. 

Configuration
============================

By default and LDAP user will be considered a test-taker belonging to no group.

The following attributes will be taken from LDAP and mapped to TAO properties by default:

* 'mail' as PROPERTY_USER_MAIL
* 'givenName' as PROPERTY_USER_FIRSTNAME
* 'sn' as PROPERTY_USER_LASTNAME
* 'displayName' as RDFS_LABEL

However there are several ways to enhance or override this default behaviour:

------------------------------

To hardcode one of the user properties, you would need to add a mapping of the type 'value' to the configuration:

    array(
        'driver' => 'oat\authLdap\model\LdapAdapter',
        'config' => SEE_ABOVE
        'mapping' => array(
            'http://www.tao.lu/Ontologies/TAOGroup.rdf#member' => array(
                'type' => 'value',
                'value' => array('http://localnamespace.com/install#i123456789')
            )
        );
    ),

This example would set the group membership of all users loging in to a group identified by the id http://localnamespace.com/install#i123456789
    
------------------------------

Alternatively if you want to take over a value of an LDAP attribute you would add a mapping of type 'attributeValue'

    array(
        'driver' => 'oat\authLdap\model\LdapAdapter',
        'config' => SEE_ABOVE
        'mapping' => array(
            'http://www.tao.lu/Ontologies/TAOGroup.rdf#member' => array(
                'type' => 'value',
                'value' => array('http://localnamespace.com/install#i123456789')
            ),
            'http://www.w3.org/2000/01/rdf-schema#label' => array(
                'type' => 'attributeValue',
                'attribute' => 'username'
            )
        );
    ),
    
This would use the value of the LDAP attribute 'username' as label for the user.

------------------------------

For more advanced cases there is the type 'callback' which allows you to programmatically enhance the mapping of the LDAP attributes to the TAO properties. See oat\authLdap\model\LdapUserFactory for details.
