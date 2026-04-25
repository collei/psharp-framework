<?php
namespace PSharp\Http\Sessions;

use PSharp\Support\Str;
use PSharp\Support\Arr;

/**
 * Manages HTTP user sessions.
 *
 * @author linblow AT hotmail DOT fr
 * @see https://www.php.net/manual/pt_BR/function.session-start.php#102460 <accessed 2021-10-31 GMT-3>
 */
class Session
{
	/**
	 * @static @var bool
	 */
	public const SESSION_STARTED = TRUE;
	public const SESSION_NOT_STARTED = FALSE;

	/**
	 * @static @var array
	 */
	protected const SPECIAL_KEYS = ['_token','_flash','_published'];

	/**
	 * @static @var array - session options
	 */
	private static $options = [
		'cookie_lifetime' => 86400
	];

	/**
	 * @var array - session state
	 */
	private $sessionState = self::SESSION_NOT_STARTED;

	/**
	 * @var PSharp\Http\Sessions\Session Session singleton
	 */
	private static $instance = null;

	/**
	 * @var string  Session name
	 */
	private static $name = null;

	/**
	 * Private constructor.
	 *
	 * @return void
	 */
	private function __construct() {}

	/**
	 * Returns THE instance of 'Session'.
	 *
	 * @return static
	 */
	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self;
		}
		//
		return self::$instance;
	}

	/**
	 *	Define the session name.
	 *
	 * @param string $name = null
	 * @return void
	 */
	public static function setName(string $name = null)
	{
		if ($name) {
			self::$name = $name;
		}
	}

	/**
	 *	(Re)starts the session.
	 *
	 * @return bool TRUE if the session has been initialized,
	 * FALSE otherwise.
	 */
	public static function start()
	{
		return Session::getInstance()->startSession();
	}

	/**
	 * Set session lifetime.
	 *
	 * @param int $lifetime
	 * @return void
	 */
	public function setLifetime(int $lifetime)
	{
		$this->options['cookie_lifetime'] = $lifetime;
	}

	/**
	 *	(Re)starts the session.
	 *
	 * @param string $name = null
	 * @return bool TRUE if the session has been initialized,
	 * FALSE otherwise.
	 */
	public function startSession()
	{
		if ($this->sessionState == self::SESSION_NOT_STARTED) {
			if (self::$name) {
				session_name(self::$name);
			}
			//
			if ($this->sessionState = session_start(self::$options)) {
				$this->regenerateToken();
			}
			//
			foreach (self::SPECIAL_KEYS as $special) {
				if (! array_key_exists($special, $_SESSION)) {
					$_SESSION[$special] = ['void' => 1]; 
				}
			}
		}
		//
		return $this->sessionState;
	}

	/**
	 * Regenerates token
	 *
	 * @return string
	 */
	public function regenerateToken()
	{
		if (!isset($_SESSION['_token'])) {
			$_SESSION['_token'] = Str::random();
		}

		return $_SESSION['_token'];
	}

	/**
	 * Erases discardables
	 *
	 * @return void
	 */
	public function eraseDiscardables()
	{
		//
	}

	/**
	 * Stores datum in the session.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set(string $name, $value)
	{
		if (! in_array($name, self::SPECIAL_KEYS)) {
			$_SESSION[$name] = $value;
		}
	}

	/**
	 * Gets datum from the session.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if ($name === 'token' || $name === 'csrf') {
			return $_SESSION['_token'] ?? '';
		}
		//
		if (array_key_exists($name, $sess = ($_SESSION ?? []))) {
			return $sess[$name] ?? '';
		}
	}

	/**
	 * Checks if $name is defined in the session.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __isset($name)
	{
		return array_key_exists($name, $_SESSION);
	}

	/**
	 * Removes $name from the session.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __unset($name)
	{
		Arr::forget($_SESSION, $name);
		//
		unset($_SESSION[$name]);
	}

	/**
	 * Prints debug info on session data.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __debugInfo()
	{
		if (! isset($_SESSION)) {
			return [
				'message' => 'No $_SESSION variable found in console mode.'
			];
		}
		//
		$internals = [ 'sessionState' => $this->sessionState ];
		$values = $_SESSION;
		//
		return array_merge($internals, $values);
	}

	/**
	 * Checks if a given $name exists..
	 *
	 * @param $name string
	 * @return bool	
	 */
	public function has(string $name)
	{
		return array_key_exists($name, $_SESSION);
	}

	/**
	 * Returns a value.
	 *
	 * @param string $name
	 * @param mixed $default = null
	 * @return mixed
	 */
	public function get(string $name, $default = null)
	{
		return $_SESSION[$name] ?? $default;
	}

	/**
	 * Sets a value to the session. Returns the old value
	 *
	 * @param $name string
	 * @param $value mixed
	 * @return mixed
	 */
	public function set(string $name, $value)
	{
		list($old, $_SESSION[$name]) = array($_SESSION[$name] ?? null, $value);
		//
		return $old;
	}

	/**
	 * Returns a value and then removes it.
	 *
	 * @param $name string the name of session variable
	 * @param $subName string a specific index in such session variable (if array)
	 * @return string|array if $name holds an array value
	 */
	public function pull(string $name, $default = null)
	{
		$value = $this->get($name, $default);
		//
		$this->remove($name);
		//
		return $value;
	}

	/**
	 * Sets a value to the session.
	 *
	 * @param $name string
	 * @param $value mixed
	 * @return void
	 */
	public function put(string $name, $value)
	{
		$this->set($name, $value);
	}

	/**
	 * Removes the $name attribute from the session.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function remove(string $name)
	{
		if (array_key_exists($name, $_SESSION)) {
			unset($_SESSION[$name]);
			//
			return true;
		}
		//
		return false;
	}

	/**
	 * Set a message to last just for the next session
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function flash(string $name, $value)
	{
		$this->set($name, $value);
		//
		$_SESSION['_flash'][$name] = $value;
	}

	/**
	 * Set a variable to be published into views
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function publish(string $name, $value)
	{
		$this->set($name, $value);
		//
		$_SESSION['_published'][$name] = $value;
	}

	/**
	 * If $name is specified, behaves like pull() among the flashed values.
	 * If not, returns an array of flash messages and erases them from the session.
	 *
	 * @param string|null $name = null
	 * @param mixed $default = null
	 * @return string|array
	 */
	public function flashed(string $name = null, $default = null)
	{
		if ($name) {
			$flashed = $_SESSION['_flash'][$name] ?? $default;
			//
			unset($_SESSION['_flash'][$name]);
			//
			return $flashed;
		}
		//
		if (isset($_SESSION['_flash'])) {
			$flashed = $_SESSION['_flash'];
			//
			unset($_SESSION['_flash']);
		}
		//
		return $flashed ?? [];
	}

	/**
	 * Return an array of the published variables
	 *
	 * @return array
	 */
	public function published()
	{
		if (isset($_SESSION['_published'])) {
			return $_SESSION['_published'];
		}
		//
		return [];
	}

	/**
	 * Retrieves the session token.
	 *
	 * @return string|null
	 */
	public function token()
	{
		return $_SESSION['_token'] ?? null;
	}

	/**
	 * Destroys the current session.
	 *
	 * @param bool $removeCookies True to remove the session cookies
	 * @return bool TRUE is session has been deleted, else FALSE.
	 */
	public function destroy(bool $removeCookies = false)
	{
		if ($this->sessionState == self::SESSION_STARTED) {
			if (self::$name) {
				@session_name(self::$name);
			}
			//
			$this->sessionState = !@session_destroy();
			//
			unset($_SESSION);
			//
			if ($removeCookies) foreach ($_COOKIE as $n => $v) {
				setcookie($n, '', time() - 43200);
			}
			//		
			return !$this->sessionState;
		}
		//
		return FALSE;
	}

	/**
	 * Destroys the current session AND starts a new fresh one.
	 *
	 * @return void
	 */
	public function closeCurrent()
	{
		$this->destroy(true);
		//
		$this->startSession();
	}
}