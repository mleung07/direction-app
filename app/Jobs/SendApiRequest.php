<?php

namespace App\Jobs;

use App\Route;

class SendApiRequest extends Job
{
    protected $route;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Fetch locations from model
        $origin = $this->route->locations()->where('category', 'origin')->first();
        $destination = $this->route->locations()->where('category', 'destination')->first();
        $waypoints = $this->route->locations()->where('category', 'waypoint')->get();

        // Build query for access google maps api
        $url = 'https://maps.googleapis.com/maps/api/directions/json?';

        $query = [
            'key'         => 'AIzaSyBzBmtwh4MhdeMXchQUnau1t3WTRYp69yY',
            'origin'      => $origin->lat . ',' . $origin->lng,
            'destination' => $destination->lat . ',' . $destination->lng,
        ];

        $url .= urldecode(http_build_query($query));

        // add query when there is waypoint
        if ($waypoints->count() > 0) {
            $waypoint_str = '&waypoints=optimize:true';
            foreach ($waypoints as $waypoint) {
                $waypoint_str .= '|' . $waypoint->lat . ',' . $waypoint->lng;
            }
            $url .= $waypoint_str;
        }

        // Call api via cURL
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            $this->route->update([
                'status' => Route::STATUS_FAIL
            ]);
            return false;
        }
        $info = curl_getinfo($ch);
        $http_result = $info ['http_code'];
        curl_close($ch);

        // Update model with response
        $json = json_decode($output, true);
        $legs = $json['routes'][0]['legs'];

        // Sum up distance and time for each legs
        $distance = 0;
        $time = 0;
        foreach($legs as $leg) {
            $distance += $leg['distance']['value'];
            $time += $leg['duration']['value'];
        }

        $this->route->update([
            'distance' => $distance,
            'time'     => $time,
            'status'   => Route::STATUS_SUCCESS
        ]);
        
        return true;
    }
}
