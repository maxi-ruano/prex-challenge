<?php

namespace App\Repositories;

use App\Models\User;
use App\Contracts\Repositories\UserRepositoryInterface;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }


    public function findById(int $id)
    {
        return User::find($id);
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = $this->findById($id);
        if ($user) {
            $user->update($data);
        }
        return $user;
    }

    public function delete(int $id)
    {
        return User::destroy($id);
    }
}