<?php

use Silex\WebTestCase;

class controllersTest extends WebTestCase
{
    public function testRoot()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isRedirection());
    }

    public function testSitemap()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $client->request('GET', '/sitemap.xml');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testLogin()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $client->request('GET', '/login');

        $this->assertTrue($client->getResponse()->isOk());
    }

    public function testShowcase()
    {
        $client = $this->createClient();
        $client->followRedirects(true);
        $client->request('GET', '/en/first');

        $this->assertTrue($client->getResponse()->isRedirection());
    }

    public function createApplication()
    {
        $app = require __DIR__.'/../src/app.php';
        require __DIR__.'/../config/dev.php';
        require __DIR__.'/../src/controllers.php';
        $app['session.test'] = true;

        return $this->app = $app;
    }
}
