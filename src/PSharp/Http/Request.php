<?php
namespace PSharp\Http;

use Closure;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

use PSharp\Http\Session;
use PSharp\Http\Factories\UriFactory;
use PSharp\Support\Arr;
use PSharp\Support\Str;

/**
 * Represents a HTTP request.
 *
 */
class Request implements RequestInterface, ServerRequestInterface
{
	/**
	 * @var array METHODS
	 */
	public const METHODS = ['GET','HEAD','POST','PUT','DELETE','CONNECT','OPTIONS','TRACE','PATCH'];

	/**
	 * @var UriInterface
	 */
	protected $uri;

	/**
	 * @var string
	 */
	protected $requestTarget = '/';

	/**
	 * @var string
	 */
	protected $format = 'html';

	/**
	 * @var string
	 */
	protected $mimeType = 'text/html';

	/**
	 * @var string
	 */
	protected $method = '';

	/**
	 * @var string
	 */
	protected $overridenMethod = '';

	/**
	 * @var string
	 */
	protected $httpVersion = '';

	/**
	 * @var string[]
	 */
	protected $headers = [];

	/**
	 * @var string
	 */
	protected $body = null;

	/**
	 * @var array
	 */
	protected $serverParams;

	/**
	 * @var array
	 */
	protected $cookieParams;

	/**
	 * @var array
	 */
	protected $queryStringParams;

	/**
	 * @var array
	 */
	protected $uploadedFiles;

	/**
	 * @var mixed
	 */
	protected $parsedBodyContent;

	/**
	 * @var mixed
	 */
	protected $parsedAcceptableContentTypes;

	/**
	 * @var array
	 */
	protected $attributes;

	/**
	 * @var \Zelatus\Routing\Route
	 */
	protected $endpoint;

	/**
	 * @var \PSharp\Http\Session
	 */
	protected $session;

	/**
	 * Instantiates a HttpRequest object
	 *
	 */
	public function __construct()
	{
		$this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
		//
		$this->uri = (new UriFactory)->createUri(
			$_SERVER['REQUEST_URI'] ?? 'http://localhost'
		);
		//
		$this->session = Session::getInstance();
	}

	/**
	 * Retrieves the message's request target.
	 *
	 * @return string
	 */
	public function getRequestTarget(): string
	{
		return empty($this->requestTarget) ? '/' : $this->requestTarget;
	}

	/**
	 * Retrieves the request format.
	 *
	 * @param	string	$default = 'html'
	 * @return	string
	 */
	public function getRequestFormat($default = 'html'): string
	{
		if (null === $this->format) {
			$this->format = $this->attributes->get('_format');
		}

		return null === $this->format ? $default : $this->format;
	}

	/**
	 * Sets the request format.
	 *
	 * @param	string	$format
	 */
	public function setRequestFormat(string $format)
	{
		$this->format = $format;
	}

	/**
	 * Sets the request format.
	 *
	 * @return	string
	 */
	public function getMimeType()
	{
		return $this->mimeType;
	}

	/**
	 * Allows to override method on HTML forms by setting a special field.
	 *
	 * @return	void
	 */
	public function enableHttpMethodParameterOverride()
	{
		$another = $_POST['_method'] ?? null;
		//
		if (!empty($another) && empty($this->overridenMethod)) {
			$this->overridenMethod = $this->method;
			//
			$this->method = $another;
		}
	}

	/**
	 * Return an instance with the specific request-target.
	 *
	 * @see http://tools.ietf.org/html/rfc7230#section-5.3 (for the various
	 *	 request-target forms allowed in request messages)
	 * @param mixed $requestTarget
	 * @return static
	 */
	public function withRequestTarget($requestTarget) : RequestInterface
	{
		$cloned = clone $this;
		$cloned->requestTarget = $requestTarget;
		return $cloned;
	}

	/**
	 * Retrieves the HTTP method of the request.
	 *
	 * @return string Returns the request method.
	 */
	public function method() : string
	{
		return $this->getMethod();
	}

	/**
	 * Retrieves the HTTP method of the request.
	 *
	 * @return string Returns the request method.
	 */
	public function getMethod() : string
	{
		return $this->method;
	}

