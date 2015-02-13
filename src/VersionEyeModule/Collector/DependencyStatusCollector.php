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

use Rs\VersionEye\Client;
use Rs\VersionEye\Http\CommunicationException;
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
    const CACHE_KEY_SUFFIX = '-project';
    const CACHE_DATA_SUFFIX = '-data';

    /**
     * @var Client
     */
    protected $api;

    /**
     * @var StorageInterface
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
     * @param Client           $api
     * @param StorageInterface $cache
     * @param string           $composerPath path to the composer.json file
     * @param string           $cacheKey
     */
    public function __construct(Client $api, StorageInterface $cache, $composerPath, $cacheKey)
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
        if (! $this->cache || ! $this->api) {
            // instance was un-serialized, therefore we can't use it
            return;
        }

        $projectKey = $this->getProjectKey();
        $response   = $this->getResponse($projectKey);

        $this->collected = $response;
    }

    /**
     * @param bool $outdatedFirst
     * @return array|null
     */
    public function getCollectedDependencyStatuses($outdatedFirst = false)
    {
        $statuses = $this->collected;
        if ($outdatedFirst && isset($statuses['dependencies'])) {
            $dependencies = $statuses['dependencies'];
            usort($dependencies, function ($dependency) {
                return !$dependency['outdated'];
            });
            $statuses['dependencies'] = $dependencies;
        }
        return $statuses;
    }

    /**
     * {@inheritDoc}
     */
    public function serialize()
    {
        return serialize(
            array(
                'collected'    => $this->collected,
                'cacheKey'     => $this->cacheKey,
                'composerPath' => $this->composerPath
            )
        );
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

    /**
     * calculate the project key
     *
     * @return string
     */
    private function getProjectKey()
    {
        $projectKey = $this->cache->getItem($this->cacheKey . static::CACHE_KEY_SUFFIX);

        if ($projectKey) {
            return $projectKey;
        }

        $data = json_decode(file_get_contents($this->composerPath), true);
        $projects = $this->api->api('projects')->all();

        foreach ($projects as $project) {
            if ($project['name'] == $data['name']) {
                $projectKey = $project['project_key'];
                $this->cache->setItem($this->cacheKey . static::CACHE_KEY_SUFFIX, $projectKey);
                break;
            }
        }

        return $projectKey;
    }

    /**
     * get the response from the api
     *
     * @param  string $projectKey
     * @return array
     */
    private function getResponse($projectKey)
    {
        $response = $this->cache->getItem($this->cacheKey . static::CACHE_DATA_SUFFIX);

        if ($response) {
            return $response;
        }

        if ($projectKey === null) {
            $response = $this->api->api('projects')->create($this->composerPath);
        } else {
            try {
                $response = $this->api->api('projects')->update($projectKey, $this->composerPath);
            } catch (CommunicationException $e) {
                //fails when project was deleted on versioneye
                $response = $this->api->api('projects')->create($this->composerPath);
            }
        }
        $this->cache->setItem($this->cacheKey . static::CACHE_DATA_SUFFIX, $response);

        return $response;
    }
}
