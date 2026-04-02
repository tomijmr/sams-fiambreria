<?php

class Report extends Model
{
    public function lowStock(): array
    {
        $sql = "SELECT * FROM products
                WHERE (unit_type = 'weight' AND stock_kg <= 1)
                   OR (unit_type = 'unit' AND stock_units <= 5)
                ORDER BY name ASC";
        return $this->db->query($sql)->fetchAll();
    }
}
