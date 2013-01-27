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

use VersionEyeModule\Collector\DependencyStatusCollector;
use VersionEyeModule\ServiceFactory\DependencyStatusCollectorFactory;

/**
 * Tests for {@see \VersionEyeModule\Collector\DependencyStatusCollector}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class DependencyStatusCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\VersionEyeModule\Service\ApiService
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

    /**
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::__construct
     */
    public function setUp()
    {
        $this->cache     = $this->getMock('Zend\\Cache\\Storage\\StorageInterface');
        $this->api       = $this->getMock('VersionEyeModule\\Service\\ApiService', array(), array(), '', false);
        $this->collector = new DependencyStatusCollector(
            $this->api,
            $this->cache,
            __DIR__ . '/../../../composer.json',
            'test_cache_key'
        );
    }

    /**
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::collect
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::getCollectedDependencyStatuses
     */
    public function testCollectWithValidCache()
    {
        $test = $this;
        $this
            ->cache
            ->expects($this->once())
            ->method('getItem')
            ->will($this->returnCallback(function ($key) use ($test) {
                $test->assertSame('test_cache_key', $key);

                return array('data');
            }));

        $this->collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));
        $this->assertSame(array('data'), $this->collector->getCollectedDependencyStatuses());
    }

    /**
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::collect
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::getCollectedDependencyStatuses
     */
    public function testCollectWithoutValidCache()
    {
        $this
            ->api
            ->expects($this->once())
            ->method('postComposerDefinitions')
            ->with($this->callback(function ($data) {
                return 'ocramius/version-eye-module' === $data['name'];
            }))
            ->will($this->returnValue(array('data')));

        $this
            ->cache
            ->expects($this->once())
            ->method('setItem')
            ->with('test_cache_key', array('data'));

        $this->collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));
        $this->assertSame(array('data'), $this->collector->getCollectedDependencyStatuses());
    }

    /**
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::collect
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::getCollectedDependencyStatuses
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::serialize
     * @covers \VersionEyeModule\Collector\DependencyStatusCollector::unserialize
     */
    public function testCanSerializeAndUnserialize()
    {
        $this
            ->api
            ->expects($this->once())
            ->method('postComposerDefinitions')
            ->will($this->returnValue(array('data')));

        $this->collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));

        /* @var $collector \VersionEyeModule\Collector\DependencyStatusCollector */
        $collector = unserialize(serialize($this->collector));

        $this->assertSame(array('data'), $collector->getCollectedDependencyStatuses());

        $collector->collect($this->getMock('Zend\\Mvc\\MvcEvent'));

        $this->assertSame(array('data'), $collector->getCollectedDependencyStatuses());
    }
}