	/**
	 * Tells if $method is the HTTP method of the request.
	 *
	 * @return bool
	 */
	public function isMethod(string $method) : bool
	{
		return strcasecmp($method, $this->method) === 0;
	}

	/**
	 * Return an instance with the provided HTTP method.
	 *
	 * While HTTP method names are typically all uppercase characters,
	 * HTTP method names are case-sensitive and thus implementations
	 * SHOULD NOT modify the given string.
	 *
	 * This method MUST be implemented in such a way as to retain the
	 * immutability of the message, and MUST return an instance that has the
	 * changed request method.
	 *
	 * @param string $method Case-sensitive method.
	 * @return static
	 * @throws \InvalidArgumentException for invalid HTTP methods.
	 */
	public function withMethod($method) : RequestInterface
	{
		if (!in_array(strtoupper($method), self::METHODS)) {
			throw new InvalidArgumentException(
				'Method must be one of these: ' . implode(', ', self::METHODS)
			);
		}
		//
		$cloned = clone $this;
		$cloned->method = $method;
		return $cloned;
	}

	/**
	 * Retrieves the URI instance.
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-4.3
	 *
	 * @return \Psr\Http\Message\UriInterface
	 */
	public function getUri() : UriInterface
	{
		return $this->uri;
	}

	/**
	 * Retrieves the unique Session instance.
	 *
	 * @return	\PSharp\Http\Session
	 */
	public function getSession() : Session
	{
		return $this->session;
	}

	/**
	 * Checks if the request URI matches a pattern.
	 *
	 * @param	string	...$patterns
	 * @return	bool
	 */
	public function is(string ...$patterns) : bool
	{
		$path = $this->getUri()->getPath();
		//
		foreach ($patterns as $pattern) {
			if (Str::is($pattern, $path, '#')) {
				return true;
			}
		}
		//
		return false;
	}

	/**
	 * Checks if the request URI and query string matches a pattern.
	 *
	 * @param	string	...$patterns
	 * @return	bool
	 */
	public function fullUrlIs(string ...$patterns) : bool
	{
		$url = $this->getUri()->toString();
		//
		foreach ($patterns as $pattern) {
			if (Str::is($pattern, $url, '#')) {
				return true;
			}
		}
		//
		return false;
	}

	/**
	 * Checks whether the request is secure or not.
	 *
	 * @return bool
	 */
	public function isSecure() : bool
	{
		$https = $this->getServerParam('HTTPS');
		//
		return !empty($https) && 'off' !== strtolower($https);
	}

	/**
	 * Returns an instance with the provided URI.
	 *
	 * @see http://tools.ietf.org/html/rfc3986#section-4.3
	 * @param UriInterface $uri New request URI to use.
	 * @param bool $preserveHost Preserve the original state of the Host header.
	 * @return static
	 */
	public function withUri(UriInterface $uri, bool $preserveHost = false) : RequestInterface
	{
		$cloned = clone $this;
		$host = $uri->getHost();
		$cloned->uri = $uri;
		//
		// If the new URI contains a host component...
		if (!empty($host)) {
			// If original host header must be preserved...
			if ($preserveHost) {
				$header = $this->getHeader('Host');
				// If the Host header is missing or empty...
				if (empty($header)) {
					// then adds the Host header in the returned request
					$cloned = $cloned->withHeader('Host', $host);
				}
			} else {
				// updates the Host header in the returned request
				$cloned = $cloned->withHeader('Host', $host);
			}
		}
		//
		return $cloned;
	}

	/**
	 * Retrieves the HTTP protocol version as a string.
	 *
	 * @return string HTTP protocol version.
	 */
	public function getProtocolVersion() : string
	{
		return $this->httpVersion;
	}

	/**
	 * Return an instance with the specified HTTP protocol version.
	 *
	 * @param string $version HTTP protocol version
	 * @return static
	 */
	public function withProtocolVersion($version) : RequestInterface
	{
		$cloned = clone $this;
		$cloned->httpVersion = $version;
		return $cloned;
	}

	/**
	 * Retrieves all message header values.
	 *
	 * @return string[][] Returns an associative array of the message's headers.
	 *	 Each key MUST be a header name, and each value MUST be an array of
	 *	 strings for that header.
	 */
	public function getHeaders() : array
	{
		return $this->headers;
	}

