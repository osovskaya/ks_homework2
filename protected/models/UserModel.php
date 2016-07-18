<?php

require(__DIR__ . '/../helpers/PDOConnection.php');

class UserModel
{
    private static $db;

    private static $fields = array(
        'name', 'secret', 'gender', 'occupation', 'euro2016', 'avatar', 'about', 'notRobot',
    );

    private static $validationRules = array(
        'name' => [
            'type' => 'string',
            'length' => 50,
        ],
        'secret' => [
            'type' => 'string',
            'length' => 50,
        ],
        'gender' => [
            'type' => 'string',
            'value' => ['male', 'female'],
        ],
        'occupation' => [
            'type' => 'string',
            'value' => ['I\'m a student', 'I\'m a worker', 'I\'m a businesman', 'I\'m a free person'],
        ],
        'euro2016' => [
            'type' => 'string',
            'length' => 256,
        ],
        'avatar' => [
            'type' => 'file',
            'mime-type' => 'image/jpeg',
            'size' => 1024000,
        ],
        'about' => [
            'type' => 'file',
            'mime-type' => 'text/plain',
            'size' => 1024000,
        ],
        'notRobot' => [
            'type' => 'string',
            'value' => ['yes',],
        ],
    );

    /**
     * @return bool|mixed
     */
    public static function addUser()
    {
        // check for required and extra fields
        if (!self::checkFields()) return false;

        // validate data
        if (!self::validateFields()) return false;

        // validate files
        if (!self::validateFile()) return false;

        // upload files
        $filesUploaded = self::uploadFiles();
        if (!$filesUploaded) return false;

        // add user to database
        self::$db = PDOConnection::getInstance()->getConnection();
        try
        {
            $query = self::$db->prepare(
                'INSERT INTO `users` (`name`, `secret`, `gender`, `occupation`, `euro2016`, `avatar`, `about`, `notRobot`)
                 VALUES (:name, :secret, :gender, :occupation, :euro2016, :avatar, :about, :notRobot)'
            );

            $query->execute(array(
                'name'       => $_POST['name'],
                'secret'     => $_POST['secret'],
                'gender'     => $_POST['gender'],
                'occupation' => $_POST['occupation'],
                'euro2016'   => $_POST['euro2016'],
                'avatar'     => $filesUploaded['img'],
                'about'      => $filesUploaded['txt'],
                'notRobot'   => $_POST['notRobot'],
            ));

            return self::getUserInfo(self::$db->lastInsertId());
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * @param $id
     * @return bool|mixed
     */
    public static function getUserInfo($id)
    {
        self::$db = PDOConnection::getInstance()->getConnection();
        try
        {
            $query = self::$db->prepare("SELECT * FROM `users` WHERE `id` = :id");
            $query->execute(array('id' => (int) $id));
            return $query->fetch(PDO::FETCH_ASSOC);
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }

    /**
     * @param $data
     * @return bool
     */
    protected static function checkFields()
    {
        foreach($_POST as $key => $value)
        {
            if (array_search($key, array_keys(self::$fields)) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $data
     * @return bool
     */
    protected static function validateFields()
    {
        foreach($_POST as $key => $value)
        {
            if (self::$validationRules[$key]['type'] == 'string')
            {
                if (!is_string($value) && !preg_match('/^[\pL\pM]+$/u', $value))
                {
                    return false;
                }

                $value = strip_tags(htmlentities($value));
            }

            if (array_key_exists('length', self::$validationRules[$key]) &&
                strlen($value) > self::$validationRules[$key]['length'])
            {
                return false;
            }

            if (array_key_exists('value', self::$validationRules[$key]) &&
                array_search($value, self::$validationRules[$key]['value']) === false)
            {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $validateRules
     * @return bool
     */
    protected static function validateFile()
    {
        foreach ($_FILES as $key => $file)
        {
            if ($file['size'] > self::$validationRules[$key]['size'])
            {
                return false;
            }

            if ($file['type'] != self::$validationRules[$key]['mime-type'])
            {
                return false;
            }
        }
        return true;
    }

    protected static function uploadFiles()
    {
        foreach ($_FILES as $file)
        {
            $fileContent = file_get_contents($file['tmp_name']);

            if ($fileContent === false)
            {
                return false;
            }

            if ($file['type'] == 'image/jpeg') $prefix = 'img';
            if ($file['type'] == 'text/plain') $prefix = 'txt';

            $filepath[$prefix] = self::makeFilePath($prefix, $file['name']);
            $filepathFull = $_SERVER['DOCUMENT_ROOT'] . $filepath[$prefix];

            if (file_put_contents($filepathFull, $fileContent) === false)
            {
                return false;
            }
        }
        return $filepath;
    }

    /**
     * @param $prefix
     * @param $oldname
     * @return string
     */
    protected static function makeFilePath($prefix, $oldname)
    {
        $name = explode('.', $oldname);
        $extension = array_pop($name);

        return '/uploads/' . $prefix . '/' . implode('', $name) . '_' . time() . '.' . $extension;
    }
}
