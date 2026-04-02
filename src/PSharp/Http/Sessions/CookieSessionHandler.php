<?php
namespace PSharp\Http\Sessions;

use PSharp\Support\Traits\InteractsWithTime;
use PSharp\Http\Factories\CookieFactoryInterface;
use PSharp\Http\Request;

/**
 * Performs session handling through session cookies.
 */
class CookieSessionHandler implements SessionHandlerInterface
{
	use InteractsWithTime;

	/**
	 * @var \PSharp\Http\Factories\CookieFactoryInterface
	 */
	private $cookieFactory;

	/**
	 * @var \PSharp\Http\Request
	 */
	private $request;

	/**
	 * @var int
	 */
	private $minutes;

	/**
	 * Create a new cookie-based session handler instance.
	 *
	 * @param	\PSharp\Http\Factories\CookieFactoryInterface	$factory
	 * @param	int	$minutes
	 */
	public function __construct(CookieFactoryInterface $factory, $minutes)
	{
		$this->cookieFactory = $factory;
		$this->minutes = $minutes;
	}

	/**
	 * @inheritdoc
	 */
	public function open($path, $sessionName): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function close(): bool
	{
		return true;
	}

	/**
	 * @inheritdoc
	 */
	#[\ReturnTypeWillChange]
	public function read($id)
	{
		$cookie = $this->request->cookie($id);
		//
		return $cookie;
	}

	/**
	 * @inheritdoc
	 */
	public function write($id, $data): bool
	{
		$jsonData = json_encode([
			'data' => $data,
			'expires' => $seconds = $this->minutes * 60
		]);
		//
		$this->cookieFactory->make($id, $jsonData, $this->availableAt($seconds));
		//
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function destroy($id): bool
	{
		$this->cookieFactory->forget($id);
		//
		return true;
	}

	/**
	 * @inheritdoc
	 */
	#[\ReturnTypeWillChange]
	public function gc($maxlifetime)
	{
		return true;
	}

	/**
	 * Define the Request being worked.
	 *
	 * @param	\PSharp\Http\Request	$request
	 * @return	void
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}
}