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

    public function byDate(string $date): array
    {
        $stmt = $this->db->prepare('SELECT id, total, payment_method, created_at FROM sales WHERE DATE(created_at) = :date ORDER BY created_at ASC, id ASC');
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll();
    }

    public function totalByDate(string $date): float
    {
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(total), 0) AS total FROM sales WHERE DATE(created_at) = :date');
        $stmt->execute(['date' => $date]);
        $row = $stmt->fetch();
        return (float)($row['total'] ?? 0);
    }

    public function paymentMethodSummaryByDate(string $date): array
    {
        $stmt = $this->db->prepare('SELECT payment_method, COUNT(*) AS qty, COALESCE(SUM(total), 0) AS total FROM sales WHERE DATE(created_at) = :date GROUP BY payment_method ORDER BY total DESC');
        $stmt->execute(['date' => $date]);
        return $stmt->fetchAll();
    }

    public function historyByDateRange(string $fromDate, string $toDate): array
    {
        $sql = 'SELECT s.id,
                       s.created_at,
                       s.payment_method,
                       s.total,
                       COUNT(si.id) AS items_count
                FROM sales s
                LEFT JOIN sale_items si ON si.sale_id = s.id
                WHERE DATE(s.created_at) BETWEEN :from_date AND :to_date
                GROUP BY s.id, s.created_at, s.payment_method, s.total
                ORDER BY s.created_at DESC, s.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        return $stmt->fetchAll();
    }

    public function itemsBySale(int $saleId): array
    {
        $sql = 'SELECT si.quantity,
                       si.unit_label,
                       si.unit_price,
                       si.subtotal,
                       p.name AS product_name,
                       p.barcode
                FROM sale_items si
                INNER JOIN products p ON p.id = si.product_id
                WHERE si.sale_id = :sale_id
                ORDER BY si.id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sale_id' => $saleId]);
        return $stmt->fetchAll();
    }
}
