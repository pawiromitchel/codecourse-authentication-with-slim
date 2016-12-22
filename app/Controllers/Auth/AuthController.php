<?php

namespace App\Controllers\Auth;

use App\Models\User;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;
class AuthController extends Controller
{
	public function getSignOut($request, $response)
	{
		$this->auth->logout();
		// flash message
		$this->flash->addMessage('error', 'You have been signed out');
		return $response->withRedirect($this->router->pathFor('home'));
	}
	// signin controller
	public function getSignIn($request, $response)
	{
		return $this->view->render($response, 'auth/signin.twig');
	}

	public function postSignIn($request, $response)
	{
		// use the attempt class
		$auth = $this->auth->attempt(
			$request->getParam('email'),
			$request->getParam('password')
		);

		if (!$auth) {
			// flash message
			$this->flash->addMessage('error', 'Could not sign you in with those details');

			return $response->withRedirect($this->router->pathFor('auth.signin'));
		}
		
		// flash message
		$this->flash->addMessage('success', 'Successfully signed in');
		return $response->withRedirect($this->router->pathFor('home'));
	}

	// signup controller
	public function getSignUp($request, $response)
	{
		return $this->view->render($response, 'auth/signup.twig');
	}

	public function postSignUp($request, $response)
	{

		$validation = $this->validator->validate($request, [
			'email' => v::noWhitespace()->notEmpty()->emailAvailable(),
			'name' => v::notEmpty()->alpha(),
			'password' => v::noWhitespace()->notEmpty(),
		]);

		if ($validation->failed()) {
			return $response->withRedirect($this->router->pathFor('auth.signup'));
		}

		$user = User::create([
			'email' => $request->getParam('email'),
			'name' => $request->getParam('name'),
			'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
		]);

		// flash a message
		$this->flash->addMessage('info', 'You have been signed up');

		// log the user directly in
		$this->auth->attempt($user->email, $request->getParam('password'));

		return $response->withRedirect($this->router->pathFor('home'));
	}
}