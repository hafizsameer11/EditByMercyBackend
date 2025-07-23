<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function createUser($data){
        return User::create($data);
    }
    public function findByEmail($email){
        return User::where('email', $email)->first();
    }
    public function update($data,$id){
        return User::where('id', $id)->update($data);
    }
    public function getUserByRole($role)
    {
        return User::where('role', $role)->get();
    }
}
