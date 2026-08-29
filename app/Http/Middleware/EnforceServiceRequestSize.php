<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnforceServiceRequestSize
{
    public function handle(Request $request, Closure $next): Response
    {
        $maximum = (int) config('zahir.maximum_request_bytes', 32768);
        $contentLength = $request->headers->get('Content-Length');
        $declared = is_string($contentLength) && ctype_digit($contentLength) ? (int) $contentLength : 0;
        if (($declared > 0 && $declared > $maximum) || strlen($request->getContent()) > $maximum) {
            return response()->json(['message' => 'Request entity too large.'], 413);
        }

        return $next($request);
    }
}
