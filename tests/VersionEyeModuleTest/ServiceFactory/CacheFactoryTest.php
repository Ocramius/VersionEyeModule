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

use VersionEyeModule\ServiceFactory\CacheFactory;

/**
 * Tests for {@see \VersionEyeModule\ServiceFactory\CacheFactory}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class CacheFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \VersionEyeModule\ServiceFactory\CacheFactory::createService
     */
    public function testCreateService()
    {
        $factory        = new CacheFactory();
        $serviceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue(array('version_eye_module' => array('cache' => array('adapter' => 'memory')))));

        $this->assertInstanceOf('Zend\\Cache\\Storage\\StorageInterface', $factory->createService($serviceLocator));
    }
}
