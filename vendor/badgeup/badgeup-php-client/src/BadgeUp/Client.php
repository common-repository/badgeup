<?php
declare(strict_types=1);

namespace BadgeUp;

final class Client
{
    // API Key
    private $key;

    // application ID for this client (string)
    private $applicationId;

    // base API URI
    private $baseUri;

    // API Version
    private $version;

    // Guzzle HTTP Client;
    private $http;

    /**
     * @param string $apiKey API key from the BadgeUp dashboard
     */
    public function __construct(string $apiKey)
    {

        $this->key = $apiKey;

        // decode $apiKey to get API Key Contents
        $keyInfo = json_decode(base64_decode($apiKey));

        // application ID is used in every request
        $this->applicationId = $keyInfo->applicationId;

        // set Base API URI
        $this->baseUri = 'https://api.useast1.badgeup.io/';

        // set App Version
        $this->version = 'v1';

        // HTTP Client
        $this->http = new \GuzzleHttp\Client(['base_uri' => $this->baseUri . $this->version . '/apps/' . $this->applicationId . '/']);
    }

    /* ACHIEVEMENTS */

    /**
     * Retrieves an achievement by ID.
     * @return Returns a promise that resolves with the results of a successful response.
     */
    public function getAchievement(string $id)
    {
        return $this->getAsync('achievements/' . $id);
    }

    /**
     * Retrieves a list of achievements.
     * @return Returns a promise that resolves with the results of a successful response.
     */
    public function getAchievements()
    {
        return $this->getResultsPaginated('achievements');
    }

    /* EARNED ACHIEVEMENTS */

    /**
     * Get Earned Achievements, Retrieves a list of earned achievements.
     * @param string $subject Filters the returned earned achievement records by subject.
     * @param string $achievementId Filters the returned earned achievement records by achievement ID.
     * @return Returns a promise that resolves with the results of a successful response.
     */
    public function getEarnedAchievements(string $subject = null, string $achievementId = null)
    {
        $query = array(
            'subject' => $subject,
            'achievementId' => $achievementId
        );
        return $this->getResultsPaginated('earnedachievements', $query);
    }

    /* EVENTS */

    /**
     * Creates a single event, returning the event and earned achievement progress records for any achievement affected.
     *
     * @param string $subject Uniquely identifies the subject the event is for.
     * @param string $key The metric key that will be modified as a result of this event.
     * @param array $modifier The metric key that will be modified as a result of this event.
     * @param bool $showIncomplete Set to "true" to get a response with all achievements that have criteria that were evaluated as a result of this event, even if the achievement was not completed. Set to "false" to only get achievements that completed.
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function createEvent(string $subject, string $key, array $modifier = ['@inc' => 1], $showIncomplete = null)
    {
        $query = array(
            'showIncomplete' => $showIncomplete
        );
        $requestBody = array(
            'subject'=> $subject,
            'key' => $key,
            'modifier' => ['@inc' => 1]
        );
        return $this->postAsync('events', $query, $requestBody);
    }

    /* HTTP */

    /**
     * Return all results via pagination
     * @param method: API Method to Query
     * @param query: Array of Query Params
     * @return Returns a promise that resolves with the results of a successful response.
     */
    private function getResultsPaginated(string $method, $query = []){
        $promise = new \GuzzleHttp\Promise\Promise(function () use (&$promise, &$method, &$query) {
            $data = array();
            while ($res = $this->getAsync($method, $query)->wait()){
                if (!property_exists($res, 'data') || !is_array($res->data)) break;
                $data = array_merge($data, $res->data);
                $next = $res->pages->next;
                if (!$next) break;
                parse_str(parse_url($next, PHP_URL_QUERY), $query);
            }
            $promise->resolve($data);
        });
        return $promise;
    }

    private function getAsync(string $uri, $query = null)
    {
        $promise = $this->http->getAsync($uri, [ "auth" => [$this->key, null], "query" => $query ])->then(function ($response) {
            return json_decode($response->getBody()->getContents());
        });
        return $promise;
    }

    private function postAsync(string $uri, $query, $json)
    {
        $promise = $this->http->postAsync($uri, [ "auth" => [$this->key, null], "query" => $query, "json" => $json ])->then(function ($response) {
            return json_decode($response->getBody()->getContents());
        });
        return $promise;
    }

    private function patchAsync(string $uri, $query, $json)
    {
        $promise = $this->http->patchAsync($uri, [ "auth" => [$this->key, null], "query" => $query, "json" => $json ])->then(function ($response) {
            return json_decode($response->getBody()->getContents());
        });
        return $promise;
    }

    private function deleteAsync(string $uri, $query)
    {
        $promise = $this->http->deleteAsync($uri, [ "auth" => [$this->key, null], "query" => $query ])->then(function ($response) {
            return json_decode($response->getBody()->getContents());
        });
        return $promise;
    }

    /* TEST HOOKS */

    /**
     * Set Test Client, sets http client for mock response testing
     * @param client: Guzzle HTTP Client
     * @return void
     */
    public function setTestClient($client)
    {
        $this->http = $client;
    }
}
