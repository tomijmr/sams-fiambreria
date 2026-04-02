<?php

class AuthController extends Controller
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $userModel = new User();
            $user = $userModel->findByUsername($username);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $this->redirect('/dashboard');
            }

            $_SESSION['error'] = 'Usuario o clave incorrectos';
            $this->redirect('/login');
        }

        $this->view('auth/login');
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('/login');
    }
}
