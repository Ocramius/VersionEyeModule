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

namespace VersionEyeModuleTest\Service;

use VersionEyeModule\Service\ApiService;
use Zend\Http\Request;

/**
 * Tests for {@see \VersionEyeModule\Service\ApiService}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ApiServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \VersionEyeModule\Service\ApiService
     */
    protected $apiService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Zend\Http\Client
     */
    protected $httpClient;

    /**
     * {@inheritDoc}
     *
     * @covers \VersionEyeModule\Service\ApiService::__construct
     */
    public function setUp()
    {
        $this->httpClient = $this->getMock('Zend\\Http\\Client');
        $this->apiService = new ApiService($this->httpClient, 'TEST_API_KEY');
    }

    /**
     * {@inheritDoc}
     *
     * @covers \VersionEyeModule\Service\ApiService::postComposerDefinitions
     * @covers \VersionEyeModule\Service\ApiService::getResponse
     */
    public function testPostComposerDefinitions()
    {
        $test         = $this;
        $mockResponse = $this->getMock('Zend\\Http\\Response');

        $mockResponse->expects($this->any())->method('isSuccess')->will($this->returnValue(true));
        $mockResponse->expects($this->any())->method('getBody')->will($this->returnValue('{"key":"value"}'));
        $this
            ->httpClient
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(function(Request $request) use ($test) {
                $file = $request->getFiles('composer.json');

                $test->assertSame(array('test' => 'value'), json_decode($file['data'], true));

                return 'TEST_API_KEY' === $request->getQuery('api_key');

            }))
            ->will($this->returnValue($mockResponse));

        $this->assertSame(array('key' => 'value'), $this->apiService->postComposerDefinitions(array('test' => 'value')));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \VersionEyeModule\Service\ApiService::postComposerDefinitions
     * @covers \VersionEyeModule\Service\ApiService::getResponse
     */
    public function testPostComposerDefinitionsFailsOnHttpRequest()
    {
        $mockResponse = $this->getMock('Zend\\Http\\Response');

        $mockResponse->expects($this->any())->method('isSuccess')->will($this->returnValue(false));
        $this->httpClient->expects($this->any())->method('send')->will($this->returnValue($mockResponse));

        $this->assertSame(false, $this->apiService->postComposerDefinitions(array('test' => 'value')));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \VersionEyeModule\Service\ApiService::getResponse
     */
    public function testWillIgnoreOnMissingApiKey()
    {
        $apiService = new ApiService($this->httpClient, '');

        $this->assertSame(false, $apiService->postComposerDefinitions(array('test' => 'value')));
    }
}