<?php

namespace App\Traits;

trait ApiResponserTrait{

    protected function successResponse($data, $message = null, $statusCode = 200)
	{
		return response()->json([
			'success'	=> true,
			'message' 	=> $message, 
			'data' 		=> $data
		], $statusCode);
	}

	protected function errorResponse($data = null, $message = null, $statusCode)
	{
		return response()->json([
			'success'	=> false,
			'message' 	=> $message,
			'data' 		=> $data
		], $statusCode);
	}

	protected function respondCreated($data, $message="Created")
	{
		return $this->successResponse($data, $message, 201);
	}

	protected function respondAccepted($data, $message="Accepted")
	{
		return $this->successResponse($data, $message, 202);
	}

	protected function respondUnauthorized($data, $message="Unauthorized")
	{
		return $this->errorResponse($data, $message, 401);
	}

	protected function respondForbidden($data, $message="Forbidden")
	{
		return $this->errorResponse($data, $message, 403);
	}

	protected function respondNotFound($data, $message="Not Found")
	{
		return $this->errorResponse($data, $message, 404);
	}


}