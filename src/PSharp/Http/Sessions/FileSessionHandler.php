<?php
namespace PSharp\Http\Sessions;

/**
 * Performs session handling through session file storage.
 *
 */
class FileSessionHandler implements SessionHandlerInterface
{
	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string
	 */
	private $name;

	/**
	 * @inheritdoc
	 */
	public function open($path, $sessionName): bool
	{
		$this->path = $path;
		$this->name = $sessionName;
		//
		if (!is_dir($this->path)) {
			mkdir($this->path, 0777);
		}
		//
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
		return (string)@file_get_contents($this->sessionFile($id));
	}

	/**
	 * @inheritdoc
	 */
	public function write($id, $data): bool
	{
		return file_put_contents($this->sessionFile($id), $data) === false ? false : true;
	}

	/**
	 * @inheritdoc
	 */
	public function destroy($id): bool
	{
		$file = $this->sessionFile($id);
		//
		if (file_exists($file)) {
			unlink($file);
		}
		//
		return true;
	}

	/**
	 * @inheritdoc
	 */
	#[\ReturnTypeWillChange]
	public function gc($maxlifetime)
	{
		$wildcard = $this->sessionFile('*');
		//
		foreach (glob($wildcard) as $file) {
			if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
				unlink($file);
			}
		}
		//
		return true;
	}

	/**
	 * Make up the session file name.
	 *
	 * @param	string	$id
	 * @return	string
	 */
	protected function sessionFile($id)
	{
		return $this->path.'/sess_'.$id;
	}
}