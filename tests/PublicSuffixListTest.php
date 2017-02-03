<?php
namespace Geekwright\RegDom;

class PublicSuffixListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PublicSuffixList
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PublicSuffixList();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testContracts()
    {
        $this->assertInstanceOf('\Geekwright\RegDom\PublicSuffixList', $this->object);
    }

    /**
     * @covers Geekwright\RegDom\PublicSuffixList::__construct
     * @covers Geekwright\RegDom\PublicSuffixList::getTree
     */
    public function testGetSet()
    {
        $tree = $this->object->getTree();
        $this->assertTrue(is_array($tree));
        $this->assertArrayHasKey('com', $tree);
    }
}
