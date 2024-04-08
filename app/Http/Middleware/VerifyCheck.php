<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use App\Helpers\ResponseError;
use App\Traits\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCheck
{
    use ApiResponse;

    protected array $whiteList = [
        null,
        '127.0.0.1',
        '206.54.191.37'
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (empty($this->whiteList)) {
            return $next($request);
        }

        $addr = (string)request()->server('SERVER_ADDR');

        if (in_array($addr, $this->whiteList)) {
            return $next($request);
        }

        return $this->errorResponse(
            ResponseError::ERROR_400,
            'Go to app\Http\Middleware\VerifyCheck.php and add your server ip in $whiteList. Your server ip is: ' . ($addr ?: 'null'),
            Response::HTTP_UNAUTHORIZED
        );
    }

}

