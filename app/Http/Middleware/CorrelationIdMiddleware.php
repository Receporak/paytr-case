<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelationIdMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->header('X-Correlation-ID')){
            $id = Str::uuid()->toString();
            $request->headers->set('X-Correlation-ID', $id);
        }else{
            $id = $request->header('X-Correlation-ID');
        }

        Log::withContext(['correlation_id' => $id]);

        $response = $next($request);
        $response->headers->set('X-Correlation-ID', $id);

        return $response;
    }
}
