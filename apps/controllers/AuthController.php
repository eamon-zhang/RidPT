<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/11/28
 * Time: 22:39
 */

namespace apps\controllers;

use apps\models\User;
use apps\models\form\UserLoginForm;
use apps\models\form\UserRegisterForm;

use Mix\Http\Controller;


class AuthController extends Controller
{

    public function actionRegister()
    {
        if (app()->request->isPost()) {
            $user = new UserRegisterForm();
            $user->importAttributes(app()->request->post());
            $error = $user->validate();
            if (count($error) > 0) {
                return $this->render("errors/action_fail.html.twig", [
                    'title' => 'Register Failed',
                    'msg' => $error->get(0)
                ]);
            } else {
                $user->flush();  // Save this user in our database and do clean work~

                if ($user->status == User::STATUS_CONFIRMED) {
                    return app()->response->redirect("/index");
                } else {
                    return $this->render('auth/register_pending.html.twig', [
                        "confirm_way" => $user->confirm_way,
                        "email" => $user->email
                    ]);
                }
            }
        } else {
            return $this->render("auth/register.html.twig");
        }
    }

    public function actionConfirm()
    {
        // TODO User Confirm Action
    }

    public function actionRecover()
    {
        // TODO User Recover Action
    }

    public function actionLogin()
    {
        if (app()->request->isPost()) {
            $login = new UserLoginForm();
            $login->importAttributes(app()->request->post());
            $error = $login->validate();

            if (count($error) > 0) {
                return $this->render("auth/login.html.twig", ["username" => $login->username, "error_msg" => $error->get(0)]);
            } else {
                $success = $login->createUserSession();
                if ($success) {
                    $login->updateUserLoginInfo();
                    $return_to = app()->session->pop('login_return_to') ?? '/index';
                    return app()->response->redirect($return_to);
                } else {
                    return $this->render('errors/action_fail.html.twig',['title'=> 'Login Failed','msg' => 'Reach the limit of Max User Session.']);
                }
            }
        } else {
            return $this->render("auth/login.html.twig");
        }
    }

    public function actionLogout()
    {
        // TODO add CSRF protect
        app()->user->deleteUserThisSession();
        return app()->response->redirect('/auth/login');
    }
}
