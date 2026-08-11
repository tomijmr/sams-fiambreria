<?php

class AuthController extends Controller
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            csrf_verify();
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            $userModel = new User();
            $user = $userModel->findByUsername($username);

            if ($user && $userModel->isLocked($user)) {
                $_SESSION['error'] = 'Cuenta bloqueada temporalmente por intentos fallidos. Proba de nuevo en unos minutos.';
                $this->redirect('/login');
            }

            if ($user && password_verify($password, $user['password'])) {
                $userModel->resetAttempts((int)$user['id']);
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                Audit::record('login', 'user', (int)$user['id'], 'Inicio de sesion exitoso');
                $this->redirect('/dashboard');
            }

            if ($user) {
                $userModel->registerFailedAttempt((int)$user['id']);
            }
            Audit::record('login_failed', 'user', $user['id'] ?? null, 'Intento fallido para usuario "' . $username . '"');

            $_SESSION['error'] = 'Usuario o clave incorrectos';
            $this->redirect('/login');
        }

        $this->view('auth/login');
    }

    public function logout(): void
    {
        if (Auth::check()) {
            Audit::record('logout', 'user', (int)$_SESSION['user_id'], 'Cierre de sesion');
        }
        session_destroy();
        $this->redirect('/login');
    }
}
