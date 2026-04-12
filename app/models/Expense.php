<?php

class Expense extends Model
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM expenses ORDER BY date DESC, id DESC')->fetchAll();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO expenses (description, category, amount, date) VALUES (:description, :category, :amount, :date)');
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM expenses WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function totalMonth(): float
    {
        $stmt = $this->db->query("SELECT COALESCE(SUM(amount),0) AS total FROM expenses WHERE DATE_FORMAT(date, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')");
        $row = $stmt->fetch();
        return (float)($row['total'] ?? 0);
    }

    public function byDate(string $date): array
    {
        $stmt = $this->db->prepare('SELECT id, description, category, amount, date FROM expenses WHERE date = :date ORDER BY id ASC');
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll();
    }

    public function totalByDate(string $date): float
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(amount),0) AS total FROM expenses WHERE date = :date');
        $stmt->execute(['date' => $date]);
        $row = $stmt->fetch();
        return (float)($row['total'] ?? 0);
    }

    public function providerPaymentsByDate(string $date): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) AS total
                FROM expenses
                WHERE date = :date
                  AND (
                    UPPER(category) LIKE '%PROVEEDOR%'
                    OR UPPER(description) LIKE '%PROVEEDOR%'
                    OR UPPER(description) LIKE '%PAGO%'
                  )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['date' => $date]);
        $row = $stmt->fetch();
        return (float)($row['total'] ?? 0);
    }
}
