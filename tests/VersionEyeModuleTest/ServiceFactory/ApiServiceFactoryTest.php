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

use VersionEyeModule\ServiceFactory\ApiServiceFactory;

/**
 * Tests for {@see \VersionEyeModule\ServiceFactory\ApiServiceFactory}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ApiServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \VersionEyeModule\ServiceFactory\ApiServiceFactory::createService
     */
    public function testCreateService()
    {
        $factory        = new ApiServiceFactory();
        $httpClient     = $this->getMock('Zend\\Http\\Client');
        $serviceLocator = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');

        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($serviceName) use ($httpClient) {
                if ($serviceName === 'Config') {
                    return array('version_eye_module' => array('api_key' => 'TEST_API_KEY'));
                }

                if ($serviceName === 'VersionEyeModule\\Service\\HttpClient') {
                    return $httpClient;
                }

                throw new \InvalidArgumentException();
            }));

        $this->assertInstanceOf('VersionEyeModule\\Service\\ApiService', $factory->createService($serviceLocator));
    }
}