	/**
	 * Checks if a header exists by the given case-insensitive name.
	 *
	 * @param string $name Case-insensitive header field name.
	 * @return bool Returns true if any header names match the given header
	 *	 name using a case-insensitive string comparison. Returns false if
	 *	 no matching header name is found in the message.
	 */
	public function hasHeader($name) : bool
	{
		if (empty($this->headers)) {
			return false;
		}
		//
		foreach ($this->headers as $n => $v) {
			if (0 == strcasecmp($n, $name)) {
				return true;
			}
		}
		//
		return false;
	}

	/**
	 * Retrieves a message header value by the given case-insensitive name.
	 *
	 * @param string $name Case-insensitive header field name.
	 * @return string[] An array of string values as provided for the given
	 *	header. If the header does not appear in the message, this method MUST
	 *	return an empty array.
	 */
	public function getHeader(string $name) : array
	{
		if (empty($this->headers)) {
			return [];
		}
		//
		foreach ($this->headers as $n => $v) {
			if (0 == strcasecmp($n, $name)) {
				return is_array($v) ? $v : [$v];
			}
		}
		//
		return [];
	}	

	/**
	 * Retrieves a comma-separated string of the values for a single header.
	 *
	 * @param string $name Case-insensitive header field name.
	 * @return string A string of values as provided for the given header
	 *	concatenated together using a comma. If the header does not appear in
	 *	the message, this method MUST return an empty string.
	 */
	public function getHeaderLine(string $name) : string
	{
		if (empty($this->headers)) {
			return '';
		}
		//
		foreach ($this->headers as $n => $v) {
			if (0 == strcasecmp($n, $name)) {
				return is_array($v) ? implode(',', $v) : $v;
			}
		}
		//
		return '';
	}

	/**
	 * Return an instance with the provided value replacing the specified header.
	 *
	 * @param string $name Case-insensitive header field name.
	 * @param string|string[] $value Header value(s).
	 * @return static
	 * @throws \InvalidArgumentException for invalid header names or values.
	 */
	public function withHeader(string $name, $value) : RequestInterface
	{
		if (!is_string($name)) {
			throw new InvalidArgumentException('Name must be a string');
		}
		//
		if (!is_string($value) && !is_array($value)) {
			throw new InvalidArgumentException('Value must be a string or array');
		}
		//
		$cloned = clone $this;
		//
		if (empty($cloned->headers)) {
			$cloned->headers = [
				$name => is_array($value) ? $value : [$value]
			];
		} else {
			$found = false;
			foreach ($cloned->headers as $n => $v) {
				if (0 == strcasecmp($n, $name)) {
					$cloned->headers[$n] = is_array($value) ? $value : [$value];
					$found = true;
					break;
				}
			}
			//
			if (!$found) {
				$cloned->headers[$name] = is_array($value) ? $value : [$value];
			}
		}
		//
		return $cloned;
	}

	/**
	 * Return an instance with the specified header appended with the given value.
	 *
	 * @param string $name Case-insensitive header field name to add.
	 * @param string|string[] $value Header value(s).
	 * @return static
	 * @throws \InvalidArgumentException for invalid header names.
	 * @throws \InvalidArgumentException for invalid header values.
	 */
	public function withAddedHeader(string $name, $value) : RequestInterface
	{
		if (!is_string($name)) {
			throw new InvalidArgumentException('Name must be a string');
		}
		//
		if (!is_string($value) && !is_array($value)) {
			throw new InvalidArgumentException('Value must be a string or array');
		}
		//
		$cloned = clone $this;
		//
		if (empty($cloned->headers)) {
			$cloned->headers = [
				$name => is_array($value) ? $value : [$value]
			];
		} else {
			$found = false;
			foreach ($cloned->headers as $n => $v) {
				if (0 == strcasecmp($n, $name)) {
					if (!is_array($v)) {
						$cloned->headers[$n] = [$v];
					}
					//
					if (is_array($value)) {
						foreach ($value as $valueItem) {
							$cloned->headers[$n][] = $valueItem;
						}
					} else {
						$cloned->headers[$n][] = $value;
					}
					//
					$found = true;
					break;
				}
			}
			//
			if (!$found) {
				$cloned->headers[$name] = is_array($value) ? $value : [$value];
			}
		}
		//
		return $cloned;
	}

