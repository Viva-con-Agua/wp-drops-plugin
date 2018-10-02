<?php

/**
 * Class DropsUserCreator
 * The class creates a wordpress user out of the given data.
 * First it checks if there is already an existing user and if not, we can create one considering its meta data
 */
class DropsUserCreator
{

    private $requiredUserData = array('user_login', 'user_nicename', 'user_email', 'display_name');
    private $requiredUserMeta = array('nickname', 'first_name', 'last_name', 'mobile', 'residence', 'birthday', 'gender', 'nation', 'city', 'region');

    /**
     * @var UserDataHandlerInterface $dataHandler
     */
    private $dataHandler;

    /**
     * @var array $userData
     */
    private $userData;

    public function __construct(array $userData)
    {
        $this->userData = $userData;
    }

    /**
     * Initializing function on calling the entry of an user
     * Checks if there is an existing user with the given id
     * If there is no user, a user with its usermeta data will be created
     * @return DropsResponse
     */
    public function run()
    {

        // Check if userdata is complete
        $invalidFields = $this->validateUserData();
        $isValid = empty($invalidFields);

        if (!$isValid) {

            ob_start();
            var_dump($this->userData);
            $userData = ob_get_clean();

            return (new DropsResponse())
                ->setCode(400)
                ->setContext(__CLASS__)
                ->setMessage('Missing parameters: ' . implode(", ", $invalidFields) . ' | userdata: [' . $userData . ']');
        }

        // Check if user already exists
        $user = $this->dataHandler->getUserByEMail($this->userData['user_email']);

        if (empty($user)) {

            $userId = $this->createUser();

            if (!$userId) {
                return (new DropsResponse())
                    ->setCode(400)
                    ->setContext(__CLASS__)
                    ->setMessage('Database error during user creation! Parameters: ' . implode(', ', $this->userData));
            }

            $isUserMetaCreated = $this->createUserMeta($userId);

            if (!$isUserMetaCreated) {
                return (new DropsResponse())
                    ->setCode(400)
                    ->setContext(__CLASS__)
                    ->setMessage('Database error during usermeta creation! [ID: ' .  $userId . '] Parameters: ' . implode(', ', $this->userData));
            }

            return (new DropsResponse())
                ->setCode(200)
                ->setContext(__CLASS__)
                ->setMessage('User has been created! [ID: ' .  $userId . ']');

        }

        return (new DropsResponse())
            ->setCode(400)
            ->setContext(__CLASS__)
            ->setMessage('User already exists! [ID: ' .  $user->ID . ']');

    }

    /**
     * @param UserDataHandlerInterface $dataHandler
     */
    public function setDataHandler(UserDataHandlerInterface $dataHandler)
    {
        $this->dataHandler = $dataHandler;
    }

    /**
     * Prepares the data and creates the user entry
     * @return false|int
     */
    private function createUser()
    {

        $userData = array(
            'user_login' => $this->userData['user_login'],
            'user_pass' => '',
            'user_nicename' => $this->userData['user_nicename'],
            'user_email' => $this->userData['user_email'],
            'user_url' => '',
            'user_registered' => date('Y-m-d H:i:s', time()),
            'user_activation_key' => '',
            'user_status' => 1,
            'display_name' => $this->userData['display_name']
        );

        return $this->dataHandler->createUser($userData);

    }

    /**
     * Prepares the data and creates the usermeta data entry
     */
    private function createUserMeta($userId)
    {

        $userMetaData = array(
            'nickname' => $this->userData['nickname'],
            'first_name' => $this->userData['first_name'],
            'last_name' => $this->userData['last_name'],
            'description' => '',
            'rich_editing' => 'true',
            'comment_shortcuts' => 'false',
            'use_ssl' => '0',
            Config::get('DB_PREFIX') . '_capabilities' => 'a:1:{s:9:"supporter";b:1;}',
            Config::get('DB_PREFIX') . '_user_level' => '0',
            'dismissed_vca1312_pointers' => 'wp330_toolbar,wp330_media_uploader,wp330_saving_widgets',
            'show_welcome_panel' => '1',
            Config::get('DB_PREFIX') . '_dashboard_quick_press_last_post_id' => '3',
            'vca_asm_last_pass_reset' => time(),
            'vca_asm_last_activity' => time(),
            'mail_switch' => 'none',
            'pool_lang' => (isset($this->userData['pool_lang']) ? DropsDataMapper::map('nation', $this->userData['pool_lang']) : 'de'),
            'default_password_nag' => '',
            Config::get('DB_PREFIX') . '_user-settings' => 'tml1=2&tml0=0&mfold=o&posts_list_mode=list&unfold=0',
            Config::get('DB_PREFIX') . '_user-settings-time' => time(),
            'mobile' => $this->userData['mobile'],
            'residence' => $this->userData['residence'],
            'birthday' => $this->userData['birthday'],
            'gender' => $this->userData['gender'],
            'simple-local-avatar' => '',
            'nation' => DropsDataMapper::map('nation', $this->userData['nation']);
            'city' => DropsDataMapper::map('city', $this->userData['city']);
            'region' => DropsDataMapper::map('city', $this->userData['city']);
        );

        return $this->dataHandler->createUserMeta($userId, $userMetaData);

    }

    /**
     * Validate the received userdata for completeness
     * @return array
     */
    private function validateUserData()
    {

        $invalidFields = array();

        foreach ($this->requiredUserData as $entry) {
            if (!isset($this->userData[$entry])) {
                $invalidFields[] = $entry;
            }
        }

        foreach ($this->requiredUserMeta as $entry) {
            if (!isset($this->userData[$entry])) {
                $invalidFields[] = $entry;
            }
        }

        return $invalidFields;

    }

}