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

return array(
    'version_eye_module' => array(
        'api_key' => '',
        'endpoint' => 'https://www.versioneye.com/api/v2/',
        'cache'   => array(
            'adapter' => array(
                'name' => 'filesystem',
                'options' => array(
                    'cache_dir' => getcwd() . '/data/cache',
                    'ttl'       => 84600,
                ),
            ),
            'plugins' => array(
                'exception_handler' => array(
                    'throw_exceptions' => false,
                ),
                'Serializer',
            ),
        ),
        'composer_json' => getcwd() . '/composer.json',
        'collector_cache_key' => 'version_eye_module_dependency_statuses',
    ),

    'service_manager' => array(
        'factories' => array(
            'VersionEyeModule\\Service\\ApiService'   => 'VersionEyeModule\\ServiceFactory\\ApiServiceFactory',
            'VersionEyeModule\\Cache\\StorageAdapter' => 'VersionEyeModule\\ServiceFactory\\CacheFactory',
            'VersionEyeModule\\Collector\\DependencyStatusCollector' => 'VersionEyeModule\\ServiceFactory\\DependencyStatusCollectorFactory'
        ),
    ),

    ////////////////////////////////////////////////////////////////////
    // `zendframework/zend-developer-tools` specific settings         //
    // ignore these if you're not developing additional features for  //
    // zend developer tools                                           //
    ////////////////////////////////////////////////////////////////////

    'view_manager' => array(
        'template_map' => array(
            'zend-developer-tools/toolbar/version-eye-status'  => __DIR__ . '/../view/zend-developer-tools/toolbar/version-eye-status.phtml',
        ),
    ),

    'zenddevelopertools' => array(
        'profiler' => array(
            'collectors' => array(
                'version_eye_status'  => 'VersionEyeModule\\Collector\\DependencyStatusCollector',
            ),
        ),
        'toolbar' => array(
            'entries' => array(
                'version_eye_status'  => 'zend-developer-tools/toolbar/version-eye-status',
            ),
        ),
    ),
);
