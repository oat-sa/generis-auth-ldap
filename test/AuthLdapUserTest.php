<?php
/**
 * Created by PhpStorm.
 * User: christophemassin
 * Date: 4/07/14
 * Time: 10:49
 */

namespace oat\authLdap\test;

use oat\authLdap\model\LdapUser;
use GenerisPhpUnitTestRunner;
use oat\generis\model\GenerisRdf;

require_once dirname(__FILE__) . '/../../generis/test/GenerisPhpUnitTestRunner.php';

class AuthKeyValueUserTest extends GenerisPhpUnitTestRunner {

    /** @var  $user AuthKeyValueUser */
    protected $user;

    public function setUp() {
        $this->user = new LdapUser();

        $this->user->setUserRawParameters(
            array(
                'preferredlanguage' => 'en',
                'mail' => 'mail@user.test',
                'displayname' => 'toto is back'
            )
        );

    }

    public function tearDown(){
        $this->user = null;
    }


    /**
     * @cover AuthKeyValueUser::setLanguageUi
     * @cover AuthKeyValueUser::getLanguageUi
     * @cover AuthKeyValueUser::setLanguageDefLg
     * @cover AuthKeyValueUser::getLanguageDefLg
     */
    public function testLanguage()
    {
        $languageProperty = 'en';

        $this->user->setLanguageUi($languageProperty);
        $this->user->setLanguageDefLg($languageProperty);

        $langUi = $this->user->getLanguageUi();
        $langDefLg = $this->user->getLanguageDefLg();

        $this->assertNotEmpty($langUi);
        $this->assertNotEmpty($langDefLg);
        $this->assertInternalType('array', $langUi);
        $this->assertInternalType('array', $langDefLg);
        $this->assertEquals(array('en-US'), $this->user->getLanguageUi());
        $this->assertEquals(array('en-US'), $this->user->getLanguageDefLg());
    }


    /**
     * @cover AuthKeyValueUser::getPropertyValues
     */
    public function testPropertyValue(){

        $this->assertEquals(array(0 => 'en-US'), $this->user->getPropertyValues(GenerisRdf::PROPERTY_USER_DEFLG));
        $this->assertEquals(array(0 => 'en-US'), $this->user->getPropertyValues(GenerisRdf::PROPERTY_USER_UILG));

    }


    /**
     * @cover AuthKeyValueUser::setRoles
     * @cover AuthKeyValueUser::getRoles
     */
    public function testRoles()
    {
        $this->user->setRoles(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'));
        $this->assertEquals(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'), $this->user->getRoles());
        $this->assertEquals(array('http://www.tao.lu/Ontologies/TAO.rdf#DeliveryRole'), $this->user->getPropertyValues(GenerisRdf::PROPERTY_USER_ROLES));
    }


    /**
     * @cover AuthKeyValueUser::getPropertyValues
     */
    public function testLazyLoadForMail(){

        $array = $this->user->getUserExtraParameters();

        // check array is currently empty
        $this->assertEmpty($array);

        $mail = $this->user->getPropertyValues(GenerisRdf::PROPERTY_USER_MAIL);

        $this->assertNotEmpty($this->user->getUserExtraParameters());
        $this->assertArrayHasKey(GenerisRdf::PROPERTY_USER_MAIL,$this->user->getUserExtraParameters());
    }


    /**
     * @cover AuthKeyValueUser::getPropertyValues
     */
    public function testLazyLoadForMultiParams(){

        $array = $this->user->getUserExtraParameters();


        // check array is currently empty
        $this->assertEmpty($array);
        $this->user->setUserExtraParameters(array('property' => array('property1', 'property2', 'property3')));

        $this->assertNotEmpty($this->user->getUserExtraParameters());
        $this->assertArrayHasKey('property',$this->user->getUserExtraParameters());
        $this->assertEquals( array('property1', 'property2', 'property3') ,$this->user->getPropertyValues('property'));
    }

}
