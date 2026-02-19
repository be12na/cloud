<?php
/**
 * Control password reset
 *
 *
 */
if (!class_exists('Resetter', false)) {
    /**
     * Class Resetter
     *
 *
     */
    class Resetter
    {
        /**
         * Call update user functions
         *
         * @return $message
         */
        public function init()
        {
            $updater = new Updater();
            $resetter = $this;

            $resetpwd = filter_input(INPUT_POST, 'reset_pwd', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $resetconf = filter_input(INPUT_POST, 'reset_conf', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $userh = filter_input(INPUT_POST, 'userh', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            $getrp = filter_input(INPUT_POST, 'getrp', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            if ($resetpwd && $resetconf && $userh && $getrp) {
                if ($resetpwd == $resetconf && $resetter->checkTok($getrp, $userh) === true) {
                    $username = $resetter->getUserFromSha($userh);
                    $new_users = $updater->updateUserPwd($username, $resetpwd);
                    $updater->updateUserFile('password', false, $new_users);
                    $resetter->resetToken($resetter->getMailFromSha($userh));
                }
            }
        }

        /**
         * Get user name from encrypted email
         *
         * @param string $usermailsha user email in SHA1
         *
         * @return username
         */
        public function getUserFromSha($usermailsha)
        {
            $gateKeeper = GateKeeper::getInstance();
            $_USERS = $gateKeeper->getUsers();

            foreach ($_USERS as $value) {
                if (isset($value['email']) && sha1($value['email']) === $usermailsha) {
                    return $value['name'];
                }
            }
            return null;
        }

        /**
         * Get user mail from encrypted email
         *
         * @param string $usermailsha user email in SHA1
         *
         * @return username
         */
        public function getMailFromSha($usermailsha)
        {
            $gateKeeper = GateKeeper::getInstance();
            $_USERS = $gateKeeper->getUsers();
            foreach ($_USERS as $value) {
                if (isset($value['email']) && sha1($value['email']) === $usermailsha) {
                    return $value['email'];
                }
            }
            return null;
        }

        /**
         * Get user name from email
         *
         * @param string $usermail user email
         *
         * @return username
         */
        public function getUserFromMail($usermail)
        {
            $gateKeeper = GateKeeper::getInstance();
            $_USERS = $gateKeeper->getUsers();
            foreach ($_USERS as $value) {
                if (isset($value['email'])) {
                    if ($value['email'] === $usermail) {
                        return $value['name'];
                    }
                }
            }
            return null;
        }

        /**
         * Reset token
         *
         * @param string $usermail user email
         *
         * @return mail to user
         */
        public function resetToken($usermail)
        {
            $_TOKENS = $this->getTokens();
            $tokens = $_TOKENS;
            unset($tokens[$usermail]);
            if (!Utils::saveJson('admin/_content/users/token.json', $tokens)) {
                Utils::setError('error, no token reset');
                return false;
            }
        }

        /**
         * Set token for password recovering
         *
         * @param string $usermail user email
         * @param string $path     path to token.php // DEPRECATED
         *
         * @return mail to user
         */
        public function setToken($usermail, $path = '')
        {
            $resetter = $this;
            $setUp = SetUp::getInstance();

            $_TOKENS = $this->getTokens();
            $tokens = $_TOKENS;

            $path = dirname(dirname(__FILE__));

            $birth = time();
            $salt = $setUp->getConfig('salt');
            $token = sha1($salt.$usermail.$birth);

            $tokens[$usermail]['token'] = $token;
            $tokens[$usermail]['birth'] = $birth;

            if (!Utils::saveJson($path.'/_content/users/token.json', $tokens)) {
                return false;
            } else {
                $message = array();
                $message['name'] = $resetter->getUserFromMail($usermail);
                $message['tok'] = '?rp='.$token.'&usr='.sha1($usermail);
                return $message;
            }
            return false;
        }

        /**
         * Check token validity and lifetime
         *
         * @param string $getrp  time to check
         * @param string $getusr getusr to check
         *
         * @return true/false
         */
        public function checkTok($getrp, $getusr)
        {
            $_TOKENS = $this->getTokens();
            $now = time();

            foreach ($_TOKENS as $key => $value) {
                if (sha1($key) === $getusr) {
                    if ($value['token'] === $getrp) {
                        if ($now < $value['birth'] + 3600) {
                            return true;
                        }
                    }
                }
            }
            return false;
        }


        /**
         * Get available tokens
         *
         * @return $_TOKENS
         */
        public function getTokens()
        {
            $jsonPath = dirname(dirname(__FILE__)).'/_content/users/token.json';
            $phpPath = dirname(dirname(__FILE__)).'/_content/users/token.php';
            // Try JSON first, fallback to legacy PHP with auto-migration
            if (file_exists($jsonPath)) {
                return Utils::loadJson($jsonPath, array());
            }
            if (file_exists($phpPath)) {
                include $phpPath;
                if (isset($_TOKENS) && is_array($_TOKENS)) {
                    Utils::saveJson($jsonPath, $_TOKENS);
                    return $_TOKENS;
                }
            }
            return array();
        }

    }
}
