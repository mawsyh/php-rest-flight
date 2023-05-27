<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);
define('MyConst', TRUE);

require_once 'vendor/autoload.php';
require_once 'db.config.php';
require_once 'db.class.php';
require_once 'functions.php';


$app = new \Flight\Engine();


$app->route('GET /', function () {
    // echo 'http:' . $_SERVER['SERVER_NAME'];
    echo hash_hmac('sha3-384', 'The quick brown fox jumped over the lazy dog.', '');
    // var_dump(db::rs('users'));
});

$app->route('POST /login', function() {
    $email = Flight::request()->data->email;
    $password = Flight::request()->data->password;
    $user = db::row('users', 'email = '. '"'.db::escape($email).'"');
    if(!$user || ($user && !password_verify($password, $user['password']))) {
        return var_dump("Password wrong or user not found!");
    }
    if($user['verify'] === '1') {
        return var_dump("Login successfull");
    }
    if(time() - $user['last_email_sent'] > 300) {
        sendActivation($email, $user['verify']);
        db::update('users', 'last_email_sent = "' . time() .'"', 'email = '. '"'.db::escape($email).'"');
    } else {
        var_dump("You already have an activation email, wait " . 300 - (time() - $user['last_email_sent']) . " seconds and try again later.");
    }
  });
  

$app->route('POST /signup', function () {
    $email = Flight::request()->data->email;
    $password = Flight::request()->data->password;
    $name = Flight::request()->data->name;
    $hashedPassword = password_hash($password,  PASSWORD_BCRYPT);
    if(db::exists('users', 'email = '. '"'.db::escape($email).'"' )) {
        return var_dump("Successfully registered and activation link is sent");
    };
    $verify = sha1(date('Y-m-d H:i:s').mt_rand(1000000,99999999));
    db::insert('users',"`name`, `email`, `password`, `verify`, `last_email_sent`" , '"'.db::escape($name).'", "'.db::escape($email).'","'.$hashedPassword.'","'.$verify.'","'.time().'"');
    sendActivation($email, $verify);
});

$app->route('GET /verify/@verify/@exptime/@sign', function ($verify, $exptime, $sign) {
    if (time() > (int)$exptime) {
        return var_dump("time has past");
    } 
    else {
        $correctSign = sha1($verify. ':' . $exptime . ':'. EMAIL_HASH);
        if ($correctSign !== $sign) return var_dump("code is changed");
        else {
            db::update('users', 'verify = 1', 'verify = "' . $verify .'"');
            return var_dump("verified");
        }
    };
});

$app->route('POST /reset-password', function () {
    $email = Flight::request()->data->email;
    $user = db::row('users', 'email = '. '"'.db::escape($email).'"');
    if(!$user) {
        return var_dump("User not found!");
    }
    if(time() - $user['last_email_sent'] > 300) {
        db::update('users', 'last_email_sent = "' . time() .'"', 'email = '. '"'.db::escape($email).'"');
        sendResetPassword($email);
        var_dump("Reset password email sent!" .db::escape($email). time());
    } else {
        var_dump("You already have an activation email, wait " . 300 - (time() - $user['last_email_sent']) . " seconds and try again later.");
    }
});

$app->route('POST /reset-password/@id/@exptime/@sign', function ($id, $exptime, $sign) {
    $password = Flight::request()->data->password;
    if (time() > (int)$exptime) {
        return var_dump("time has past");
    } 
    else {
        $correctSign = sha1($exptime . ':' . EMAIL_HASH);
        if ($correctSign !== $sign) return var_dump("code is changed");
        else {
            db::update('users', 'email = '. '"'.password_hash($password,  PASSWORD_BCRYPT).'"', 'id = "' . $id .'"');
            return var_dump("verified");
        }
    };
});

$app->route('DELETE /users/@id', function ($id) {
    // Delete user with ID $id
});

$app->start();

?>