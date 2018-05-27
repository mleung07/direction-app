<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use App\Route;

class RouteTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->app->instance('middleware.disable', true);
    }

    public function testRouteCreateSuccess()
    {
        $response = $this->json('POST', '/route', [
            ["22.372081", "114.107877"],
            ["22.326442", "114.167811"]
        ]);

        $this->assertResponseStatus(200);
        $response->seeJsonStructure(['token']);
    }

    public function testRouteCreateSuccessWithWaypoint()
    {
        $response = $this->json('POST', '/route', [
            ["22.372081", "114.107877"],
            ["22.284419", "114.159510"],
            ["22.326442", "114.167811"]
        ]);

        $this->assertResponseStatus(200);
        $response->seeJsonStructure(['token']);
    }

    public function testRouteCreateNonArray()
    {
        $response = $this->json('POST', '/route');

        $this->assertResponseStatus(400);
        $response->seeJson([
            'error' => 'INPUT_NON_ARRAY',
        ]);
    }

    public function testRouteCreateInsufficientLength()
    {
        $response = $this->json('POST', '/route', [
            ["22.326442", "114.167811"]
        ]);

        $this->assertResponseStatus(400);
        $response->seeJson([
            'error' => 'INSUFFICIENT_LENGTH',
        ]);
    }

    public function testRouteCreateInvalidLocation()
    {
        $response = $this->json('POST', '/route', [
            ["22.372081", "114.107877", "114.107877"],
            ["22.284419", "114.159510", "114.107877"],
            ["22.326442", "114.167811"]
        ]);

        $this->assertResponseStatus(400);
        $response->seeJson([
            'error' => 'INVALID_LOCATION',
        ]);
    }

    public function testRouteCreateNan()
    {
        $response = $this->json('POST', '/route', [
            ["a", "b"],
            ["22.326442", "114.167811"],
        ]);

        $this->assertResponseStatus(400);
        $response->seeJson([
            'error' => 'NOT_A_NUMBER',
        ]);
    }

    public function testRouteGetInvalid() {
        $response = $this->json('GET', '/route/invalid_token');

        $this->assertResponseStatus(400);
        $response->seeJson([
            'status' => 'failure',
            'error'  => 'INVALID_TOKEN'
        ]);
    }

    public function testRouteGetSuccess() {
        $route = Route::where('status', Route::STATUS_SUCCESS)->first();
        if ($route) {
            $response = $this->json('GET', '/route/' . $route->token);

            $this->assertResponseStatus(200);
            $response->seeJson([
                'total_distance' => $route->distance,
                'total_time' => $route->time,
            ]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function testRouteGetProgress() {
        $route = Route::where('status', Route::STATUS_PROGRESS)->first();
        if ($route) {
            $response = $this->json('GET', '/route/' . $route->token);

            $this->assertResponseStatus(200);
            $response->seeJson([
                'status' => 'in progress',
            ]);
        } else {
            $this->assertTrue(true);
        }
    }

    public function testRouteGetFail() {
        $route = Route::where('status', Route::STATUS_FAIL)->first();
        if ($route) {
            $response = $this->json('GET', '/route/' . $route->token);

            $this->assertResponseStatus(400);
            $response->seeJson([
                'status' => 'failure',
            ]);
        } else {
            $this->assertTrue(true);
        }
    }
}
