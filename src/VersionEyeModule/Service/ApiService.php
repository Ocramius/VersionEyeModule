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

namespace VersionEyeModule\Service;

use Zend\Http\Client;
use Zend\Http\Request;

/**
 * VersionEye API adapter, provides a simple logic to interact with
 * {@see https://www.versioneye.com/api}
 * Currently partially supports {@see https://www.versioneye.com/api/v1/swagger_doc.json}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ApiService
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     *
     * @todo should use https once the ugly problem with the ca cert file problem is solved
     */
    protected $baseUrl = 'http://www.versioneye.com/api/v1/';

    /**
     * @var \Zend\Http\Client
     */
    protected $httpClient;

    /**
     * @param \Zend\Http\Client $httpClient
     * @param string            $apiKey
     */
    public function __construct(Client $httpClient, $apiKey)
    {
        $this->httpClient = $httpClient;
        $this->apiKey     = (string) $apiKey;
    }

    /**
     * Retrieves an associative array from the {@see https://www.versioneye.com/api/v1/projects.json}
     * given an array representing the project's `composer.json`
     *
     * @param array $definitions contents of a `composer.json` to be submitted
     *
     * @return array|bool
     */
    public function postComposerDefinitions(array $definitions)
    {
        $request = new Request();

        $request->setMethod(Request::METHOD_POST);
        $request->getFiles()->set(
            'composer.json',
            array(
                'formname'  => 'upload',
                'filename'  => 'composer.json',
                'ctype'     => 'application/json',
                'data'      => json_encode($definitions)
            )
        );
        $request->setUri($this->baseUrl . 'projects.json');

        return $this->getResponse($request);
    }

    /**
     * @param \Zend\Http\Request $request
     *
     * @return bool|array
     */
    protected function getResponse(Request $request)
    {
        if (empty($this->apiKey)) {
            return false;
        }

        $request->getQuery()->set('api_key', $this->apiKey);

        $response = $this->httpClient->send($request);

        if ( ! $response->isSuccess()) {
            return false;
        }

        return json_decode($response->getBody(), true);
    }
}
