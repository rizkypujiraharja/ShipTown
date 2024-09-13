<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserMeStoreRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

/**
 * Class UserMeController.
 */
class UserMeController extends Controller
{
    public function index(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function store(UserMeStoreRequest $request): UserResource
    {
        $request->user()->update($request->validated());

        return UserResource::make($request->user()->load('roles'));
    }
}