	/**
	 * Return an instance without the specified header.
	 *
	 * @param string $name Case-insensitive header field name to remove.
	 * @return static
	 */
	public function withoutHeader(string $name) : RequestInterface
	{
		if (!is_string($name)) {
			throw new InvalidArgumentException('Name must be a string');
		}
		//
		$cloned = clone $this;
		//
		if (!empty($cloned->headers)) {
			foreach ($cloned->headers as $n => $v) {
				if (0 == strcasecmp($n, $name)) {
					unset($cloned->headers[$n]);
					break;
				}
			}
		}
		//
		return $cloned;
	}

	/**
	 * Gets the body of the message.
	 *
	 * @return StreamInterface Returns the body as a stream.
	 */
	public function getBody() : StreamInterface
	{
		return $this->body;
	}

	/**
	 * Return an instance with the specified message body.
	 *
	 * @param StreamInterface $body Body.
	 * @return static
	 * @throws \InvalidArgumentException When the body is not valid.
	*/
	public function withBody(StreamInterface $body) : RequestInterface
	{
		$cloned = clone $this;
		$cloned->body = $body;
		return $cloned;
	}

	/**
	 * Retrieve the given server parameter.
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return string
	 */
	public function getServerParam(string $name, $default = null) : string
	{
		return $this->serverParams[$name] ?? $default ?? '';
	}

	/**
	 * Retrieve server parameters.
	 *
	 * @return array
	 */
	public function getServerParams() : array
	{
		return $this->serverParams;
	}

	/**
	 * Return an instance with the specified server parameters.
	 *
	 * @param array $server Array of server parameters, typically from $_SERVER.
	 * @return static
	 */
	public function withServerParams(array $server) : RequestInterface
	{
		$cloned = clone $this;
		$cloned->serverParams = $server;
		return $cloned;
	}

	/**
	 * Retrieves cookies sent by the client to the server.
	 *
	 * @return array
	 */
	public function getCookieParams() : array
	{
		return $this->cookieParams;
	}

	/**
	 * Return an instance with the specified cookies.
	 *
	 * @param array $cookies Array of key/value pairs representing cookies.
	 * @return static
	 */
	public function withCookieParams(array $cookies) : ServerRequestInterface
	{
		$cloned = clone $this;
		$cloned->cookieParams = $cookies;
		return $cloned;
	}

	/**
	 * Retrieve query string arguments.
	 *
	 * @return array
	 */
	public function getQueryParams() : array
	{
		return $this->queryStringParams;
	}

	/**
	 * Return an instance with the specified query string arguments.
	 *
	 * @param array $query Array of query string arguments, typically from $_GET.
	 * @return static
	 */
	public function withQueryParams(array $query) : ServerRequestInterface
	{
		$cloned = clone $this;
		$cloned->queryStringParams = $query;
		return $cloned;
	}

	/**
	 * Retrieve normalized file upload data.
	 *
	 * @return array An array tree of UploadedFileInterface instances; an empty
	 *	 array MUST be returned if no data is present.
	 */
	public function getUploadedFiles() : array
	{
		return $this->uploadedFiles;
	}

	/**
	 * Create a new instance with the specified uploaded files.
	 *
	 * @param array $uploadedFiles An array tree of UploadedFileInterface instances.
	 * @return static
	 * @throws \InvalidArgumentException if an invalid structure is provided.
	 */
	public function withUploadedFiles(array $uploadedFiles) : ServerRequestInterface
	{
		$cloned = clone $this;
		$cloned->uploadedFiles = $uploadedFiles;
		return $cloned;
	}

	/**
	 * Retrieve any parameters provided in the request body.
	 *
	 * @return null|array|object The deserialized body parameters, if any.
	 *	 These will typically be an array or object.
	 */
	public function getParsedBody()
	{
		return $this->parsedBodyContent;
	}

	/**
	 * Return an instance with the specified body parameters.
	 *
	 * @param null|array|object $data The deserialized body data. This will
	 *	 typically be in an array or object.
	 * @return static
	 * @throws \InvalidArgumentException if an unsupported argument type is
	 *	 provided.
	 */
	public function withParsedBody($data) : ServerRequestInterface
	{
		$cloned = clone $this;
		$cloned->parsedBodyContent = $data;
		return $cloned;
	}

