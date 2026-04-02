<?php

class User extends Model
{
    public function findByUsername(string $username): ?array
    {
        $sql = 'SELECT id, name, username, password FROM users WHERE username = :username LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        return $user ?: null;
    }
}
