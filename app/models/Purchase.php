<?php

class Purchase extends Model
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTables();
    }

    private function ensureTables(): void
    {
        $this->db->exec("CREATE TABLE IF NOT EXISTS purchases (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_id INT NULL,
            doc_type VARCHAR(30) NOT NULL,
            doc_number VARCHAR(80) NULL,
            date DATE NOT NULL,
            total DECIMAL(12,2) NOT NULL DEFAULT 0,
            notes VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_purchases_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $this->db->exec("CREATE TABLE IF NOT EXISTS purchase_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            purchase_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity DECIMAL(10,3) NOT NULL,
            unit_cost DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(12,2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_purchase_items_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
            CONSTRAINT fk_purchase_items_product FOREIGN KEY (product_id) REFERENCES products(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    public function create(array $header, array $items): int
    {
        if (empty($items)) {
            throw new InvalidArgumentException('La compra no tiene items');
        }

        $this->db->beginTransaction();

        try {
            $stmtPurchase = $this->db->prepare('INSERT INTO purchases (supplier_id, doc_type, doc_number, date, total, notes) VALUES (:supplier_id, :doc_type, :doc_number, :date, 0, :notes)');
            $stmtPurchase->execute([
                'supplier_id' => $header['supplier_id'] ?? null,
                'doc_type' => $header['doc_type'],
                'doc_number' => $header['doc_number'] ?? null,
                'date' => $header['date'],
                'notes' => $header['notes'] ?? null,
            ]);

            $purchaseId = (int)$this->db->lastInsertId();
            $total = 0.0;

            $stmtProduct = $this->db->prepare('SELECT id, unit_type, profit_percent FROM products WHERE id = :id LIMIT 1 FOR UPDATE');
            $stmtItem = $this->db->prepare('INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_cost, subtotal) VALUES (:purchase_id, :product_id, :quantity, :unit_cost, :subtotal)');

            foreach ($items as $item) {
                $stmtProduct->execute(['id' => $item['product_id']]);
                $product = $stmtProduct->fetch();
                if (!$product) {
                    throw new RuntimeException('Producto no encontrado en compra: ' . $item['product_id']);
                }

                $quantity = (float)$item['quantity'];
                $unitCost = (float)$item['unit_cost'];
                $subtotal = round($quantity * $unitCost, 2);
                $total += $subtotal;

                $stmtItem->execute([
                    'purchase_id' => $purchaseId,
                    'product_id' => (int)$product['id'],
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                if ($product['unit_type'] === 'weight') {
                    $stmtStock = $this->db->prepare('UPDATE products SET stock_kg = stock_kg + :qty WHERE id = :id');
                } else {
                    $stmtStock = $this->db->prepare('UPDATE products SET stock_units = stock_units + :qty WHERE id = :id');
                }

                $stmtStock->execute([
                    'qty' => $quantity,
                    'id' => (int)$product['id'],
                ]);

                if ($unitCost > 0) {
                    $profitPercent = (float)$product['profit_percent'];
                    $newSalePrice = round($unitCost + ($unitCost * $profitPercent / 100), 2);

                    $stmtCost = $this->db->prepare('UPDATE products SET cost_price = :cost, sale_price = :sale WHERE id = :id');
                    $stmtCost->execute([
                        'cost' => $unitCost,
                        'sale' => $newSalePrice,
                        'id' => (int)$product['id'],
                    ]);
                }
            }

            $stmtTotal = $this->db->prepare('UPDATE purchases SET total = :total WHERE id = :id');
            $stmtTotal->execute([
                'total' => round($total, 2),
                'id' => $purchaseId,
            ]);

            $this->db->commit();
            return $purchaseId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function recent(int $limit = 20, ?string $docType = null): array
    {
        $limit = max(1, $limit);

        if ($docType === null) {
            $sql = 'SELECT p.*, s.name AS supplier_name
                    FROM purchases p
                    LEFT JOIN suppliers s ON s.id = p.supplier_id
                    ORDER BY p.date DESC, p.id DESC
                    LIMIT :limit';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $sql = 'SELECT p.*, s.name AS supplier_name
                FROM purchases p
                LEFT JOIN suppliers s ON s.id = p.supplier_id
                WHERE p.doc_type = :doc_type
                ORDER BY p.date DESC, p.id DESC
                LIMIT :limit';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':doc_type', $docType, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
