<?php
/**
 * - WebProfilerServiceProviderTest.php
 *
 * @author  chris
 * @created 26/05/15 12:32
 */
namespace Kurl\Silex\Provider\Tests;

use Kurl\Silex\Provider\WebProfilerServiceProvider;
use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WebProfilerServiceProviderTest extends WebTestCase
{

    /**
     * The test profiler directory.
     *
     * @var string
     */
    private $profilerDirectory;

    /**
     * Vanilla checks.
     */
    public function testCreateProfiler()
    {
        $app = $this->createApplication();
        $app->boot();

        $this->assertContains(
            'symfony/security-bundle/Symfony/Bundle/SecurityBundle/Resources/views',
            $app['security_profiler.templates_path']
        );
        $this->assertArrayHasKey('security', $app['data_collectors']);
    }

    /**
     * Does a sweep on the profiler to ensure everything is all good.
     */
    public function testProfiler()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        $node = $crawler->filter('div.sf-toolbar');
        $this->assertCount(1, $node);

        $crawler = $client->request('GET', '/_profiler/empty/search/results?limit=10');

        $table = $crawler->filter('table tbody tr td a');

        $this->assertCount(1, $table); // There should only be one profiler entry.

        $hash = $table->getNode(0)->nodeValue; // The profiler key

        // Assert we don't get a 404, that should be enough!
        $client->request('GET', '/_profiler/' . $hash . '?panel=security');

        $this->assertTrue($client->getResponse()->isOk());
    }

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $app = new Application();

        $app->register(new UrlGeneratorServiceProvider());
        $app->register(new ServiceControllerServiceProvider());
        $app->register(new TwigServiceProvider());
        $app->register(
            new SecurityServiceProvider(),
            array(
                'security.firewalls'    => array(
                    'admin' => array(
                        'pattern' => '^/admin',
                        'http'    => true,
                        'users'   => array(
                            // raw password is foo
                            'admin' => array(
                                'ROLE_ADMIN',
                                '5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg=='
                            ),
                        ),
                    ),
                )
            )
        );

        $app->register(
            new WebProfilerServiceProvider(),
            array(
                'profiler.cache_dir' => $this->profilerDirectory
            )
        );

        $app->get('/', function () {
            return new Response('<html><body>Hello World!</body></html>');
        });

        return $app;
    }

    public function setUp()
    {
        $this->profilerDirectory = sys_get_temp_dir() . '/' . substr(sha1(microtime()), 0, 8);
        mkdir($this->profilerDirectory);

        parent::setUp();
    }
}
