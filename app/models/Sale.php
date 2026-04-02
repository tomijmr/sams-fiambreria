<?php

class Sale extends Model
{
    public function createSale(array $saleData, array $items): int
    {
        $this->db->beginTransaction();

        try {
            $stmtSale = $this->db->prepare('INSERT INTO sales (total, payment_method, created_at) VALUES (:total, :payment_method, NOW())');
            $stmtSale->execute([
                'total' => $saleData['total'],
                'payment_method' => $saleData['payment_method'],
            ]);

            $saleId = (int)$this->db->lastInsertId();

            $stmtItem = $this->db->prepare('INSERT INTO sale_items (sale_id, product_id, quantity, unit_label, unit_price, subtotal) VALUES (:sale_id, :product_id, :quantity, :unit_label, :unit_price, :subtotal)');

            $productModel = new Product();

            foreach ($items as $item) {
                $stmtItem->execute([
                    'sale_id' => $saleId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_label' => $item['unit_label'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                $stockAmount = $item['unit_type'] === 'weight' ? ($item['quantity'] / 1000) : $item['quantity'];
                $productModel->decreaseStock((int)$item['product_id'], $item['unit_type'], $stockAmount);
            }

            $this->db->commit();
            return $saleId;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function totalToday(): float
    {
        $stmt = $this->db->query('SELECT COALESCE(SUM(total),0) AS total FROM sales WHERE DATE(created_at) = CURDATE()');
        $row = $stmt->fetch();
        return (float)($row['total'] ?? 0);
    }

    public function countToday(): int
    {
        $stmt = $this->db->query('SELECT COUNT(*) AS qty FROM sales WHERE DATE(created_at) = CURDATE()');
        $row = $stmt->fetch();
        return (int)($row['qty'] ?? 0);
    }
}
