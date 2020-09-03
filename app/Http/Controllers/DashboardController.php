<?php

namespace App\Http\Controllers;

use App\User;
use App\Models\Match;

use Illuminate\Http\Request;
use Auth;
use DB;

define('IMAGE_FOLDER', '/uploads/images/');
define('EARTH_RADIUS', 6371000);
define('SEARCH_RADIUS', 5000);

class DashboardController extends Controller {
    public function dashboard() {
        /**
         *  Check whether user has a profile picture
         *  redirect to upload prompt page if not
         *  otherwise redirect to dashboard
         */
        if (!isset(Auth::user()->image)) {
            return view('upload');
        }

        // Find users within SEARCH_RADIUS
        $users = $this->search();

        // Remove previously liked/disliked users from the list
        foreach ($users as $key => $user) {
            // if exists get the match row between the 2 user
            $match = Match::whereRaw('user1_id = ' . Auth::user()->id . ' OR ' . 'user2_id = ' . Auth::user()->id)
                            ->whereRaw('user1_id = ' . $user->id . ' OR ' . 'user2_id = ' . $user->id)
                            ->first();

            if (!isset($match)) continue;

            /**
              * Check if current users status is pending and whether other user likes him
              * if either is false remove the user from the list
              */
            $remove = false;
            if ($match->user1_id == Auth::user()->id) {
                if ($match->user1_status == 0 && $match->user2_status != 2) continue;
                $remove = true;
            } else {
                if ($match->user2_status == 0 && $match->user1_status != 2) continue;
                $remove = true;
            }

            if ($remove) unset($users[$key]);
        }

        // Calculate age of the users
        $today = explode('-', date('Y-m-d', time()));
        foreach ($users as $key => $user) {
            $dob = explode('-', $user->dob);

            $user->age = intval($today[0]) - intval($dob[0]);
            if (intval($today[1]) == intval($dob[1]) && intval($today[2]) < intval($dob[2])) {
                $user->age--;
            } else if (intval($today[1]) < intval($dob[1])) {
                $user->age--;
            }
        }

        return view('dashboard', compact('users'));
    }

    public function upload(Request $request) {
        /**
         *  If request has an image file
         *  move the image file to server
         *  and store file info in database
         */
        if ($request->hasFile('image')) {
            if (Auth::user()->image) unlink(dirname(base_path()) . Auth::user()->image);

            $filename = uniqid() . time() . '.png';
            
            Auth::user()->image = IMAGE_FOLDER . $filename;
            Auth::user()->save();

            move_uploaded_file($request->image, dirname(base_path()) . IMAGE_FOLDER . $filename);
        }

        return redirect()->route('dashboard');
    }

    /**
      * Handles jQuery POST request when a user like another user
      * Returns 0 for pending matches and 1 for matches 
      */
    public function like(Request $request) {
        $user_id = $request->user_id;
        $match = Match::where('user2_id', Auth::user()->id)
                        ->where('user1_id', $user_id)
                        ->first();

        /**
         *  If match already exists that means the other user already liked him
         *  so its a match
         *  else create a match
         */
        if (!isset($match)) {
            $match = new Match;
            $match->user1_id = Auth::user()->id;
            $match->user2_id = $user_id;
            $match->user1_status = 1;
            $match->save();

            return 0;
        } else {
            $match->user2_status = 1;
            $match->save();

            return 1;
        }
    }

    /**
      * Handles jQuery POST request when a user dislike another user
      */
    public function dislike(Request $request) {
        $user_id = $request->user_id;
        $match = Match::where('user2_id', Auth::user()->id)
                        ->where('user1_id', $user_id)
                        ->first();

        /**
         *  If match already exists that means the other user already liked him
         *  so its a match
         *  else create a match
         */
        if (!isset($match)) {
            $match = new Match;
            $match->user1_id = Auth::user()->id;
            $match->user2_id = $user_id;
            $match->user1_status = 2;
            $match->save();
        } else {
            $match->user2_status = 2;
            $match->save();
        }
    }

    /**
     *  Takes user id as parameter
     *  Returns list of other users within the SEARCH_RADIUS of the user
     */
    private function search() {
        /**
         *  Haversine's formula
         *  a = sin²(Δφ/2) + cos φ1 * cos φ2 * sin²(Δλ/2)
         *  c = 2 * atan2(√a, √(1−a))
         *  d = R * c
         *  where φ is latitude, λ is longitude, R is earth’s radius
         */

        // Get current user data
        $co1 = User::selectRaw('*, RADIANS(latitude) as φ1, RADIANS(longitude) as λ1')->find(Auth::user()->id);
        
        // Get subquery sql for other users data
        $co2 = User::selectRaw('*, ' . $co1->φ1 . ' as φ1, ' . $co1->λ1 . ' as λ1, RADIANS(latitude) as φ2, RADIANS(longitude) as λ2')
                        ->toSql();
        
        // Calculate a
        $a = User::selectRaw('*, POWER(SIN(t1.φ2 - t1.φ1) / 2, 2) + COS(t1.φ1) * COS(t1.φ2) * POWER(SIN(t1.λ2 - t1.λ1) / 2, 2) as a')
                        ->from(DB::raw('(' . $co2 . ') as t1'))
                        ->toSql();

        // Calculate distance
        $distance = User::selectRaw('*, ' . EARTH_RADIUS . ' * 2 * ATAN2(SQRT(t2.a), SQRT(1 - t2.a)) as distance')
                        ->from(DB::raw('(' . $a . ') as t2'))
                        ->toSql();

        // Get users within the SEARCH_RADIUS and of opposite gender
        $users = User::select('*')
                        ->from(DB::raw('(' . $distance . ') as t3'))
                        ->where('id', '!=', $co1->id)
                        ->where('gender', '!=', $co1->gender)
                        ->where('distance', '<', SEARCH_RADIUS)
                        ->get();

        return $users;
    }
}