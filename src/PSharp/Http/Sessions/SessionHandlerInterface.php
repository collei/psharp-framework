<?php
namespace PSharp\Http\Sessions;

use SessionHandlerInterface as NativeSessionHandlerInterface;

/**
 * Interface of the Zelatus session handler.
 *
 */
interface SessionHandlerInterface extends NativeSessionHandlerInterface
{
	/**
	 * Re-initialize existing session, or creates a new one.
	 *
	 * This function is called when a session starts or when session_start()
	 * is invoked.
	 *
	 * @param string $path The path where to store/retrieve the session
	 * @param string $sessionName The session name
	 * @return bool True on success, false on failure.
	 */
	public function open($path, $sessionName): bool;

	/**
	 * Closes the current session.
	 *
	 * This function is automatically executed when closing the session, or
	 * explicitly via session_write_close().
	 *
	 * @return bool True on success, false on failure.
	 */
	public function close(): bool;

	/**
	 * Reads the session data from the session storage, and returns the results.
	 *
	 * Called right after the session starts or when session_start() is called.
	 * Please note that SessionHandlerInterface::open() is called before this
	 * method.
	 * This method is called by PHP itself when the session is started.
	 * This method should retrieve the session data from storage by the session
	 * ID provided. The string returned by this method must be in the same
	 * serialized format as when originally passed to the
	 * SessionHandlerInterface::write(). If the record was not found, returns
	 * false. 
	 *
	 * @param string $id The session id.
	 * @return string|false
	 */
	#[\ReturnTypeWillChange]
	public function read($id);

	/**
	 * Writes the session data to the session storage.
	 *
	 * Called by session_write_close(), when session_register_shutdown() fails,
	 * or during a normal shutdown. right after the session starts or when
	 * session_start() is called.
	 * PHP will call this method when the session is ready to be saved and
	 * closed. It encodes the session data from the $_SESSION superglobal to a
	 * serialized string and passes this along with the session ID to this
	 * method for storage.
	 *
	 * @param string $id The session id.
	 * @param string $data The encoded session data.
	 * @return bool True on success, false on failure
	 */
	public function write($id, $data): bool;

	/**
	 * Destorys a session.
	 *
	 * Called by session_regenerate_id() (with $destroy = true),
	 * session_destroy() and then session_decode() fails.
	 *
	 * @param string $id The session ID being destroyed.
	 * @return bool True on success, false on failure.
	 */
	public function destroy($id): bool;

	/**
	 * Cleans up expired sessions.
	 *
	 * Called by session_start(), based on session.gc_divisor,
	 * session.gc_probability and session.gc_maxlifetime settings.
	 *
	 * @param int $max_lifetime Sessions that have not upedated for the last
	 *        max_lifetime seconds will be removed.
	 * @return int|false Return the number of deleted sessions on success,
	 *        false on failure.
	 */
	#[\ReturnTypeWillChange]
	public function gc($maxlifetime);
}