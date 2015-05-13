<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace VersionEyeModuleTest\Collector;

use Rs\VersionEye\Client;
use VersionEyeModule\Collector\DependencyStatusCollector;

/**
 * Tests for {@see \VersionEyeModule\Collector\DependencyStatusCollector}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @covers \VersionEyeModule\Collector\DependencyStatusCollector
 */
class DependencyStatusCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Rs\VersionEye\Client
     */
    protected $api;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Cache\Storage\StorageInterface
     */
    protected $cache;

    /**
     * @var DependencyStatusCollector
     */
    protected $collector;
    private $http;

    public function setUp()
    {
        $this->cache     = $this->getMock('Zend\\Cache\\Storage\\StorageInterface');
        $this->http      = $this->getMock('Rs\VersionEye\Http\ZendClient', array(), array(), '', false);
        $this->api       = new Client($this->http);
        $this->collector = new DependencyStatusCollector(
            $this->api,
            $this->cache,
            __DIR__ . '/../../../composer.json',
            'test_cache_key'
        );
    }

    public function testCollectOutdatedOrder()
    {
        $this->http->expects($this->never())->method('request');

        $data = array(
            'dependencies' => array(
                array('name' => 'not-outdated' , 'outdated' => false),
                array('name' => 'outdated' , 'outdated' => true)
            )
        );

        $this
            ->cache
            ->expects($this->atLeastOnce())
            ->method('getItem')
            ->will($this->returnValueMap(array(
                array('test_cache_key'.DependencyStatusCollector::CACHE_KEY_SUFFIX,null,null, 'foo'),
                array('test_cache_key'.DependencyStatusCollector::CACHE_DATA_SUFFIX,null,null, $data)
            )));

        $this->collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));

        $this->assertSame(array(
            'dependencies' => array(
                array('name' => 'outdated' , 'outdated' => true),
                array('name' => 'not-outdated' , 'outdated' => false)
            )),
            $this->collector->getCollectedDependencyStatuses()
        );
    }

    public function testCollectWithValidCache()
    {
        $this->http->expects($this->never())->method('request');

        $this
            ->cache
            ->expects($this->atLeastOnce())
            ->method('getItem')
            ->will($this->returnValueMap(array(
                array('test_cache_key'.DependencyStatusCollector::CACHE_KEY_SUFFIX,null,null, 'foo'),
                array('test_cache_key'.DependencyStatusCollector::CACHE_DATA_SUFFIX,null,null, array('data'))
            )));

        $this->collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));
        $this->assertSame(array('data'), $this->collector->getCollectedDependencyStatuses());
    }

    public function testCollectWithoutValidCache()
    {
        $this->http->expects($this->atLeastOnce())->method('request')->will($this->returnValueMap([
            ['GET', 'projects', [], [['name' => 'ocramius/version-eye-module', 'project_key'=>'foo']]],
            ['POST', 'projects/foo', ['project_file' => __DIR__ . '/../../../composer.json'], []]
        ]));

        $this
            ->cache
            ->expects($this->atLeastOnce())
            ->method('setItem')
        ;

        $this->collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));
        $this->assertSame(array(), $this->collector->getCollectedDependencyStatuses());
    }

    public function testCanSerializeAndUnserialize()
    {
        $this->http->expects($this->atLeastOnce())->method('request')->will($this->returnValueMap([
            ['GET', 'projects', [], [['name' => 'ocramius/version-eye-module', 'project_key'=>'foo']]],
            ['POST', 'projects/foo', ['project_file' => __DIR__ . '/../../../composer.json'], []]
        ]));

        $this->collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));

        /* @var $collector \VersionEyeModule\Collector\DependencyStatusCollector */
        $collector = unserialize(serialize($this->collector));

        $this->assertSame(array(), $collector->getCollectedDependencyStatuses());

        $collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));

        $this->assertSame(array(), $collector->getCollectedDependencyStatuses());
    }
}
