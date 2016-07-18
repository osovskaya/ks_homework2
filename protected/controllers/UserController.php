<?php

if (!file_exists(__DIR__.'/../models/UserModel.php'))
    {
        header('Location: /form');
        exit;
    }
// include model
require(__DIR__.'/../models/UserModel.php');

class UserController
{
    /**
     * @return bool
     */
    public static function addUser()
    {
        if(empty($_POST))
        {
            require_once(__DIR__.'/../views/form.php');
            exit;
        }

        // add user to database
        $user = UserModel::addUser();

        // update text file
        if (!self::updateTextFile($_SERVER['DOCUMENT_ROOT'] . $user['about'], $user['euro2016']))
        {
            header('Location: /form');
            exit;
        }

        session_start();
        $_SESSION['userid'] = $user['id'];
        $_SESSION['message'] = 'Successfully saved user';

        if (!file_exists(__DIR__.'/../views/form.php'))
        {
            header('Location: /form');
            exit;
        }
        require(__DIR__.'/../views/form.php');
    }

    /**
     * @return bool
     */
    public static function getUserInfo()
    {
        session_start();
        if (!empty($_SESSION['userid']))
        {
            $user = UserModel::getUserInfo($_SESSION['userid']);

            if ($user)
            {
                $user['about'] = file_get_contents($_SERVER['DOCUMENT_ROOT'] . $user['about']);
            }
        }

        if (!file_exists(__DIR__.'/../views/aboutmyself.php'))
        {
            header('Location: /form');
            exit;
        }
        require(__DIR__.'/../views/aboutmyself.php');
    }

    /**
     * @param $fileName
     * @param $text
     * @return bool|string
     */
    public static function updateTextFile($fileName, $text)
    {
        $fp = fopen($fileName, 'a+');
        if (!$fp) return false;
        if (!fwrite($fp, "\n My opinion about ukrainian football: ".$text)) return false;
        fclose($fp);

        return file_get_contents($fileName);
    }
}
