<?php

namespace Controllers;

use \Models\User;
use \Models\Problem;

use \Util\Validate;

class UserController extends Controller {

    public function register($f3, $params) {
        $this->generateCsrf($f3, $params);

        $f3->mset([
            'title' => 'Register',
            'content' => $f3->get('THEME') . '/views/users/register.html',
            'navbarItemClasses' => ['register' => 'active']
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

    public function create($f3, $params) {
        if (!$this->checkCsrf($f3, $params))
            $f3->error(403);

        $formErrors = [];

        $username = $f3->get('POST.username');
        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');
        $passwordConfirm = $f3->get('POST.passwordConfirm');

        if ($username === '')
            $formErrors['username'] = 'A username is required.';
        else if (strlen($username) > 16)
            $formErrors['username'] = 'Usernames must be at most 16 characters.';
        else if (!Validate::isAlphaNumericDash($username))
            $formErrors['username'] = 'Usernames can only contain letters, numbers, dashes, or underscores.';
        else if (!Validate::isUnique($f3, 'users', 'username', $username))
            $formErrors['username'] = 'This username is taken. Try another?';

        if ($email === '')
            $formErrors['email'] = 'An email is required.';
        else if (strlen($email) > 255)
            $formErrors['email'] = 'Emails must be at most 255 characters.';
        else if (!Validate::isValidEmail($email))
            $formErrors['email'] = 'This is not a valid email address.';
        else if (!Validate::isUnique($f3, 'users', 'email', $email))
            $formErrors['email'] = 'An account with this email already exists. Try logging in?';

        if ($password === '')
            $formErrors['password'] = 'A password is required.';
        else if (strlen($password) < 6 || 255 < strlen($password))
            $formErrors['password'] = 'Passwords must be between 6 and 255 characters.';

        if ($password !== $passwordConfirm)
            $formErrors['passwordConfirm'] = 'The passwords do not match.';

        if (empty($formErrors)) {
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->save();
            $this->authenticate($f3, $params);
        }
        else {
            $f3->set('formErrors', $formErrors);
            $this->register($f3, $params);
        }
    }
    
    public function login($f3, $params) {
        $this->generateCsrf($f3, $params);

        $f3->mset([
            'title' => 'Login',
            'content' => $f3->get('THEME') . '/views/users/login.html',
            'navbarItemClasses' => ['login' => 'active']
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

    public function authenticate($f3, $params) {
        if (!$this->checkCsrf($f3, $params))
            $f3->error(403);

        $email = $f3->get('POST.email');
        $password = $f3->get('POST.password');

        $formErrors = [];

        $user = new User();
        $user->load(['email = ?', $email]);

        if ($password === '')
            $formErrors['password'] = 'A password is required.';

        if ($email === '')
            $formErrors['email'] = 'An email is required.';
        else if (!Validate::isValidEmail($email))
            $formErrors['email'] = 'This is not a valid email address.';
        else if ($user->dry() || ($password !== '' && !password_verify($password, $user->password)))
            $formErrors['password'] = 'Incorrect email or password.';
            
        if (empty($formErrors)) {
            $f3->set('SESSION.user.id', $user->id);
            $f3->set('SESSION.user.username', $user->username);
            $f3->reroute('/');
        }
        else {
            $f3->set('formErrors', $formErrors);
            $this->login($f3, $params);
        }
    }

    public function logout($f3, $params) {
        $this->checkIfAuthenticated($f3, $params);
        $f3->clear('SESSION');
        $f3->reroute('/');
    }

    public function leaderboard($f3, $params) {
        $users = new User();

        if (!$f3->exists('SESSION.contest')) {
            $users = $users->select(
                'id, username, points',
                NULL,
                [ 'order' => 'points DESC, username ASC' ]
            );

            $f3->mset([
                'users' => $users,
                'title' => 'Leaderboards',
                'content' => $f3->get('THEME') . '/views/users/leaderboard.html',
                'navbarItemClasses' => ['users' => 'active'],
                'rankCounter' => 1
            ]);

            echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
        }
        else {
        }
    }

    public function show($f3, $params) {
        $user = new User();
        $user->load(['username = ?', $params['username']]);
        if ($user->dry())
            $f3->error(404);

        $rank = 1 + $user->count([
            '(points > :points) OR (points = :points AND username < :username)',
            ':points' => $user->points,
            ':username' => $user->username
        ]);

        $problemsAttempted = $f3->get('DB')->exec(
            'SELECT problems.slug AS problem_slug, problems.name AS problem_name, problems.points AS problem_points, submissions.id AS best_submission_id, submissions.points AS best_submission_points
            FROM users_solved_problems_pivot
            LEFT JOIN problems ON users_solved_problems_pivot.problem_id = problems.id
            LEFT JOIN submissions ON users_solved_problems_pivot.best_submission_id = submissions.id
            WHERE users_solved_problems_pivot.user_id = ?
            ORDER BY problems.name ASC',
            $user->id
        );

        $solvedCount = $f3->get('DB')->exec(
            'SELECT COUNT(problem_id) AS solvedCount FROM users_solved_problems_pivot
            WHERE user_id = ?
            GROUP BY user_id',
            $user->id
        )[0]['solvedCount'] ?? 0;

        $f3->mset([
            'user' => $user,
            'userRank' => $rank,
            'userCount' => $user->count(),
            'solvedCount' => $solvedCount,
            'problemsAttempted' => $problemsAttempted,
            'title' => $user->username . '\'s Profile',
            'content' => $f3->get('THEME') . '/views/users/show.html',
            'navbarItemClasses' => ['users' => 'active']
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

}