	/**
	 * Retrieve attributes derived from the request.
	 *
	 * The request "attributes" may be used to allow injection of any
	 * parameters derived from the request: e.g., the results of path
	 * match operations; the results of decrypting cookies; the results of
	 * deserializing non-form-encoded message bodies; etc. Attributes
	 * will be application and request specific, and CAN be mutable.
	 *
	 * @return mixed[] Attributes derived from the request.
	 */
	public function getAttributes() : array
	{
		return $this->attributes;
	}

	/**
	 * Returns if the specified request attribute exists.
	 *
	 * @see getAttributes()
	 * @param string $name The attribute name.
	 * @return bool
	 */
	public function hasAttribute(string $name) : bool
	{
		return array_key_exists($name, $this->attributes);
	}

	/**
	 * Retrieve a single derived request attribute.
	 *
	 * @see getAttributes()
	 * @param string $name The attribute name.
	 * @param mixed $default Default value to return if the attribute does not exist.
	 * @return mixed
	 */
	public function getAttribute(string $name, $default = null)
	{
		return $this->attributes[$name] ?? $default;
	}

	/**
	 * Return an instance with the specified derived request attribute.
	 *
	 * @see getAttributes()
	 * @param string $name The attribute name.
	 * @param mixed $value The value of the attribute.
	 * @return static
	 */
	public function withAttribute(string $name, $value) : ServerRequestInterface
	{
		$cloned = clone $this;
		//
		$cloned->attributes[$name] = $value;
		//
		return $cloned;
	}

	/**
	 * Return an instance that removes the specified derived request attribute.
	 *
	 * @see getAttributes()
	 * @param string $name The attribute name.
	 * @return static
	 */
	public function withoutAttribute(string $name) : ServerRequestInterface
	{
		$cloned = clone $this;
		//
		unset($cloned->attributes[$name]);
		//
		return $cloned;
	}

	/**
	 * Returns the endpoint bound with the request.
	 *
	 * @return \Zelatus\Routing\Route
	 */
	public function endpoint()
	{
		if ($this->endpoint) {
			return $this->endpoint;
		}

		return null;
	}

	/**
	 * Captures a request with a help of its factory
	 *
	 * @return \PSharp\Http\Request
	 */
	public static function capture()
	{
		return RequestFactory::captureRequest();
	}

	/**
	 * Creates a Request based on a given URI and configuration.
	 *
	 * The information contained in the URI always take precedence
	 * over the other information (server and parameters).
	 *
	 * @param string	$uri		The URI
	 * @param string	$method		The HTTP method
	 * @param array		$parameters	The query (GET) or request (POST) parameters
	 * @param array		$cookies	The request cookies ($_COOKIE)
	 * @param array		$files		The request files ($_FILES)
	 * @param array		$server		The server parameters ($_SERVER)
	 * @param string|resource|null $content	The raw body data
	 * @return PSharp\Http\Request
	 */
	public static function create(
		string $uri, string $method = 'GET',
		array $parameters = [],
		array $cookies = [],
		array $files = [],
		array $server = [],
		$content = null
	) {
		return (new RequestFactory)->createFromParts(
			$uri, $method, $parameters, $cookies, $files, $server, $content
		);
	}

	/**
	 * Returns the given $name field value from the POST and GET fields.
	 * POST fields are priorized.
	 *
	 * @param string $name = null
	 * @param mixed $default
	 * @return mixed 
	 */
	public function input(string $name = null, $default = null)
	{
		if (is_null($name)) {
			if (is_array($this->parsedBodyContent)) {
				return $this->parsedBodyContent + $this->queryStringParams;
			}
			//
			return $this->queryStringParams;
		}
		//
		if (is_array($this->parsedBodyContent)) {
			return $this->parsedBodyContent[$name]
				?? $this->queryStringParams[$name]
				?? $default;
		}
		//
		if (is_object($this->parsedBodyContent)) {
			return Arr::get(
				$this->parsedBodyContent,
				$name,
				$this->queryStringParams[$name] ?? $default
			);
		}
		//
		return $default;
	}

