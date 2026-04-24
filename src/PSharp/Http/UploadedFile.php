<?php
namespace PSharp\Http;

use RuntimeException;
use PSharp\Streams\Stream;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Represents an uploaded file instance. 
 */
class UploadedFile implements UploadedFileInterface
{
	/**
	 * @var \Psr\Http\Message\StreamInterface
	 */
	protected $stream;

	/**
	 * @var int
	 */
	protected $size;

	/**
	 * @var int
	 */
	protected $error;

	/**
	 * @var string
	 */
	protected $clientFilename;

	/**
	 * @var string
	 */
	protected $clientMediaType;

	/**
	 * @var string
	 */
	protected $uploadedFile;

	/**
	 * @var bool
	 */
	protected $moved;

	/**
	 * @var string
	 */
	protected $destinationPath;

	/**
	 * Instantiate me.
	 *
	 * @param \Psr\Http\Message\StreamInterface $stream
	 * @param int $size
	 * @param int $error
	 * @param string $clientFilename
	 * @param string $clientMediaType
	 */
	public function __construct(
		StreamInterface $stream,
		int $size = null,
		int $error = \UPLOAD_ERR_OK,
		string $clientFilename = null,
		string $clientMediaType = null
	) {
		$this->stream = $stream;
		$this->size = $size;
		$this->error = $error;
		$this->clientFilename = $clientFilename;
		$this->clientMediaType = $clientMediaType;
		//
		if ($filename = $stream->getMetadata('filename')) {
			$this->uploadedFile = realpath($filename);
		}
		//
		$this->moved = false;
	}

	/**
	 * Retrieve a stream representing the uploaded file.
	 *
	 * @return StreamInterface Stream representation of the uploaded file.
	 * @throws \RuntimeException in cases when no stream is available.
	 * @throws \RuntimeException in cases when no stream can be created.
	 */
	public function getStream()
	{
		return $this->stream;
	}

	/**
	 * Move the uploaded file to a new location.
	 *
	 * Use this method as an alternative to move_uploaded_file(). This method is
	 * guaranteed to work in both SAPI and non-SAPI environments.
	 * Implementations must determine which environment they are in, and use the
	 * appropriate method (move_uploaded_file(), rename(), or a stream
	 * operation) to perform the operation.
	 *
	 * $targetPath may be an absolute path, or a relative path. If it is a
	 * relative path, resolution should be the same as used by PHP's rename()
	 * function.
	 *
	 * The original file or stream MUST be removed on completion.
	 *
	 * If this method is called more than once, any subsequent calls MUST raise
	 * an exception.
	 *
	 * When used in an SAPI environment where $_FILES is populated, when writing
	 * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
	 * used to ensure permissions and upload status are verified correctly.
	 *
	 * If you wish to move to a stream, use getStream(), as SAPI operations
	 * cannot guarantee writing to stream destinations.
	 *
	 * @see http://php.net/is_uploaded_file
	 * @see http://php.net/move_uploaded_file
	 * @param string $targetPath Path to which to move the uploaded file.
	 * @return $this
	 * @throws \InvalidArgumentException if the $targetPath specified is invalid.
	 * @throws \RuntimeException on any error during the move operation.
	 * @throws \RuntimeException on the second or subsequent call to the method.
	 */
	public function moveTo($targetPath, string $withName =null)
	{
		if ($this->moved) {
			throw new RuntimeException(sprintf(
				'File [%s] was already moved !', $this->clientFilename
			));
		}
		//
		$originalName = $this->clientFilename ?: $this->uploadedFile;
		//
		$beingSavedName = empty($withName)
			? (sha1($originalName) . '.' . pathinfo($originalName, PATHINFO_EXTENSION))
			: ($withName . '.' . pathinfo($originalName, PATHINFO_EXTENSION));
		//
		$destination = $targetPath . DIRECTORY_SEPARATOR . $beingSavedName;
		//
		try {
			clearstatcache();
			//
			if (file_exists($this->uploadedFile) && is_readable($this->uploadedFile) && is_uploaded_file($this->uploadedFile)) {
				move_uploaded_file($this->uploadedFile, $destination);
				//
				$this->moved = true;
				$this->destinationPath = $destination;
			} elseif ($this->stream && $this->stream->isReadable()) {
				$this->stream->toStringStream()->saveToFile($destination);
				//
				$this->moved = true;
				$this->destinationPath = $destination;
			}
		} catch (Throwable $t) {
			$message = sprintf(
				'File %s could not be uploaded: intermediate %s failed.',
				$this->clientMediaType, $this->uploadedFile
			);
			//
			throw new RuntimeException($message, 0, $t);
		}
		//
		return $this;
	}

	/**
	 * Retrieve the saved file name.
	 *
	 * @return string|null
	 */
	public function getDestinationPath()
	{
		return $this->destinationPath;
	}

	/**
	 * Retrieve the file size.
	 *
	 * @return int|null The file size in bytes or null if unknown.
	 */
	public function getSize()
	{
		return $this->size;
	}

	/**
	 * Retrieve the error associated with the uploaded file. Returns
	 * UPLOAD_ERR_OK when successful.
	 *
	 * @see http://php.net/manual/en/features.file-upload.errors.php
	 * @return int One of PHP's UPLOAD_ERR_XXX constants.
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Retrieve the filename sent by the client.
	 *
	 * @return string|null The filename sent by the client or null if none
	 *	 was provided.
	 */
	public function getClientFilename()
	{
		return $this->clientFilename;
	}

	/**
	 * Retrieve the media type sent by the client.
	 *
	 * @return string|null The media type sent by the client or null if none
	 *	 was provided.
	 */
	public function getClientMediaType()
	{
		return $this->clientMediaType;
	}
}