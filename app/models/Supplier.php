<?php

class Supplier extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM suppliers ORDER BY name ASC')->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO suppliers (name, phone, contact) VALUES (:name, :phone, :contact)');
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE suppliers SET name = :name, phone = :phone, contact = :contact WHERE id = :id');
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM suppliers WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