	/**
	 * Returns the server parameter $name, or null if not found.
	 * If parameter is ommited, returns all server parameters at once.
	 *
	 * @param string $name
	 * @return string|array|null
	 */
	public function server(string $name = null)
	{
		if ($name) {
			return $this->serverParams[$name]
				?? $this->serverParams[strtoupper($name)]
				?? null;
		}
		//
		return $this->serverParams;
	}

	/**
	 * Returns the query parameter $name, or null if not found.
	 * If parameter is ommited, returns all query parameters at once.
	 *
	 * @param string $name
	 * @return string|array|null
	 */
	public function query(string $name = null)
	{
		if ($name) {
			return $this->queryStringParams[$name] ?? null;
		}
		//
		return $this->queryStringParams;
	}

	/**
	 * Alias of input()
	 *
	 * @param string $name = null
	 * @param mixed $default
	 * @return mixed 
	 */
	public function request(string $name = null, $default = null)
	{
		return $this->input($name, $default);
	}

	/**
	 * Returns the cookie of $name, or null if not found.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function cookie(string $name)
	{
		return $this->cookieParams[$name] ?? null;
	}

	/**
	 * Returns all cookies at once, if any.
	 *
	 * @return array|null
	 */
	public function cookies()
	{
		return $this->cookieParams;
	}

	/**
	 * Returns the file of $name, or null if not found.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function file(string $name)
	{
		return Arr::get($this->uploadedFiles, $name, null);
	}

	/**
	 * Returns all files at once, if any.
	 *
	 * @return array|null
	 */
	public function files()
	{
		return $this->uploadedFiles;
	}

	/**
	 * Returns the attribute of $name, or null if not found.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function attribute(string $name)
	{
		return $this->attributes[$name] ?? null;
	}

	/**
	 * Returns all attributes at once, if any.
	 *
	 * @return array|null
	 */
	public function attributes()
	{
		return $this->attributes;
	}

	/**
	 * Determine if the request is sending JSON.
	 *
	 * @return bool
	 */
	public function isJson()
	{
		return Str::contains($this->header('CONTENT_TYPE') ?? '', ['/json', '+json']);
	}

	/**
	 * Determine if the current request probably expects a JSON response.
	 *
	 * @return bool
	 */
	public function expectsJson()
	{
		return ($this->ajax() && ! $this->pjax() && $this->acceptsAnyContentType()) || $this->wantsJson();
	}

	/**
	 * Determine if the request is the result of an AJAX call.
	 *
	 * @return bool
	 */
	public function ajax()
	{
		return $this->isXmlHttpRequest();
	}

	/**
	 * Determine if the request is the result of a PJAX call.
	 *
	 * @return bool
	 */
	public function pjax()
	{
		return $this->headers->get('X-PJAX') == true;
	}

	/**
	 * Returns true if the request is an XMLHttpRequest.
	 *
	 * It works if your JavaScript library sets an X-Requested-With HTTP header.
	 * It is known to work with common JavaScript frameworks:
	 *
	 * @author Fabien Potencier <fabien@symfony.com>
	 * @see https://github.com/symfony/http-foundation/blob/6.3/Request.php#L1726
	 * @see https://wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
	 *
	 * @return bool true if the request is an XMLHttpRequest, false otherwise
	 */
	public function isXmlHttpRequest()
	{
		return 'XMLHttpRequest' == ($this->headers['X-Requested-With'] ?? null);
	}

	/**
	 * Determine if the current request is asking for JSON.
	 *
	 * @return bool
	 */
	public function wantsJson()
	{
		$acceptable = $this->getAcceptableContentTypes();

		return isset($acceptable[0]) && Str::contains($acceptable[0], ['/json', '+json']);
	}

