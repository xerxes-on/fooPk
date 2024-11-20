<?php
/**
 * @copyright   2018 Lindenvalley GmbH (http://www.lindenvalley.de/)
 * @author      Andrey Rayfurak <andrey.rayfurak@lindenvalley.de>
 * @date        01.12.18
 */

namespace App\Http\Middleware;

use Closure;

/**
 * Class HttpsProtocol
 *
 * @package App\Http\Middleware
 */
class HttpsProtocol
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return (!$request->secure() && in_array(config('app.env'), ['stage', 'production'], true)) ?
            redirect()->secure($request->getRequestUri()) :
            $next($request);
    }
}
