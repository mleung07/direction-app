<?php

namespace App\Http\Controllers\Route;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Route;
use App\Location;
use App\Jobs\SendApiRequest;

class RouteController extends Controller
{
    // Function to receive location input
    public function create(Request $request) {
        $input = $request->all();

        // validations
        if(!is_array($input) || count($input) == 0) {
            return response(
                [ 'error' => 'INPUT_NON_ARRAY' ]
                , 400);
        }

        if(count($input) < 2) {
            return response(
                [ 'error' => 'INSUFFICIENT_LENGTH' ]
                , 400);
        }

        foreach($input as $location) {
            if(count($location) > 2) {
                return response(
                    [ 'error' => 'INVALID_LOCATION' ]
                    , 400);
            }
            if(!is_numeric($location[0]) || !is_numeric($location[1])) {
                return response(
                    [ 'error' => 'NOT_A_NUMBER' ]
                    , 400);
            }
        }

        // use random string with length 30 to simulate token
        $token = str_random(30);
        $route = Route::create([
            'token'  => $token,
            'status' => Route::STATUS_PROGRESS
        ]);

        // Save each location with category
        foreach($input as $index => $location) {
            if ($index == 0) {
                $category = 'origin';
            } elseif ($index == count($input) - 1) {
                $category = 'destination';
            } else {
                $category = 'waypoint';
            }

            Location::create([
                'route_id' => $route->id,
                'lat'      => $location[0],
                'lng'      => $location[1],
                'category' => $category,
            ]);
        }

        // Dispatch job to call google maps api
        dispatch(new SendApiRequest($route));
        
        return response()->json([
            'token' => $token
        ]);
    }

    // Function to get location detail by token
    public function get($token) {
        $route = Route::where('token', $token)->first();

        if (!$route) {
            return response(
                [
                    'status' => 'failure',
                    'error'  => 'INVALID_TOKEN'
                ]
                , 400);
        } else {
            if ($route->status == Route::STATUS_PROGRESS) {
                return response()->json([
                        'status' => 'in progress'
                    ]);
            } elseif ($route->status == Route::STATUS_FAIL) {
                return response(
                    [
                        'status' => 'failure',
                        'error'  => 'INVALID_LOCATION'
                    ]
                    , 400);
            } else {
                $paths = $route->locations->map(function ($location) {
                    return [$location->lat, $location->lng];
                });
                return response()->json([
                    'status'         => 'success',
                    'path'           => $paths,
                    'total_distance' => $route->distance,
                    'total_time'     => $route->time
                ]);
            }
        }

    }
}
