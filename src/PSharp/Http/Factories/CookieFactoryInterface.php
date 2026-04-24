<?php
namespace PSharp\Http\Factories;

/**
 * Encapsulates a HTTP cookie factory.
 */
interface CookieFactoryInterface
{
	/**
	 * Define the default expiration term, in seconds.
	 *
	 * @param int $expires
	 * @return $this
	 */
	public function setDefaultExpiration(int $expires);

	/**
	 * Define the default path field.
	 *
	 * @param string $path
	 * @return $this
	 */
	public function setDefaultPath(string $path);

	/**
	 * Define the default cookie domain.
	 *
	 * @param string $domain
	 * @return $this
	 */
	public function setDefaultDomain(string $domain);

	/**
	 * Define the default value of secure attribute.
	 *
	 * @param bool $secure
	 * @return $this
	 */
	public function setDefaultSecure(bool $secure);

	/**
	 * Define the default value of httpOnly attribute.
	 *
	 * @param bool $httpOnly
	 * @return $this
	 */
	public function setDefaultHttpMode(bool $httpOnly);

	/**
	 * Define the default value of sameSite attribute.
	 *
	 * @param string $sameSite
	 * @return $this
	 */
	public function setDefaultSameSite(string $sameSite);

	/**
	 * Define the default cookie domain.
	 *
	 * @param string $path
	 * @param string $domain
	 * @return $this
	 */
	public function setDefaultPathAndDomain(string $path, string $domain);

	/**
	 * Define the default value of cookie safety attributes.
	 *
	 * @param bool $secure
	 * @param bool $httpOnly
	 * @param string $sameSite
	 * @return $this
	 */
	public function setDefaultSafetyValues(bool $secure, bool $httpOnly, string $sameSite);

	/**
	 * Publishes the default values.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name);

	/**
	 * Returns the list of all created cookies.
	 *
	 * @return array
	 */
	public function getCookies();

	/**
	 * Creates a new Cookie instance
	 *
	 * @param string $name
	 * @param string $value
	 * @param int $expires
	 * @param string $path
	 * @param string $domain
	 * @param bool $secure
	 * @param bool $httpOnly
	 * @param string $sameSite
	 * @return \PSharp\Http\Cookie
	 */
	public function make(
		$name, string $value = null, int $expires = null,
		string $path = null, string $domain = null,
		bool $secure = false, bool $httpOnly = false, string $sameSite = null
	);

	/**
	 * Creates a new Cookie instance
	 *
	 * @param string $name
	 * @param string $value
	 * @param string $path
	 * @param string $domain
	 * @param bool $secure
	 * @param bool $httpOnly
	 * @param string $sameSite
	 * @return \PSharp\Http\Cookie
	 */
	public function forever(
		$name, string $value = null, string $path = null, string $domain = null,
		bool $secure = false, bool $httpOnly = false, string $sameSite = null
	);

	/**
	 * Creates a new Cookie instance
	 *
	 * @param string $name
	 * @param string $path
	 * @param string $domain
	 */
	public function forget($name, string $path = null, string $domain = null);
}