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

namespace VersionEyeModule\ServiceFactory;

use Rs\VersionEye\Client;
use Rs\VersionEye\Http\ZendClient;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Service factory responsible for instantiating an {@see \Rs\VersionEye\Client}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ApiServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Rs\VersionEye\Client
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $httpClient \Zend\Http\Client */
        $config = $serviceLocator->get('Config');

        $client = new Client(new ZendClient($config['version_eye_module']['endpoint']));
        $client->authorize($config['version_eye_module']['api_key']);

        return $client;
    }
}
