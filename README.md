# wp-drops-plugin

Drops connector plugin for wordpress
====================================

The drops plugin is responsible for the new user handling of the wordpress-based POOL from Viva con Agua.
It connects the POOL to the drops service (supporter management microservice) to provide user login, registration process 
and user profile management.

- It implements the user login of Viva con Agua supporters. If the supporter initially arrives on the pool, the user gets 
redirected to the drops service for login/registration.
 
- If the user executes a login, the service ensures the login in the Pool system.
 
- When there is a registration of a new user in the drops microservice, it pushes the the necessary user data to the pool
to provide synchronicity of the user data.

- After the user updates it's profile in the pool, the plugin pushed the user data to the drops microservice to ensure 
the synchronicity in reverse

Install
=======

To install the plugin, you have to navigate to the plugins folder of the wordpress installation and clone the repository: 

```
git clone https://github.com/Viva-con-Agua/wp-drops-plugin.git
```

After the repository has been cloned, it is necessary to add the session table to the database

```
CREATE TABLE `<SESSION_TABLE_NAME>` (
  `temporary_session_id` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `user_session` text NOT NULL,
  `drops_session_id` varchar(64) NOT NULL,
  `token_type` varchar(64) DEFAULT NULL,
  `access_token` text NOT NULL,
  `refresh_token` varchar(64) DEFAULT NULL,
  `expiry_timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

```

After the database has been updated, the third step is to go to the plugin folder and create the config.inc.php out of the 
config.inc.php.scel file and fill out the configuration for the database and the connection to drops

```
$_CONFIG['DB_SESSION_TABLE'] = '';
$_CONFIG['DB_USER_TABLE'] = '';
$_CONFIG['DB_USERMETA_TABLE'] = '';

$_CONFIG['CLIENT_ID'] = '';

$_CONFIG['USER_ACCESS_HASH'] = '';
$_CONFIG['DROPS_LOGFILE'] = '';

$_CONFIG['DROPS_BASE_URL'] = '';
$_CONFIG['DROPS_LOGIN_URL'] = '';
$_CONFIG['DROPS_AUTHORIZATION_URL'] = '';
$_CONFIG['DROPS_ACCESSTOKEN_URL'] = '';
```

Usage
=======

- Login:
On the first call of the Pool, if the user is not logged in yet, he gets redirected to the <DROPS_LOGIN_URL> to login into the service
After that it establishes an handshake to exchange the access token for further communication

- User creation:
New user data will be pushed from the drops microservice to the plugin, which adds a new user.
The expected data model must be:

```
{
    'user_login' => 'exampleuser',
    'user_nicename' => 'example-user',
    'user_email' => 'example@user.de',
    'display_name' => 'Example U.',
    'user_name' => 'Example User',
    'usermeta' => {
        'nickname' => 'Example User',
        'first_name' => 'Example',
        'last_name' => 'User',
        'mobile' => '123456789',
        'residence' => 'ExampleCity',
        'birthday' => '585352800',
        'gender' => 'male',
        'nation' => '40',
        'city' => '1',
        'region' => '1'
    }
}
```

- User update: When a user profile is updated in wordpress, the plugin pushes the data to the drops service user a POST request
sending the following data structure:

```
{ 
    ["ID"] => int 
    ["user_login"] => string 
    ["user_email"] => string
    ["user_name"] => string 
    ["usermeta"] => { 
        ["first_name"] => string
        ["last_name"] => string
        ["mobile"] => string
        ["residence"] => string
        ["birthday"] => string
        ["gender"] => string 
        ["nation"] => int
        ["city"] => int 
        ["region"] => int 
    } 
} 
    
```

Response
========

The response of the calls for user creation has the following structure:
 
```
{
    "code":INT,
    "message":STRING,
    "context":STRING
}
```