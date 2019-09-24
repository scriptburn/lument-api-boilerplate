<?php

namespace App\Http\Middleware;
use App\User;
use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class JwtMiddleware
{
	public function handle($request, Closure $next, $guard = null)
	{
		//$token = $request->get('token');
		$token = $this->parseToken($request);

		if (!$token)
		{
			// Unauthorized response if token not there
			return response()->json([
				'error' => 'Token not provided2.',
			], 401);
		}
		try {
			$credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
		}
		catch (ExpiredException $e)
		{
			return response()->json([
				'error' => 'Provided token is expired.',
			], 400);
		}
		catch (Exception $e)
		{
			return response()->json([
				'error' => 'An error while decoding token.',
			], 400);
		}
		$user = User::find($credentials->sub);
		// Now let's put the user in the request class so that you can grab it from there
		$request->auth = $user;

		return $next($request);
	}
	public function checkForToken(Request $request)
	{
		if (!$this->auth->parser()->setRequest($request)->hasToken())
		{
			throw new UnauthorizedHttpException('jwt-auth', 'Token not provided1');
		}
	}
	public function parseToken($request)
	{
		if ($request->header('Authorization'))
		{
			list($type, $data) = explode(" ", $request->header('Authorization'), 2);

			return $data;
		}
	}
}