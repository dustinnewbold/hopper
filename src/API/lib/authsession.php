<?php

namespace API\lib;

class AuthSession {
	const expireTime = 2700;   // seconds: 45 min
	const hardExpire = 86400; // seconds : 1 day

	protected $userid = 0;
	protected $deviceid = 0;
	protected $authkey = null;
	protected $expires = 0;
	protected $orgid = 0;
	protected $roles = array();
	protected $roleid = 0;

	/**
	 * 
	 */
	public function validSession() {
		if ( Input::get('auth') ) {
			$sessionkey = Input::get('auth');
		} else {
			return false;
		}

		global $db;
		$auth = $db->single('SELECT ID, UserID, DeviceID, Expires FROM AuthSession WHERE AuthKey = :authkey AND Expires >= :time', array(':authkey' => $sessionkey, ':time' => time()));
		if ( !$auth ) {
			return false;
		}

		global $userid;
		$userid = $auth->userid;

		$this->userid = $auth->userid;
		$this->deviceid = $auth->deviceid;
		$this->authkey = $sessionkey;
		$this->expires = $auth->expires;

		$user = $db->single('SELECT OrganizationID FROM User WHERE ID = :userid', array(':userid' => $this->userid));
		$this->orgid = $user->organizationid;

		// $roles = $db->query('SELECT * FROM Permission WHERE UserID = :userid', array(':userid' => $this->userid));
		// $roles_obj = array();
		// foreach ( $roles as $role ) {
		// 	$roles_obj[] = (object)array(
		// 		'resourcetype' => $role->resourceid
		// 	);
		// }

		$db->update('AuthSession', $auth->id, 'Expires = ' . (time() + self::expireTime));
		return true;
	}

	public function sessionInformation() {
		if ( $this->userid !== 0 ) {
			return (object)array(
				'status'         => 'active',
				'userid'         => $this->userid,
				'organizationid' => $this->orgid,
				'deviceid'       => $this->deviceid,
				'authkey'        => $this->authkey,
				'expires'        => $this->expires
			);
		}
	}

	public function attemptDevice($client, $secret) {
		global $db;
		$device = $db->single('SELECT ID FROM Device WHERE Client = :client', array(':client' => $client));

		if ( !$device ) {
			return false;
		}

		return $device;
	}

	public function attemptUser($username, $password ) {
		global $db;
		$user = $db->single('SELECT ID, Password FROM User WHERE Email = :email', array(':email' => $username));
		if ( !$user ) {
			return false;
		}

		if ( !password_verify($password, $user->password) ) {
			return false;
		}

		return $user;
	}

	public function createSession($deviceid, $user_id) {
		global $db;
		$this->cleanSessions();

		$exists = $this->sessionExists($deviceid, $user_id);
		if ( !$exists ) {
			$sessionkey = md5(rand() * time());
			$query = $db->insert('AuthSession', array(
								 							'AuthKey'  => $sessionkey,
								 							'DeviceID' => $deviceid,
								 							'UserID'   => $user_id,
								 							'Created'  => time(),
								 							'Expires'  => (time() + self::expireTime)
														));
			if ( $query === false ) {
				return false;
			}
		} else {
			$sessionkey = md5(rand() * time());
			$id = $exists->id;
			$query = $db->update('AuthSession', $id, 'AuthKey = \'' . $sessionkey . '\', Expires = ' . (time() + self::expireTime) );
		}

		global $userid;
		$userid = $user_id;

		$db->update('User', $user_id, array('LastSignin' => time()));

		return $sessionkey;
	}

	public function cleanSessions() {
		global $db;
		$query = $db->delete('AuthSession', 'Expires < ' . time());
	}

	public function sessionExists($deviceid, $userid) {
		global $db;
		// dd('SESSION EXISTS!');
		$query = $db->single('SELECT ID,AuthKey FROM AuthSession WHERE DeviceID = :deviceid AND UserID = :userid AND Expires >= :time',
									  array(':deviceid' => $deviceid, ':userid' => $userid, ':time' => time()));
		return $query;
	}

	/**
	 * Permission checker
	 */
	public function require_permission($permission, $resourceid = null) {
		if ( $this->roleid == 4 ) {
			return true;
		}

		global $db;
		if ( $resourceid === null ) {
			$query = $db->single('SELECT PermissionDefault.* FROM `PermissionDefault` LEFT JOIN PermissionType ON PermissionType.ID = PermissionDefault.TypeID WHERE PermissionType.ShortCode = :type AND PermissionDefault.GroupID = :groupid', array(':type' => $permission, ':groupid' => $this->roleid));
			if ( empty($query) ) {
				return \API\lib\Response::fail('You do not have permission to use this resource.');
			}
		} else {
			$query = $db->single('SELECT * FROM `Permission` LEFT JOIN PermissionResource ON PermissionResource.ID = Permission.ResourceType WHERE PermissionResource.Resource = :type AND ResourceID = :resourceid AND (UserID = :userid OR GroupID = :groupid) LIMIT 0, 1',
										array(':type' => $permission, ':resourceid' => $resourceid, ':userid' => $this->userid, ':groupid' => $this->roleid));
			if ( empty($query) ) {
				return \API\lib\Response::fail('You do not have permission to use this resource.');
			}
		}

		return true;
	}
}
