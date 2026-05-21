<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserDetailResource;
use App\Http\Resources\UserSimpleResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function store(UserRequest $request)
    {
        $data = $request->validated();

        $profile = $request->file('profile');
        $pathProfile = null;

        if ($profile) {
            $pathProfile = $profile->store('profile_user', 'public');
        }

        $user = DB::transaction(function () use ($data, $pathProfile) {
            return User::create([
                'name' => $data['name'],
                'fullname' => $data['fullname'],
                'email' => $data['email'],
                'password' => $data['password'],
                'profile' => $pathProfile,
            ]);
        });

        return response()->json([
            'message' => 'user berhasil dibuat',
            'data' => (new UserDetailResource($user))->resolve(),
        ], 201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $profile = $request->file('profile');
        $pathProfileBaru = $user->profile;

        if ($profile) {
            $pathProfileBaru = $profile->store('profile_user', 'public');
        }

        $profileLama = $user->profile;

        $user = DB::transaction(function () use ($user, $data, $pathProfileBaru) {
            $payload = [
                'name' => $data['name'],
                'fullname' => $data['fullname'],
                'email' => $data['email'],
                'profile' => $pathProfileBaru,
            ];

            if (! empty($data['password'])) {
                $payload['password'] = $data['password'];
            }

            $user->update($payload);

            return $user->fresh();
        });

        if ($profile && $profileLama && $profileLama !== $pathProfileBaru) {
            Storage::disk('public')->delete($profileLama);
            Storage::delete($profileLama);
        }

        return response()->json([
            'message' => 'user berhasil diubah',
            'data' => (new UserDetailResource($user))->resolve(),
        ]);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|array',
            'id_user.*' => 'required|integer',
        ]);

        $user = User::whereIn('id', $validated['id_user'])->delete();

        return response()->json([
            'message' => $user . ' user berhasil dihapus',
        ]);
    }

    public function restore(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|array',
            'id_user.*' => 'required|integer',
        ]);

        $user = User::onlyTrashed()->whereIn('id', $validated['id_user'])->restore();

        return response()->json([
            'message' => $user . ' user berhasil direstore',
        ]);
    }

    public function forceDelete(Request $request)
    {
        $validated = $request->validate([
            'id_user' => 'required|array',
            'id_user.*' => 'required|integer',
        ]);

        $users = User::onlyTrashed()->whereIn('id', $validated['id_user'])->get();
        $count = $users->count();

        foreach ($users as $user) {
            $user->forceDelete();
        }

        return response()->json([
            'message' => $count . ' user berhasil hapus',
        ]);
    }

    public function trash()
    {
        return UserSimpleResource::collection(User::onlyTrashed()->get());
    }

    public function index(Request $request)
    {
        $size = $request->query('size', 10);
        $search = $request->search;

        $query = User::query();
        $query->when($search, function ($q, $search) {
            $q->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%$search%")
                    ->orWhere('fullname', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        });

        return UserSimpleResource::collection($query->paginate($size));
    }

    public function profile(){
        $user = Auth::user();
        return new UserDetailResource($user);
    }
}
