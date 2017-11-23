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

After clonig the repository to the plugins folder, the second step is to go to the plugin folder and create the config.inc.php out of the 
config.inc.php.scel file and fill out the configuration for the database and the connection to drops

```
$_CONFIG['DB_USER_TABLE'] = ''; // predefined table from wordpress
$_CONFIG['DB_USERMETA_TABLE'] = ''; // predefined table from wordpress
$_CONFIG['DB_SESSION_TABLE'] = ''; // table will be created
$_CONFIG['DB_META_TABLE'] = ''; // table will be created

$_CONFIG['CLIENT_ID'] = '';
```

After editing the config file, the necessary tables will be added to the database on plugin actionvation.

You can now see the menu point <b>Drops</b> in your Wordpress admin area. Go there and navigate to the settings tab. To set the required options for the communication with drops.

Usage
=======

- Login:
On the first call of the Pool, if the user is not logged in yet, he gets redirected to the <DROPS_LOGIN_URL> to login into the service
After that it establishes an handshake to exchange the access token for further communication

- User creation:
New user data will be pushed from the drops microservice to the plugin, which adds a new user.
Just POST the parameters <i>hash</i> as a string for the vailation and the parameter <i>user</i> as a json data model
The expected data model must be:

```
{
    'user_login' => 'exampleuser', // string
    'user_nicename' => 'example-user', // string
    'user_email' => 'example@user.de', // string
    'display_name' => 'Example U.', // string
    'user_name' => 'Example User', // string
    'usermeta' => {
        'nickname' => 'Example User', // string
        'first_name' => 'Example', // string
        'last_name' => 'User', // string
        'mobile' => '123456789', // string
        'residence' => 'ExampleCity', // string
        'birthday' => 585352800, // long
        'gender' => 'male', // string
        'nation' => 40, // int
        'city' => 1, // int
        'region' => 1 // int
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
        ["birthday"] => long
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