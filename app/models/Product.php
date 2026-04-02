<?php

class Product extends Model
{
    public function all(): array
    {
        $sql = 'SELECT p.*, s.name AS supplier_name
                FROM products p
                LEFT JOIN suppliers s ON s.id = p.supplier_id
                ORDER BY p.name ASC';
        return $this->db->query($sql)->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByBarcode(string $barcode): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM products WHERE barcode = :barcode LIMIT 1');
        $stmt->execute(['barcode' => $barcode]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): bool
    {
        $sql = 'INSERT INTO products (name, barcode, unit_type, stock_kg, stock_units, cost_price, profit_percent, sale_price, supplier_id)
                VALUES (:name, :barcode, :unit_type, :stock_kg, :stock_units, :cost_price, :profit_percent, :sale_price, :supplier_id)';

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $data['id'] = $id;
        $sql = 'UPDATE products
                SET name = :name,
                    barcode = :barcode,
                    unit_type = :unit_type,
                    stock_kg = :stock_kg,
                    stock_units = :stock_units,
                    cost_price = :cost_price,
                    profit_percent = :profit_percent,
                    sale_price = :sale_price,
                    supplier_id = :supplier_id
                WHERE id = :id';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM products WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function decreaseStock(int $id, string $unitType, float $amount): bool
    {
        if ($unitType === 'weight') {
            $stmt = $this->db->prepare('UPDATE products SET stock_kg = stock_kg - :amount WHERE id = :id AND stock_kg >= :amount');
        } else {
            $stmt = $this->db->prepare('UPDATE products SET stock_units = stock_units - :amount WHERE id = :id AND stock_units >= :amount');
        }

        return $stmt->execute([
            'id' => $id,
            'amount' => $amount,
        ]);
    }
}
