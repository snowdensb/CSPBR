<?php

namespace NCSC;

/**
 * Class AuthTokenMgr -- code to manage the auth_token rememberMe feature,
 * on both the db and cookie sides
 * 
 * implementation based largely on suggested solution at:
 * https://paragonie.com/blog/2015/04/secure-authentication-php-with-long-term-persistence
 *
 */
class AuthTokenMgr extends ModelBase
{
	const SELECTOR_LENGTH = 10;
	const COOKIE_NAME = 'rmInfo';

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * @return mixed
	 */
	public function getCookie() {
		return $this->f3->get('COOKIE.'.self::COOKIE_NAME);
	}
	
	/**
	 * @param $userId
	 */
	public function generateAndStoreNewAuthToken($userId) {
		\Wyolution\Logger::debug('in generateAndStoreNewAuthToken');
		
		$expirationInterval = $this->f3->get('REMEMBER_ME_EXPIRE_INTERVAL');

		list($rmInfo, $expiresAt) = $this->generateAndSaveAuthTokenRecord($userId, $expirationInterval);

		if ($rmInfo != '') {
			setcookie(self::COOKIE_NAME, $rmInfo, $expiresAt);
		} // else -- failed to save in db, too bad but not worth failing the login
		  // user can try again next time
		return ;
	}
	
	/**
	 * @param $rmInfo -- the user's stored authToken / cookie
	 * @param string $tokenType -- valid types are enums in db auth_tokens/tokenType
	 * @return null -- if the authToken does not map to a valid record / userId
	 *         user -- a hash like a user record from the db but including 
	 *                 status info such as 'rememberMe' and 'expired'
	 * Caller needs to examine the result and compose an appropriate error message
	 * for the end user
	 */
	public function getUserFromAuthToken($rmInfo, $tokenType='LOGIN_COOKIE') {
		\Wyolution\Logger::debug("in getUserFromAuthToken, tokenType = $tokenType");
		$user = null;
		
		// Split the authToken/cookie into selector + validator
		$selector = substr($rmInfo, 0, self::SELECTOR_LENGTH);
		$validator = substr($rmInfo, self::SELECTOR_LENGTH);

		// Use selector to see if the authToken is in the db
		$authTokenMapper = new \Wyolution\Mapper($this->db, 'auth_token_user_view',
												null,			// return all fields
												0); 			// don't use cache

		$authTokenMapper->load(array('selector = ? AND tokenType = ?', $selector, $tokenType));
		if (!$authTokenMapper->dry()) {
			if ($authTokenMapper->validator == $validator) {
				// We have a token of the right type in the db.
				// So we're going to return the corresponding user info to the caller.

				// But we need to do some more work to pass along the correct/complete
				// status
				$user = array(
					'userName' => $authTokenMapper->username,
					'name' => $authTokenMapper->name,
					'accessLevel' => $authTokenMapper->accessLevel,
					'userId' => $authTokenMapper->userId,
					//'rememberMe' => 'on',
					//'expired' => true,
					'active' => $authTokenMapper->active
				);
			}
			if ($authTokenMapper->expiresTimestamp >= time()) {
				if ($tokenType=='LOGIN_COOKIE') {
					$user['rememberMe'] = true;
		}
			}
			else {
				// token has expired, caller needs to inform user in appropriate terms
				$user['expired'] = true;
				// And we pull the authtoken from the db, no use leaving it lying around
				$this->forgetAuthToken($rmInfo);
			}
		} // else, just return the null user record
		return $user;
	}

	/**
	 * @param $rmInfo
	 */
	public function forgetAuthToken($rmInfo) {
		\Wyolution\Logger::debug('in forgetAuthToken');

		// Use selector to see if the auth_token is in the db
		$selector = substr($rmInfo, 0, self::SELECTOR_LENGTH);
		$authTokenMapper = new \Wyolution\Mapper($this->db, 'auth_tokens',
												 null,			// return all fields
												 0); 			// don't use cache
		$authTokenMapper->load(array('selector = ?', $selector));
		if (!$authTokenMapper->dry()) {
			$authTokenType = $authTokenMapper->tokenType;
			$authTokenMapper->erase();
			if ($authTokenType == 'LOGIN_COOKIE') {
				// remove the cookie from the user's machine
				setcookie(self::COOKIE_NAME, '', time() - 3600);
			}
		}
	}


	/**
	 * @param $userId
	 * @param $expirationInterval
	 * @return array
	 */
	public function generateAndSaveAuthTokenRecord($userId, $expirationInterval, $tokenType='LOGIN_COOKIE') {
		\Wyolution\Logger::debug('in generateAndSaveAuthTokenRecord');
		$rmInfo = '';
		
		$now = time(); // get UTC time
		$expiresAt = $now + $expirationInterval;
		// Generate a new 'selector', needs to be a unique value
		// Is there any reason not to use something simple like the string 
		// value of the current time?
		// We think that time() will always return the same number of digits,
		// but let's just make sure we alway get the same number from the right.
		$selector = substr("$now", -1 * self::SELECTOR_LENGTH);

		// Generate a new 'validator' token
		// random_bytes is preferred but may not be available on all platforms / dev machines
		$binValidator = '';
		if (function_exists('random_bytes')) {
			$binValidator = random_bytes(32);
		} 
		else if (function_exists('mcrypt_create_iv')) {
			$binValidator = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
		}
		else {
			\Wyolution\Logger::warning("no random number generator found, can't create auth tokens");
		}
		
		if (!empty($binValidator)) {
			$hexValidator = \bin2hex($binValidator);

			// Hash the validator for the db, get back lowercase 'hexits'
			$dbValidator = hash('sha256', $hexValidator);

			// Save / update the db record
			$authTokenMapper = new \Wyolution\Mapper($this->db, 'auth_tokens',
													 null,            // return all fields
													 0);            // don't use cache
			// Grab the existing record from the db, if there is one
			// We're filling the fields with all new data. But this way the
			// mapper knows to do a save or an update
			$authTokenMapper->load(array('userId = ?', $userId));
			$authTokenMapper->userId = $userId;
			$authTokenMapper->selector = $selector;
			$authTokenMapper->validator = $dbValidator;
			$authTokenMapper->expires = gmdate("Y-m-d H:i:s", $expiresAt); // store gm/utc time
			$authTokenMapper->tokenType = $tokenType;

			$authTokenMapper->save();
			// If we get an id back, the save succeeded: 
			if ($authTokenMapper->id > 0) {
				// Concatenate the selector and the validator to form the cookie/activation token value
				$rmInfo = $selector . $dbValidator;
				\Wyolution\Logger::debug('authtoken saved, about to return rminfo');
			}
		}
		return array($rmInfo, $expiresAt);
	}
}