	/**
	 * Determines whether the current requests accepts a given content type.
	 *
	 * @param  string|array  $contentTypes
	 * @return bool
	 */
	public function accepts($contentTypes)
	{
		$accepts = $this->getAcceptableContentTypes();

		if (count($accepts) === 0) {
			return true;
		}

		$types = (array) $contentTypes;

		foreach ($accepts as $accept) {
			if ($accept === '*/*' || $accept === '*') {
				return true;
			}

			foreach ($types as $type) {
				if ($this->matchesType($accept, $type) || $accept === strtok($type, '/').'/*') {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Return the most suitable content type from the given array based on content negotiation.
	 *
	 * @param  string|array  $contentTypes
	 * @return string|null
	 */
	public function prefers($contentTypes)
	{
		$accepts = $this->getAcceptableContentTypes();

		$contentTypes = (array) $contentTypes;

		foreach ($accepts as $accept) {
			if (in_array($accept, ['*/*', '*'])) {
				return $contentTypes[0];
			}

			foreach ($contentTypes as $contentType) {
				$type = $contentType;

				if (! is_null($mimeType = $this->getMimeType($contentType))) {
					$type = $mimeType;
				}

				if ($this->matchesType($type, $accept) || $accept === strtok($type, '/').'/*') {
					return $contentType;
				}
			}
		}
	}

	/**
	 * Determine if the current request accepts any content type.
	 *
	 * @return bool
	 */
	public function acceptsAnyContentType()
	{
		$acceptable = $this->getAcceptableContentTypes();

		return count($acceptable) === 0 || (
			isset($acceptable[0]) && ($acceptable[0] === '*/*' || $acceptable[0] === '*')
		);
	}

	/**
	 * Parses the accept header broguth from the server parameters.
	 *
	 * @return array List of content types in preferable order
	 */
	public function parseAcceptHeader()
	{
		$params = $this->getServerParams();
		//
		$acceptable = explode(
			',', ($params['HTTP_ACCEPT'] ?? $params['Accept'] ?? '')
		);
		//
		$acceptable = array_map(function($item) {
			$pair = explode(';', $item);
			//
			if (count($pair) >= 2) {
				return [(float)str_replace('q=', '', $pair[1]), $pair[0]];
			}
			//
			return [1.0, $pair[0]];
		}, $acceptable);
		//
		rsort($acceptable);
		//
		$acceptable = array_map(function($item) {
			return $item[1];
		}, $acceptable);
		//
		return $acceptable;
	}

	/**
	 * Gets a list of content types acceptable by the client browser.
	 *
	 * @return array List of content types in preferable order
	 */
	public function getAcceptableContentTypes()
	{
		if (null !== $this->parsedAcceptableContentTypes) {
			return $this->parsedAcceptableContentTypes;
		}
		//
		return $this->parsedAcceptableContentTypes = $this->parseAcceptHeader();
	}

	/**
	 * Determines whether a request accepts JSON.
	 *
	 * @return bool
	 */
	public function acceptsJson()
	{
		return $this->accepts('application/json');
	}

	/**
	 * Determines whether a request accepts HTML.
	 *
	 * @return bool
	 */
	public function acceptsHtml()
	{
		return $this->accepts('text/html');
	}

	/**
	 * Determine if the given content types match.
	 *
	 * @param  string  $actual
	 * @param  string  $type
	 * @return bool
	 */
	public static function matchesType($actual, $type)
	{
		if ($actual === $type) {
			return true;
		}

		$split = explode('/', $actual);

		return isset($split[1]) && preg_match('#'.preg_quote($split[0], '#').'/.+\+'.preg_quote($split[1], '#').'#', $type);
	}

	/**
	 * Get the data format expected in the response.
	 *
	 * @param  string  $default
	 * @return string
	 */
	public function format($default = 'html')
	{
		foreach ($this->getAcceptableContentTypes() as $type) {
			if ($format = $this->getFormat($type)) {
				return $format;
			}
		}

		return $default;
	}

	/**
	 * Crafted for the internal PHP functions.
	 * 
	 * @return array
	 */
	public function __debugInfo()
	{
		return [
			'uri' => $this->uri,
			'requestTarget' => $this->requestTarget,
			'format' => $this->format,
			'mimeType' => $this->mimeType,
			'method' => $this->method,
			'overridenMethod' => $this->overridenMethod,
			'httpVersion' => $this->httpVersion,
			'headers' => $this->headers,
			'body' => $this->body,
			'serverParams' => $this->serverParams,
			'cookieParams' => $this->cookieParams,
			'queryStringParams' => $this->queryStringParams,
			'uploadedFiles' => $this->uploadedFiles,
			'parsedBodyContent' => $this->parsedBodyContent,
			'parsedAcceptableContentTypes' => $this->parsedAcceptableContentTypes,
			'attributes' => $this->attributes,
			'endpoint' => $this->endpoint,
			'session' => $this->session,
		];
	}
}