<?php

class User extends Model
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;

    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT id, name, username, password, role, failed_attempts, locked_until FROM users WHERE username = :username LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function isLocked(array $user): bool
    {
        return !empty($user['locked_until']) && strtotime($user['locked_until']) > time();
    }

    public function registerFailedAttempt(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET failed_attempts = failed_attempts + 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $stmt = $this->db->prepare('SELECT failed_attempts FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $attempts = (int)$stmt->fetchColumn();

        if ($attempts >= self::MAX_ATTEMPTS) {
            $lockUntil = date('Y-m-d H:i:s', time() + self::LOCK_MINUTES * 60);
            $stmt = $this->db->prepare('UPDATE users SET locked_until = :locked_until WHERE id = :id');
            $stmt->execute(['locked_until' => $lockUntil, 'id' => $id]);
        }
    }

    public function resetAttempts(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
