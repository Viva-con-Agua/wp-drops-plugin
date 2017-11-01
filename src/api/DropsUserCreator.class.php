<?php

/**
 * Class DropsUserCreator
 * The class creates a wordpress user out of the given data.
 * First it checks if there is already an existing user and if not, we can create one considering its meta data
 */
class DropsUserCreator
{

    private $requiredUserData = array('ID', 'user_login', 'user_nicename', 'user_email', 'display_name', 'usermeta');
    private $requiredUserMeta = array('nickname', 'first_name', 'last_name', 'mobile', 'residence', 'birthday', 'gender', 'nation', 'city', 'region');

    /**
     * @var DropsDataHandler $dataHandler
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
     */
    public function run()
    {
        // Check if userdata is complete
        $isValid = $this->validateUserData();

        if (!$isValid) {
            return array(
                'code' => 400,
                'message' => 'User could not be created! Missing parameters!'
            );
        }

        // Check if user already exists
        $user = $this->dataHandler->getUser($this->userData);

        if (empty($user)) {

            $isUserCreated = $this->createUser();

            if (!$isUserCreated) {
                return array(
                    'code' => 400,
                    'message' => 'User could not be created! Error during user creation'
                );
            }

            $isUserMetaCreated = $this->createUserMeta();

            if (!$isUserMetaCreated) {
                return array(
                    'code' => 400,
                    'message' => 'User could not be created! Error during usermeta creation'
                );
            }

        } else {
            return array(
                'code' => 400,
                'message' => 'User already exists!'
            );
        }

        return array(
            'code' => 200,
            'message' => 'User has been created!'
        );

    }

    /**
     * @param DropsDataHandler $dataHandler
     */
    public function setDataHandler(DropsDataHandler $dataHandler)
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
            'ID' => $this->userData['id'],
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
    private function createUserMeta()
    {

        $userMetaData = array(
            'nickname' => $this->userData['usermeta']['nickname'],
            'first_name' => $this->userData['usermeta']['first_name'],
            'last_name' => $this->userData['usermeta']['last_name'],
            'description' => '',
            'rich_editing' => 'true',
            'comment_shortcuts' => 'false',
            'use_ssl' => '0',
            'vca1312_capabilities' => 'a:1:{s:9:"supporter";b:1;}',
            'vca1312_user_level' => '0',
            'dismissed_vca1312_pointers' => 'wp330_toolbar,wp330_media_uploader,wp330_saving_widgets',
            'show_welcome_panel' => '1',
            'vca1312_dashboard_quick_press_last_post_id' => '3',
            'vca_asm_last_pass_reset' => time(),
            'vca_asm_last_activity' => time(),
            'default_password_nag' => '',
            'vca1312_user-settings' => 'tml1=2&tml0=0&mfold=o&posts_list_mode=list&unfold=0',
            'vca1312_user-settings-time' => time(),
            'mobile' => $this->userData['usermeta']['mobile'],
            'residence' => $this->userData['usermeta']['residence'],
            'birthday' => $this->userData['usermeta']['birthday'],
            'gender' => $this->userData['usermeta']['gender'],
            'simple-local-avatar' => '',
            'nation' => $this->userData['usermeta']['nation'],
            'city' => $this->userData['usermeta']['city'],
            'region' => $this->userData['usermeta']['region']
        );

        return $this->dataHandler->createUserMeta($this->userData['ID'], $userMetaData);

    }

    /**
     * Validate the received userdata for completeness
     * @return bool
     */
    private function validateUserData()
    {

        foreach ($this->requiredUserData as $entry) {
            if (!isset($this->userData[$entry])) {
                return false;
            }
        }

        foreach ($this->requiredUserMeta as $entry) {
            if (!isset($this->userData['usermeta'][$entry])) {
                return false;
            }
        }

        return true;

    }

}