<?php
namespace App\Core;

/**
 * Singleton PDO para acceso a base de datos
 * Conexión lazy: se conecta al primer uso
 */
class Database
{
    private static ?self $instance = null;
    private ?\PDO $pdo = null;

    private function __construct() {}

    /**
     * Obtener instancia única
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Conexión lazy a la base de datos
     */
    private function connect(): \PDO
    {
        if ($this->pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return $this->pdo;
    }

    /**
     * SELECT múltiple — retorna array de arrays
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * SELECT uno — retorna un array o null
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * INSERT/UPDATE/DELETE — retorna filas afectadas
     */
    public function execute(string $sql, array $params = []): int
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * INSERT helper — retorna lastInsertId
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->execute($sql, array_values($data));
        return (int) $this->connect()->lastInsertId();
    }

    /**
     * UPDATE helper — retorna filas afectadas
     */
    public function update(string $table, array $data, string $where, array $params = []): int
    {
        $set = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($data)));
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        return $this->execute($sql, [...array_values($data), ...$params]);
    }

    /**
     * DELETE helper — retorna filas afectadas
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        return $this->execute("DELETE FROM {$table} WHERE {$where}", $params);
    }

    /**
     * COUNT helper
     */
    public function count(string $table, string $where = '1=1', array $params = []): int
    {
        $result = $this->fetch("SELECT COUNT(*) as total FROM {$table} WHERE {$where}", $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Acceso directo al objeto PDO
     */
    public function getPDO(): \PDO
    {
        return $this->connect();
    }

    // Prevenir clonación y deserialización
    private function __clone() {}
    public function __wakeup()
    {
        throw new \RuntimeException('No se puede deserializar singleton');
    }
}
