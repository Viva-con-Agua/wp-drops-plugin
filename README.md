# wp-drops-plugin

Drops connector plugin for wordpress
====================================

The drops plugin is responsible for the new user handling of the wordpress-based POOL from Viva con Agua.
It connects the POOL to the drops service (supporter management microservice) to provide user login, registration process 
and user profile management.

- It implements the user login of Viva con Agua supporters. If the supporter initially arrives on the pool, the user gets 
redirected to the drops service for login/registration.
 
- If the user executes the login action, the service ensures the login into the Pool system.
 
- When there is a registration of a new user in the drops microservice, it pushes the the necessary user data to the pool
to provide synchronicity of the user data. Same on updating users or geography informations

- After the user updates it's profile in the pool, the plugin pushed the user data to the drops microservice to ensure 
the synchronicity in reverse. Same on deleting an existing user from the pool.

Install
=======

To install the plugin, you have to navigate to the plugins folder of the wordpress installation and clone the repository: 

```
git clone https://github.com/Viva-con-Agua/wp-drops-plugin.git
```

After clonig the repository to the plugins folder, the second step is to go to the plugin folder and create the config.inc.php out of the 
config.inc.php.scel file and fill out the configuration for the database and the connection to drops

```
$_CONFIG['LOGIN_ENABLED'] = false;

$_CONFIG['DB_PREFIX'] = 'vca1312';

// Exitsting wordpress tables
$_CONFIG['DB_USER_TABLE'] = $_CONFIG['DB_PREFIX'] . '_users';
$_CONFIG['DB_USERMETA_TABLE'] = $_CONFIG['DB_PREFIX'] . '_usermeta';

// Tables will be created on plugin activation
$_CONFIG['DB_SESSION_TABLE'] = $_CONFIG['DB_PREFIX'] . '_sessions';
$_CONFIG['DB_META_TABLE'] = $_CONFIG['DB_PREFIX'] . '_drops_meta';

$_CONFIG['DB_DROPS_LOG'] = $_CONFIG['DB_PREFIX'] . '_drops_logs';
$_CONFIG['DB_GEOGRAPHY'] = $_CONFIG['DB_PREFIX'] . '_vca_asm_geography';
```

After editing the config file, the necessary tables will be added to the database on plugin actionvation.

You can now see the menu point <b>Drops</b> in your Wordpress admin area. Go there and navigate to the settings tab. To set the required URL options for the communication with drops.

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
	'nickname' => 'Example User', // string
	'first_name' => 'Example', // string
	'last_name' => 'User', // string
	'mobile' => '123456789', // string
	'residence' => 'ExampleCity', // string
	'birthday' => 585352800, // long
	'gender' => 'male', // string
	'nation' => "Germany", // string
	'city' => "Hamburg", // string
}
```

- User update: When a user profile is updated in wordpress, the plugin pushes the data to the drops service user a PUT request
sending the following JSON encoded data structure:

```
{ 
    ["email"] => string 
    ["firstName"] => string
    ["lastName"] => string 
    ["mobilePhone"] => string 
    ["placeOfResidence"] => string 
    ["birthday"] => long (timestamp in ms)
    ["sex"] => string 
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