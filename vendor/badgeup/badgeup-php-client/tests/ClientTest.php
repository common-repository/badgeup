<?php
declare(strict_types=1);
require(dirname(__FILE__).'/../vendor/autoload.php');


use PHPUnit\Framework\Testcase;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\RequestException;

final class ClientTest extends TestCase
{

    private $key = 'eyJhY2NvdW50SWQiOiJ0aGViZXN0IiwiYXBwbGljYXRpb25JZCI6IjEzMzciLCJrZXkiOiJpY2VjcmVhbWFuZGNvb2tpZXN5dW0ifQ==';

    public function test_Init()
    {
        $bup = new BadgeUp\Client($this->key);
        $this->assertInstanceOf(BadgeUp\Client::class, $bup);
        $this->assertInstanceOf(GuzzleHttp\Promise\Promise::class, $bup->getAchievements());
        $this->assertInstanceOf(GuzzleHttp\Promise\Promise::class, $bup->getEarnedAchievements());
        $this->assertInstanceOf(GuzzleHttp\Promise\Promise::class, $bup->getEarnedAchievements('bob'));
        $this->assertInstanceOf(GuzzleHttp\Promise\Promise::class, $bup->createEvent('test-user', 'some:key'));
    }

    public function test_getAchievement_200()
    {
        $responseBody = '{
            "id": "123",
            "name": "name",
            "description": "desc",
            "meta": {
                "created": "2017-10-15T23:59:29.622Z"
            }
        }';
        $response200 = (new Response())
            ->withBody(Psr7\stream_for($responseBody))
            ->withStatus('200');
        $mock = new MockHandler([ $response200 ]);

        $stack = GuzzleHttp\HandlerStack::create($mock);

        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $stack->push($history);

        $client = new GuzzleHttp\Client(['handler' => $stack]);

        // construct the client
        $bup = new BadgeUp\Client($this->key);
        $bup->setTestClient($client);

        $obj = json_decode($responseBody);
        $res = $bup->getAchievement("123")->wait();
        $this->assertEquals($obj, $res);

        // check uri
        $uri = (string) $container[0]['request']->getUri();
        $this->assertEquals('achievements/123', $uri);
    }

    public function test_getAchievements_200()
    {
        $mockBody = Psr7\stream_for('{
            "pages": {
                "previous": null,
                "next": null
            },
            "data": [{ "item": 1 }]
        }');
        $response200 = (new Response())
            ->withBody($mockBody)
            ->withStatus('200');
        $mock = new MockHandler([ $response200 ]);

        $stack = GuzzleHttp\HandlerStack::create($mock);

        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $stack->push($history);

        $client = new GuzzleHttp\Client(['handler' => $stack]);

        // construct the client
        $bup = new BadgeUp\Client($this->key);
        $bup->setTestClient($client);

        // validate the results
        $obj = json_decode('[{ "item": 1 }]');
        $res = $bup->getAchievements()->wait();

        $this->assertEquals($obj, $res);

        // check uri
        $uri = $container[0]['request']->getUri();
        $this->assertEquals('achievements', $uri);
    }

    public function test_getEarnedAchievements_200()
    {
        $mockBody = Psr7\stream_for('{
            "pages": {
                "previous": null,
                "next": null
            },
            "data": [{ "item": 1 }]
        }');
        $response200 = (new Response())
                    ->withBody($mockBody)
                    ->withStatus('200');
        $mock = new MockHandler([ $response200 ]);
        $handler = HandlerStack::create($mock);
        $client = new GuzzleHttp\Client(['handler' => $mock]);

        // construct the client
        $bup = new BadgeUp\Client($this->key);
        $bup->setTestClient($client);

        $obj = json_decode('[{ "item": 1 }]');
        $res = $bup->getEarnedAchievements()->wait();
        $this->assertEquals($obj, $res);
    }

    public function test_getEarnedAchievementForSubject_200()
    {
        $mockBody = Psr7\stream_for('{
            "pages": {
                "previous": null,
                "next": null
            },
            "data": [{ "item": 1 }]
        }');
        $response200 = (new Response())
                    ->withBody($mockBody)
                    ->withStatus('200');
        $mock = new MockHandler([ $response200 ]);

        $stack = GuzzleHttp\HandlerStack::create($mock);

        $container = [];
        $history = GuzzleHttp\Middleware::history($container);
        $stack->push($history);

        $client = new GuzzleHttp\Client(['handler' => $stack]);

        // construct the client
        $bup = new BadgeUp\Client($this->key);
        $bup->setTestClient($client);

        $obj = json_decode('[{ "item": 1 }]');
        $res = $bup->getEarnedAchievements('bob')->wait();
        $this->assertEquals($obj, $res);

        // check uri
        $uri = (string) $container[0]['request']->getUri();
        $this->assertEquals('earnedachievements?subject=bob', $uri);
    }

    public function test_getEarnedAchievementsForSubject_200()
    {
        $bodyPage1 = Psr7\stream_for('{
            "pages": {
                "previous": null,
                "next": "/v1/apps/1uyukumasg/achievements?after=cj8md6c4b27wwn6558f4zdjnb&more=params"
            },
            "data": [{ "item": 1 }]
        }');
        $responsePage1 = (new Response())
                    ->withBody($bodyPage1)
                    ->withStatus('200');

        $bodyPage2 = Psr7\stream_for('{
            "pages": {
                "previous": null,
                "next": null
            },
            "data": [{ "item": 2 }]
        }');
        $responsePage2 = (new Response())
                    ->withBody($bodyPage2)
                    ->withStatus('200');

        $mock = new MockHandler([ $responsePage1, $responsePage2 ]);
        $handler = HandlerStack::create($mock);
        $client = new GuzzleHttp\Client(['handler' => $mock]);

        // construct the client
        $bup = new BadgeUp\Client($this->key);
        $bup->setTestClient($client);

        $obj = json_decode('[{ "item": 1 }, { "item": 2 }]');
        $res = $bup->getEarnedAchievements()->wait();

        $this->assertEquals($obj, $res);
    }

    public function test_createEvent_200()
    {
        $mockBody = Psr7\stream_for('{"hello": "world"}');
        $response200 = (new Response())
                    ->withBody($mockBody)
                    ->withStatus('200');
        $mock = new MockHandler([ $response200 ]);
        $handler = HandlerStack::create($mock);
        $client = new GuzzleHttp\Client(['handler' => $mock]);

        // construct the client
        $bup = new BadgeUp\Client($this->key);
        $bup->setTestClient($client);

        $res = $bup->createEvent('test-user', 'some:key')->wait();
        $this->assertEquals('world', $res->hello);
    }
}
