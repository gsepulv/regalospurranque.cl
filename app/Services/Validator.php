<?php
namespace App\Services;

/**
 * Validación centralizada de datos
 * Reglas: required, string, email, numeric, integer, min, max, in, url, slug, unique
 * Uso: $v = new Validator($data, ['campo' => 'required|string|min:3|max:100']);
 */
class Validator
{
    private array $data;
    private array $rules;
    private array $errors = [];
    private array $validated = [];

    public function __construct(array $data, array $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    /**
     * Ejecutar validación
     */
    public function validate(): self
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $params = [];
                if (str_contains($rule, ':')) {
                    [$rule, $paramStr] = explode(':', $rule, 2);
                    $params = explode(',', $paramStr);
                }

                $method = 'rule' . ucfirst($rule);
                if (method_exists($this, $method)) {
                    if (!$this->$method($field, $value, $params)) {
                        break; // Detener al primer error del campo
                    }
                }
            }

            // Agregar al array de validados si no tiene errores
            if (!isset($this->errors[$field]) && $value !== null && $value !== '') {
                $this->validated[$field] = is_string($value) ? trim($value) : $value;
            }
        }

        return $this;
    }

    /**
     * Verificar si la validación falló
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Obtener errores
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Obtener solo los datos validados
     */
    public function validated(): array
    {
        return $this->validated;
    }

    /**
     * Obtener primer error de un campo
     */
    public function first(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    // ── Reglas ────────────────────────────────────────────────

    private function ruleRequired(string $field, mixed $value, array $params): bool
    {
        if ($value === null || $value === '' || $value === []) {
            $this->errors[$field] = "El campo {$field} es obligatorio";
            return false;
        }
        return true;
    }

    private function ruleString(string $field, mixed $value, array $params): bool
    {
        if ($value !== null && $value !== '' && !is_string($value)) {
            $this->errors[$field] = "El campo {$field} debe ser texto";
            return false;
        }
        return true;
    }

    private function ruleEmail(string $field, mixed $value, array $params): bool
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "El campo {$field} debe ser un email válido";
            return false;
        }
        return true;
    }

    private function ruleNumeric(string $field, mixed $value, array $params): bool
    {
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = "El campo {$field} debe ser numérico";
            return false;
        }
        return true;
    }

    private function ruleInteger(string $field, mixed $value, array $params): bool
    {
        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_INT)) {
            $this->errors[$field] = "El campo {$field} debe ser un número entero";
            return false;
        }
        return true;
    }

    private function ruleMin(string $field, mixed $value, array $params): bool
    {
        $min = (int) ($params[0] ?? 0);
        if (is_string($value) && mb_strlen($value) < $min) {
            $this->errors[$field] = "El campo {$field} debe tener al menos {$min} caracteres";
            return false;
        } elseif (!is_string($value) && is_numeric($value) && (float) $value < $min) {
            $this->errors[$field] = "El campo {$field} debe ser al menos {$min}";
            return false;
        }
        return true;
    }

    private function ruleMax(string $field, mixed $value, array $params): bool
    {
        $max = (int) ($params[0] ?? 0);
        if (is_string($value) && mb_strlen($value) > $max) {
            $this->errors[$field] = "El campo {$field} no debe exceder {$max} caracteres";
            return false;
        } elseif (!is_string($value) && is_numeric($value) && (float) $value > $max) {
            $this->errors[$field] = "El campo {$field} no debe ser mayor que {$max}";
            return false;
        }
        return true;
    }

    private function ruleIn(string $field, mixed $value, array $params): bool
    {
        if ($value !== null && $value !== '' && !in_array($value, $params, true)) {
            $allowed = implode(', ', $params);
            $this->errors[$field] = "El valor de {$field} debe ser uno de: {$allowed}";
            return false;
        }
        return true;
    }

    private function ruleUrl(string $field, mixed $value, array $params): bool
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->errors[$field] = "El campo {$field} debe ser una URL válida";
            return false;
        }
        return true;
    }

    private function ruleSlug(string $field, mixed $value, array $params): bool
    {
        if ($value && !preg_match('/^[a-z0-9][a-z0-9\-]*[a-z0-9]$/', $value)) {
            $this->errors[$field] = "El campo {$field} solo permite letras minúsculas, números y guiones";
            return false;
        }
        return true;
    }

    private function ruleUnique(string $field, mixed $value, array $params): bool
    {
        if (!$value || count($params) < 1) {
            return true;
        }
        $table = $params[0];
        $column = $params[1] ?? $field;
        $exceptId = $params[2] ?? null;

        $db = \App\Core\Database::getInstance();
        $sql = "SELECT COUNT(*) as total FROM {$table} WHERE {$column} = ?";
        $bindings = [$value];

        if ($exceptId) {
            $sql .= " AND id != ?";
            $bindings[] = $exceptId;
        }

        $result = $db->fetch($sql, $bindings);
        if ((int) ($result['total'] ?? 0) > 0) {
            $this->errors[$field] = "El valor de {$field} ya existe";
            return false;
        }
        return true;
    }
}
