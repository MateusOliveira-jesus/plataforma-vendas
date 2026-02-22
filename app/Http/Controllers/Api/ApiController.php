<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\Api\ResponseApi;

class ApiController extends Controller
{
    use ResponseApi;

    public function index()
    {
        return $this->successResponse(message: 'API is working!');
    }
}
