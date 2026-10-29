<?php
namespace App\Validators;

final class ProductoValidator
{
    public function validate(array $data, bool $actualizar = false): array
    {
        $errors = [];

        foreach (['sku' => 50, 'nombre_producto' => 100, 'categoria' => 50] as $field => $length) {
            if ($actualizar && !array_key_exists($field, $data)) {
                continue;
            }

            $value = $data[$field] ?? null;
            if (!is_string($value) || trim($value) === '' || mb_strlen($value) > $length) {
                $errors[$field] = "Es obligatorio y debe tener máximo {$length} caracteres.";
            }
        }

        if (isset($data['descripcion'])
            && (!is_string($data['descripcion']) || strlen($data['descripcion']) > 65535)) {
            $errors['descripcion'] = 'Debe ser un texto de máximo 65535 bytes o null.';
        }

        if (!$actualizar || array_key_exists('precio_unitario', $data)) {
            $price = $data['precio_unitario'] ?? null;
            if ((!is_string($price) && !is_int($price) && !is_float($price))
                || !preg_match('/^-?\d+(?:\.\d{1,2})?$/', (string) $price)
                || abs((float) $price) > 99999999.99) {
                $errors['precio_unitario'] = 'Es obligatorio y debe tener hasta 8 enteros y 2 decimales.';
            }
        }

        if (!$actualizar || array_key_exists('stock_actual', $data)) {
            $value = $data['stock_actual'] ?? null;
            $stock = filter_var($value, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => -2147483648, 'max_range' => 2147483647],
            ]);
            if (is_bool($value) || $stock === false) {
                $errors['stock_actual'] = 'Es obligatorio y debe ser un entero entre -2147483648 y 2147483647.';
            }
        }

        return $errors;
    }
}
