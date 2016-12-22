<?php

namespace App\Auth;

use App\Models\User;

class Auth
{
	public function user()
	{
		if (isset($_SESSION['user'])) {
			return User::find($_SESSION['user']);
		}
	}

	public function check()
	{
		if (isset($_SESSION['user'])) {
			return isset($_SESSION['user']);
		}
	}

	public function attempt($email, $password)
	{
		// get the data of the attempted user
		$user = User::where('email' , $email)->first();
		
		// check if the user exists 
		if (!$user) {
			return false;
		}

		// check if password is valid
		if (password_verify($password, $user->password)) {
			$_SESSION['user'] = $user->id;
			return true;
		}
		
		return false;
	}

	public function logout()
	{
		unset($_SESSION['user']);
	}
}