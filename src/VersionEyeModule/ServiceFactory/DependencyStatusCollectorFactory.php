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

use VersionEyeModule\Collector\DependencyStatusCollector;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory responsible of building a {@see \VersionEyeModule\Collector\DependencyStatusCollector}
 *
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class DependencyStatusCollectorFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \VersionEyeModule\Collector\DependencyStatusCollector
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $api \Rs\VersionEye\Client */
        $api = $serviceLocator->get('VersionEyeModule\\Service\\ApiService');
        /* @var $cache \Zend\Cache\Storage\StorageInterface */
        $cache = $serviceLocator->get('VersionEyeModule\\Cache\\StorageAdapter');
        /* @var $config array */
        $config = $serviceLocator->get('Config');

        $composerPath = $config['version_eye_module']['composer_json'];
        $cacheKey     = $config['version_eye_module']['collector_cache_key'];

        return new DependencyStatusCollector($api, $cache, $composerPath, $cacheKey);
    }
}
