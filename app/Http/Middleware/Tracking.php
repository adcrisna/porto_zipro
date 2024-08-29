<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Visitor;

class Tracking
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $time = date('Y-m-d');
        
        $ip = $request->ip();
        $path = $request->path();
        $traffic = Visitor::where('ip',$ip)->latest()->first();
        if(auth('api')->check()) {
            $user = auth('api')->User();
        }

        if($traffic && $path == $traffic->path && $time == date_format(date_create($traffic->created_at),"Y-m-d")) {
            $traffic->visits++; // $traffic->visits = $traffic->visits + 1;
            $traffic->path = $path;
            // $traffic->user_id = $user->id ?? null;
            $traffic->update();
        }else if ($traffic && $time >= date_format(date_create($traffic->created_at),"Y-m-d")) {
            $traffic = new Visitor;
            $traffic->ip = $request->ip();
            $traffic->path = $path;
            $traffic->user_id = $user->id ?? null;
            $traffic->save();
        }else {
            $traffic = new Visitor;
            $traffic->ip = $request->ip();
            $traffic->path = $path;
            $traffic->user_id = $user->id ?? null;
            $traffic->save();
        }
        return $next($request);
    }
}
