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

namespace VersionEyeModuleTest\ServiceFactory;

use VersionEyeModule\ServiceFactory\DependencyStatusCollectorFactory;

/**
 * Tests for {@see \VersionEyeModule\ServiceFactory\DependencyStatusCollectorFactory}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class DependencyStatusCollectorFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \VersionEyeModule\ServiceFactory\DependencyStatusCollectorFactory::createService
     */
    public function testCreateService()
    {
        $factory        = new DependencyStatusCollectorFactory();
        $cache          = $this->getMock('Zend\\Cache\\Storage\\StorageInterface');
        $api            = $this->getMock('Rs\VersionEye\Client', array(), array(), '', false);

        $serviceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($serviceName) use ($api, $cache) {
                if ($serviceName === 'VersionEyeModule\\Service\\ApiService') {
                    return $api;
                }

                if ($serviceName === 'VersionEyeModule\\Cache\\StorageAdapter') {
                    return $cache;
                }

                if ($serviceName === 'Config') {
                    return array(
                        'version_eye_module' => array(
                            'composer_json'       => 'files/composer.json',
                            'collector_cache_key' => 'test_cache_key',
                        ),
                    );
                }

                throw new \InvalidArgumentException();
            }));

        $this->assertInstanceOf(
            'VersionEyeModule\\Collector\\DependencyStatusCollector',
            $factory->createService($serviceLocator)
        );
    }
}
