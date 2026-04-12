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
        $barcode = trim($barcode);
        $stmt = $this->db->prepare('SELECT * FROM products WHERE TRIM(barcode) = :barcode LIMIT 1');
        $stmt->execute(['barcode' => $barcode]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function searchForPos(string $term, int $limit = 15): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $sql = 'SELECT id, name, barcode, unit_type, sale_price, stock_kg, stock_units
                FROM products
                WHERE TRIM(barcode) LIKE :barcode
                   OR UPPER(name) LIKE UPPER(:name)
                ORDER BY name ASC
                LIMIT :limit';

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':barcode', '%' . $term . '%', PDO::PARAM_STR);
        $stmt->bindValue(':name', '%' . $term . '%', PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
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

    public function upsertFromBulk(array $payload, string $stockMode = 'replace'): void
    {
        $barcode = trim($payload['barcode']);
        $name = trim($payload['name']);
        $quantity = (float)$payload['quantity'];
        $salePrice = (float)$payload['sale_price'];
        $defaultUnitType = $payload['unit_type'] ?? 'unit';
        $defaultProfit = (float)($payload['default_profit'] ?? 30);
        $supplierId = $payload['supplier_id'] ?? null;

        $existing = $this->findByBarcode($barcode);

        if ($existing) {
            $unitType = $existing['unit_type'];
            $profit = (float)$existing['profit_percent'];
            $effectiveSalePrice = $salePrice > 0 ? $salePrice : (float)$existing['sale_price'];
            $divisor = 1 + ($profit / 100);
            $costPrice = $divisor > 0 ? round($effectiveSalePrice / $divisor, 2) : $effectiveSalePrice;

            $stockKg = (float)$existing['stock_kg'];
            $stockUnits = (float)$existing['stock_units'];

            if ($unitType === 'weight') {
                $stockKg = $stockMode === 'add' ? $stockKg + $quantity : $quantity;
            } else {
                $stockUnits = $stockMode === 'add' ? $stockUnits + $quantity : $quantity;
            }

            $this->update((int)$existing['id'], [
                'name' => $name !== '' ? $name : $existing['name'],
                'barcode' => $barcode,
                'unit_type' => $unitType,
                'stock_kg' => $stockKg,
                'stock_units' => $stockUnits,
                'cost_price' => $costPrice,
                'profit_percent' => $profit,
                'sale_price' => $effectiveSalePrice,
                'supplier_id' => $existing['supplier_id'],
            ]);

            return;
        }

        $profit = $defaultProfit;
        $effectiveSalePrice = $salePrice > 0 ? $salePrice : 0;
        $divisor = 1 + ($profit / 100);
        $costPrice = $divisor > 0 ? round($effectiveSalePrice / $divisor, 2) : $effectiveSalePrice;

        $stockKg = $defaultUnitType === 'weight' ? $quantity : 0;
        $stockUnits = $defaultUnitType === 'unit' ? $quantity : 0;

        $this->create([
            'name' => $name !== '' ? $name : 'Producto sin nombre',
            'barcode' => $barcode,
            'unit_type' => $defaultUnitType,
            'stock_kg' => $stockKg,
            'stock_units' => $stockUnits,
            'cost_price' => $costPrice,
            'profit_percent' => $profit,
            'sale_price' => $effectiveSalePrice,
            'supplier_id' => $supplierId,
        ]);
    }
}
