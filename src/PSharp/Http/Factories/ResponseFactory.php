<?php
namespace PSharp\Http\Factories;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;

use PSharp\Http\Response;
use PSharp\Http\Factories\CookieFactoryInterface;
use PSharp\Streams\StreamFactory;
use PSharp\Streams\StringStream;

/**
 *	Creates HTTP response instances.
 */
class ResponseFactory implements ResponseFactoryInterface
{
	/**
	 * Initializes factories and stuff
	 *
	 * @var \PSharp\Streams\StreamFactory
	 * @var \PSharp\Http\Factories\CookieFactoryInterface
	 */
	public function __construct(protected StreamFactory $streamFactory, protected CookieFactoryInterface $cookieFactory) {}

	/**
	 * Create a new response.
	 *
	 * @param int $code The HTTP status code. Defaults to 200.
	 * @param string $reasonPhrase The reason phrase to associate with the status code
	 *	 in the generated response. If none is provided, implementations MAY use
	 *	 the defaults as suggested in the HTTP specification.
	 */
	public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
	{
		return $this->create('', $code, []);
	}

	/**
	 * Creates an instance with the specified body, status code and, optionally, headers.
	 *
	 * @param string $body
	 * @param int $code
	 * @param array|null $headers
	 * @throws \InvalidArgumentException For invalid status code arguments
	 */
	public function create(string $body, int $statusCode = 200, array $headers = null)
	{
		return (new Response(
			new StringStream($body), $statusCode, $headers
		))->setCookieFactory($this->cookieFactory);
	}

	/**
	 * Creates an instance with the specified body, status code and, optionally, headers.
	 *
	 * @param mixed $body
	 * @param int $code
	 * @param array|null $headers
	 * @throws \InvalidArgumentException For invalid status code arguments
	 */
	public function json($body, int $statusCode = 200, array $headers = null)
	{
		return (new JsonResponse(
			$body, $statusCode, $headers
		))->setCookieFactory($this->cookieFactory);
	}

	/**
	 * Creates an instance with the specified body, status code and, optionally, headers.
	 *
	 * @param string $fileName
	 * @param int $code
	 * @param array|null $headers
	 * @throws \InvalidArgumentException For invalid status code arguments
	 */
	public function image(string $fileName, int $statusCode = 200, array $headers = null)
	{
		$headers = array_merge($headers ?? [], [
			'Content-Disposition' => 'inline',
			'Content-type' => 'image',
		]);
		//
		return (new Response(
			(new StreamFactory)->createStreamFromFile($fileName), 200, $headers
		))->setCookieFactory($this->cookieFactory);
	}

	/**
	 * Creates an instance with the specified body, status code and, optionally, headers.
	 *
	 * @param string $fileName
	 * @param int $code
	 * @param array|null $headers
	 * @throws \InvalidArgumentException For invalid status code arguments
	 */
	public function download(string $fileName, int $statusCode = 200, array $headers = null)
	{
		$headers = array_merge($headers ?? [], [
			'Content-Description' => 'File Transfer',
			'Content-Type' => 'application/octet-stream',
			'Content-Disposition' => 'attachment; filename="'.$file_name.'"',
			'Content-Transfer-Encoding' => 'binary',
			'Expires' => '0',
			'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
			'Pragma' => 'public',
			'Content-Length' => ('' . filesize($fileName)),
		]);
		//
		return (new Response(
			(new StreamFactory)->createStreamFromFile($fileName), 200, $headers
		))->setCookieFactory($this->cookieFactory);
	}
}