<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('admin');

        return UserResource::collection(User::orderBy('name')->paginate(20));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        Gate::authorize('admin');

        $user = User::create($request->validated());

        return (new UserResource($user))->response()->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, User $user): Response
    {
        Gate::authorize('admin');
        abort_if($request->user()->is($user), Response::HTTP_UNPROCESSABLE_ENTITY, 'Você não pode remover a própria conta.');

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }
}
