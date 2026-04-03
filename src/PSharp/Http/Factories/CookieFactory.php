<?php
namespace PSharp\Http\Factories;

/**
 *	Encapsulates a HTTP cookie factory.
 *
 *	@author	alarido <alarido.su@gmail.com>
 *
 */
class CookieFactory implements CookieFactoryInterface
{
	/**
	 *	@var int
	 */
	public const FOREVER = 31536000;

	/**
	 *	@var int
	 */
	public const FORGET = -3600;

	/**
	 *	@var array
	 */
	protected $queued = [];

	/**
	 *	@var int $expires
	 */
	private $expires = 0;

	/**
	 *	@var string $path
	 */
	private $path = '/';

	/**
	 *	@var string $domain
	 */
	private $domain = '';

	/**
	 *	@var bool $secure
	 */
	private $secure = false;

	/**
	 *	@var bool $httpOnly
	 */
	private $httpOnly = false;

	/**
	 *	@var string $sameSite
	 */
	private $sameSite = 'Lax';

	/**
	 *	Define the default expiration term, in seconds.
	 *
	 *	@param	int	$expires
	 *	@return	$this
	 */
	public function setDefaultExpiration(int $expires)
	{
		$this->expires = $expires;
		//
		return $this;
	}

	/**
	 *	Define the default path field.
	 *
	 *	@param	string	$path
	 *	@return	$this
	 */
	public function setDefaultPath(string $path)
	{
		$this->path = $path;
		//
		return $this;
	}

	/**
	 *	Define the default cookie domain.
	 *
	 *	@param	string	$domain
	 *	@return	$this
	 */
	public function setDefaultDomain(string $domain)
	{
		$this->domain = $domain;
		//
		return $this;
	}

	/**
	 *	Define the default value of secure attribute.
	 *
	 *	@param	bool	$secure
	 *	@return	$this
	 */
	public function setDefaultSecure(bool $secure)
	{
		$this->secure = $secure;
		//
		return $this;
	}

	/**
	 *	Define the default value of httpOnly attribute.
	 *
	 *	@param	bool	$httpOnly
	 *	@return	$this
	 */
	public function setDefaultHttpMode(bool $httpOnly)
	{
		$this->httpOnly = $httpOnly;
		//
		return $this;
	}

	/**
	 *	Define the default value of sameSite attribute.
	 *
	 *	@param	string	$sameSite
	 *	@return	$this
	 */
	public function setDefaultSameSite(string $sameSite)
	{
		$this->sameSite = $sameSite;
		//
		return $this;
	}

	/**
	 *	Define the default cookie domain.
	 *
	 *	@param	string	$path
	 *	@param	string	$domain
	 *	@return	$this
	 */
	public function setDefaultPathAndDomain(string $path, string $domain)
	{
		[$this->path, $this->domain] = [$path, $domain];
		//
		return $this;
	}

	/**
	 *	Define the default value of cookie safety attributes.
	 *
	 *	@param	bool	$secure
	 *	@param	bool	$httpOnly
	 *	@param	string	$sameSite
	 *	@return	$this
	 */
	public function setDefaultSafetyValues(bool $secure, bool $httpOnly, string $sameSite)
	{
		[$this->secure, $this->httpOnly, $this->sameSite] = [$secure, $httpOnly, $sameSite];
		//
		return $this;
	}

	/**
	 *	Publishes the default values.
	 *
	 *	@param	string	$name
	 *	@return	mixed
	 */
	public function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}
		//
		return;
	}

	/**
	 *	Returns the list of all created cookies.
	 *
	 *	@return	array
	 */
	public function getCookies()
	{
		return $cookies = $this->queued ?? [];
	}

	/**
	 *	Creates a new Cookie instance
	 *
	 *	@param string $name
	 *	@param string $value
	 *	@param int $expires
	 *	@param string $path
	 *	@param string $domain
	 *	@param bool $secure
	 *	@param bool $httpOnly
	 *	@param string $sameSite
	 *	@return	\PSharp\Http\Cookie
	 */
	public function make(
		$name, string $value = null, int $expires = null,
		string $path = null, string $domain = null,
		bool $secure = false, bool $httpOnly = false, string $sameSite = null
	) {
		$this->queued[$name] = $cookie = new HttpCookie(
			$name,
			$value ?? '',
			$expires ?? $this->expires,
			$path ?? $this->path,
			$domain ?? $this->domain,
			$secure ?? $this->secure,
			$httpOnly ?? $this->httpOnly,
			$sameSite ?? $this->sameSite
		);
		//
		return $cookie;
	}

	/**
	 *	Creates a new Cookie instance
	 *
	 *	@param string $name
	 *	@param string $value
	 *	@param string $path
	 *	@param string $domain
	 *	@param bool $secure
	 *	@param bool $httpOnly
	 *	@param string $sameSite
	 *	@return	\PSharp\Http\Cookie
	 */
	public function forever(
		$name, string $value = null, string $path = null, string $domain = null,
		bool $secure = false, bool $httpOnly = false, string $sameSite = null
	) {
		return $this->make(
			$name, $value, self::FOREVER, $path, $domain, $secure, $httpOnly, $sameSite
		);
	}

	/**
	 *	Creates a new Cookie instance
	 *
	 *	@param string $name
	 *	@param string $path
	 *	@param string $domain
	 */
	public function forget($name, string $path = null, string $domain = null)
	{
		return $this->make($name, null, self::FORGET, $path, $domain);
	}
}