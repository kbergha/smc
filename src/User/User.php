<?php declare(strict_types=1);

namespace Smc\User;

use DateMalformedStringException;
use DateTimeImmutable;
use PDO;
use Smc\Database\Connection;
use Smc\Router\RedirectException;

class User
{
    public const int USER_STATUS_NONE = 0;
    public const int USER_STATUS_LOCKED = 1;
    public const int USER_STATUS_REQUIRE_PASSWORD_RESET = 2;

    private const string TIMING_DUMMY_PASSWORD = 'dummy-password-to-work-on';

    protected ?PDO $pdo = null;

    public function __construct()
    {

    }

    private function pdo(): PDO
    {
        return $this->pdo ??= Connection::getPdo();
    }

    public function login(?string $username, ?string $password): void
    {
        if (is_null($username) || is_null($password)) {
             throw new UserException('Invalid username or password');
        }

        // @todo: status
        $statement = $this->pdo()->prepare('SELECT id, password FROM users WHERE username = :username LIMIT 1');
        $statement->bindParam(':username', $username);
        $statement->execute();

        $result = $statement->fetch(PDO::FETCH_ASSOC);
        if ($result === false) {
            // No user found

            // Try to mitigate username enumeration by always doing the same work as when a user exists.
            password_hash(self::TIMING_DUMMY_PASSWORD, PASSWORD_DEFAULT);

            throw new UserException('Invalid username or password');
        }

        $storedHash = $result['password'];

        // Verify stored hash against plain-text password
        if (password_verify($password, $storedHash)) {
            // Check if the algorithm or the options have changed
            if (password_needs_rehash($storedHash, PASSWORD_DEFAULT)) {
                // If so, create a new hash, and replace the old one
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                // Update the user record with the $newHash
                $newHashStatement = $this->pdo()->prepare('UPDATE users SET password = :password WHERE id = :id');
                $newHashStatement->bindParam(':id', $result['id'], PDO::PARAM_INT);
                $newHashStatement->bindParam(':password', $newHash);
                $newHashStatement->execute();

                if ($newHashStatement->rowCount() === 0) {
                    throw new UserException('Unable to update hash');
                }

                unset($newHash, $newHashStatement);
            }

            // Perform the login.
            $loggedInAt = new \DateTime()->format(DATE_ATOM);
            Session::regenerateId(true);
            Session::setVariable('loggedIn', true);
            Session::setVariable('loggedInAt', $loggedInAt);
            Session::setVariable('loggedInUserId', $result['id']);

            $lastLoginStatement = $this->pdo()->prepare('UPDATE users SET last_login = :last_login WHERE id = :id');
            $lastLoginStatement->bindParam(':id', $result['id'], PDO::PARAM_INT);
            $lastLoginStatement->bindParam(':last_login', $loggedInAt);
            $lastLoginStatement->execute();

            if ($lastLoginStatement->rowCount() === 0) {
                throw new UserException('Unable to update required user data');
            }

            unset($lastLoginStatement, $loggedInAt, $result);

            throw new RedirectException('/dashboard/');
        } else {
            throw new UserException('Invalid username or password');
        }
    }

    public function isLoggedIn(): bool
    {
        return Session::getVariable('loggedIn') ?? false;
    }

    /**
     * How long the current session has been logged in.
     *
     * Nobody logged in is 0.
     * Missing, invalid or malformed date is PHP_INT_MAX
     * Timestamp in the future is 0
     */
    public function loggedInForSeconds(): int
    {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        $loggedInAt = Session::getVariable('loggedInAt');

        if (!is_string($loggedInAt)) {
            return PHP_INT_MAX;
        }

        try {
            $timestamp = new DateTimeImmutable($loggedInAt)->getTimestamp();
        } catch (DateMalformedStringException) {
            return PHP_INT_MAX;
        }

        // A timestamp in the future means a clock that moved, not a negative age.
        return max(0, time() - $timestamp);
    }

    public function logout(): void
    {
        Session::destroy();
    }
}
