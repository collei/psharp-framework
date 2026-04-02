<?php
namespace PSharp\Streams;

/**
 * Represents a String stream. Implements PSR-7 StreamInterface through its
 * ancestor.
 *
 */
class StringStream extends Stream
{
	/**
	 * Creates a resouce from string
	 *
	 * @param string $content
	 * @return resource
	 */
	private function resouceFromString(string $content = '')
	{
		$handle = fopen('php://temp', 'w+b');
		//
		fwrite($handle, $content);
		//
		return $handle;
	}
	
	/**
	 * Builds a StringStream from a raw string
	 *
	 * @param string $content
	 * @param array $metadata
	 * @return void
	 */
	public function __construct(string $content, array $metadata = [])
	{
		parent::__construct(
			$this->resouceFromString($content), strlen($content), $metadata
		);
	}

	/**
	 * Save the contents to the file.
	 *
	 * @param string $filename
	 * @return void
	 */
	public function saveToFile($filename)
	{
		$handle = $this->rewind()->getHandle();
		//
		$file = fopen($filename, 'wb');
		//
		while ($buf = fread($handle, 4096)) {
			fwrite($file, $buf);
		}
		//
		fclose($file);
	}
}