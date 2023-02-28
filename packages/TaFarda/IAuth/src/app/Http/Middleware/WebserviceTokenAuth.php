<?php

namespace TaFarda\IAuth\app\Http\Middleware;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use TaFarda\IAuth\Models\Admin;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

use Closure;

class WebserviceTokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $admin = Admin::where('webservice_call_token' , $request->webservice_call_token)->first();
        if(!$admin || $admin->status == 0)
            return response()->json([
                'message' => trans('Unauthorized'),
                'data' => null
            ], 403);
        return $next($request);
    }
}
