<?php
namespace Geekwright\RegDom;

class RegisteredDomainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RegisteredDomain
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        //$this->object = new RegisteredDomain();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Geekwright\RegDom\RegisteredDomain::__construct
     */
    public function testContracts()
    {
        $object = new RegisteredDomain();
        $this->assertInstanceOf('\Geekwright\RegDom\RegisteredDomain', $object);
    }

    /**
     * @covers Geekwright\RegDom\RegisteredDomain::getRegisteredDomain
     *
     * @dataProvider domainsProvider
     */
    public function testGetRegisteredDomain($url, $regdom)
    {
        $object = new RegisteredDomain();
        $this->assertEquals($regdom, $object->getRegisteredDomain($url));
    }

    /**
     * @return array
     */
    public function domainsProvider()
    {
        $provider = array(
            array(null, null),
            // Mixed case.
            array('COM', null),
            array('example.COM', 'example.com'),
            array('WwW.example.COM', 'example.com'),
            // Leading dot.
            array('.com', null),
            array('.example', null),
            // Unlisted TLD.
            array('example', null),
            array('example.example', 'example.example'),
            array('b.example.example', 'example.example'),
            array('a.b.example.example', 'example.example'),
            // TLD with only 1 rule.
            array('biz', null),
            array('domain.biz', 'domain.biz'),
            array('b.domain.biz', 'domain.biz'),
            array('a.b.domain.biz', 'domain.biz'),
            // TLD with some 2-level rules.
            array('com', null),
            array('example.com', 'example.com'),
            array('b.example.com', 'example.com'),
            array('a.b.example.com', 'example.com'),
            array('uk.com', null),
            array('example.uk.com', 'example.uk.com'),
            array('b.example.uk.com', 'example.uk.com'),
            array('a.b.example.uk.com', 'example.uk.com'),
            array('test.ac', 'test.ac'),
            // TLD with only 1 (wildcard) rule.
            array('mm', null),
            array('c.mm', null),
            array('b.c.mm', 'b.c.mm'),
            array('a.b.c.mm', 'b.c.mm'),
            // More complex TLD.
            array('jp', null),
            array('test.jp', 'test.jp'),
            array('www.test.jp', 'test.jp'),
            array('ac.jp', null),
            array('test.ac.jp', 'test.ac.jp'),
            array('www.test.ac.jp', 'test.ac.jp'),
            array('kyoto.jp', null),
            array('test.kyoto.jp', 'test.kyoto.jp'),
            array('ide.kyoto.jp', null),
            array('b.ide.kyoto.jp', 'b.ide.kyoto.jp'),
            array('a.b.ide.kyoto.jp', 'b.ide.kyoto.jp'),
            array('c.kobe.jp', null),
            array('b.c.kobe.jp', 'b.c.kobe.jp'),
            array('a.b.c.kobe.jp', 'b.c.kobe.jp'),
            array('city.kobe.jp', 'city.kobe.jp'),
            array('www.city.kobe.jp', 'city.kobe.jp'),
            // TLD with a wildcard rule and exceptions.
            array('ck', null),
            array('test.ck', null),
            array('b.test.ck', 'b.test.ck'),
            array('a.b.test.ck', 'b.test.ck'),
            array('www.ck', 'www.ck'),
            array('www.www.ck', 'www.ck'),
            // US K12.
            array('us', null),
            array('test.us', 'test.us'),
            array('www.test.us', 'test.us'),
            array('ak.us', null),
            array('test.ak.us', 'test.ak.us'),
            array('www.test.ak.us', 'test.ak.us'),
            array('k12.ak.us', null),
            array('test.k12.ak.us', 'test.k12.ak.us'),
            array('www.test.k12.ak.us', 'test.k12.ak.us'),
            // IDN labels.
            array('食狮.com.cn', '食狮.com.cn'),
            array('食狮.公司.cn', '食狮.公司.cn'),
            array('www.食狮.公司.cn', '食狮.公司.cn'),
            array('shishi.公司.cn', 'shishi.公司.cn'),
            array('公司.cn', null),
            array('食狮.中国', '食狮.中国'),
            array('www.食狮.中国', '食狮.中国'),
            array('shishi.中国', 'shishi.中国'),
            array('中国', null),
            // Same as above, but punycoded.
            array('xn--85x722f.com.cn', '食狮.com.cn'),
            array('xn--85x722f.xn--55qx5d.cn', '食狮.公司.cn'),
            array('www.xn--85x722f.xn--55qx5d.cn', '食狮.公司.cn'),
            array('shishi.xn--55qx5d.cn', 'shishi.公司.cn'),
            array('xn--55qx5d.cn', null),
            array('xn--85x722f.xn--fiqs8s', '食狮.中国'),
            array('www.xn--85x722f.xn--fiqs8s', '食狮.中国'),
            array('shishi.xn--fiqs8s', 'shishi.中国'),
            array('xn--fiqs8s', null),
            // inspiration case
            array('rfu.in.ua', 'rfu.in.ua'),
            array('in.ua', null),
        );
        return $provider;
    }
}
