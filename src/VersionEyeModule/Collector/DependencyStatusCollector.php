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

namespace VersionEyeModule\Collector;

use VersionEyeModule\Service\ApiService;
use Zend\Cache\Storage\StorageInterface;
use Zend\Mvc\MvcEvent;
use ZendDeveloperTools\Collector\CollectorInterface;

/**
 * Collector that retrieves status of each installed component
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class DependencyStatusCollector implements CollectorInterface, \Serializable
{
    const DEFAULT_PRIORITY = 100;

    /**
     * @var \VersionEyeModule\Service\ApiService|null
     */
    protected $api;

    /**
     * @var \Zend\Cache\Storage\StorageInterface|null
     */
    protected $cache;

    /**
     * @var string path to the project's `composer.json` file
     */
    protected $composerPath;

    /**
     * @var string
     */
    protected $cacheKey;

    /**
     * @var array|null
     */
    protected $collected;

    /**
     * @param \VersionEyeModule\Service\ApiService $api
     * @param \Zend\Cache\Storage\StorageInterface $cache
     * @param string                               $composerPath path to the composer.json file
     * @param string                               $cacheKey
     */
    public function __construct(ApiService $api, StorageInterface $cache, $composerPath, $cacheKey)
    {
        $this->api          = $api;
        $this->cache        = $cache;
        $this->composerPath = $composerPath;
        $this->cacheKey     = $cacheKey;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'version_eye_status';
    }

    /**
     * {@inheritDoc}
     */
    public function getPriority()
    {
        return static::DEFAULT_PRIORITY;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(MvcEvent $mvcEvent)
    {
        if ( ! $this->cache || ! $this->api) {
            // instance was un-serialized, therefore we can't use it
            return;
        }

        $data = $this->cache->getItem($this->cacheKey, $success);

        if (is_array($data)) {
            $this->collected = $data;

            return;
        }

        $composerJson = file_get_contents($this->composerPath);

        if ( ! is_string($composerJson)) {
            $this->collected = null;

            return;
        }

        $submittedData = json_decode($composerJson, true);

        if ( ! is_array($submittedData)) {
            $this->collected = null;

            return;
        }

        $this->collected = $this->api->postComposerDefinitions($submittedData);

        $this->cache->setItem($this->cacheKey, $this->collected);
    }

    /**
     * @return array|null
     */
    public function getCollectedDependencyStatuses()
    {
        return $this->collected;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(array(
            'collected'    => $this->collected,
            'cacheKey'     => $this->cacheKey,
            'composerPath' => $this->composerPath
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);

        $this->collected    = $data['collected'];
        $this->cacheKey     = $data['cacheKey'];
        $this->composerPath = $data['composerPath'];
    }
}