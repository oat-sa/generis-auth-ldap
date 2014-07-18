generis-auth-ldap
=================

An LDAP implementation of the Tao user authentication

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

To enable them, you need to go to generis/common/conf/auth.conf.php and add these lines 

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
