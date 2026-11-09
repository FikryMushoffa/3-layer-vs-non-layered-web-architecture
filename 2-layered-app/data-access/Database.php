<?php
/**
 * ============================================================================
 * LAYER 3: DATA LAYER — Database.php
 * Tugas : Membuka koneksi langsung ke database MySQL (customer_management).
 *         Struktur tabel dan data awal bersumber dari file SQL:
 *         database/customer_management.sql
 * ============================================================================
 */

class Database
{
    private static $pdo = null;

    public static function getConnection()
    {
        if (self::$pdo === null) {
            $host     = "localhost";
            $dbname   = "customer_management";
            $username = "root";
            $password = "";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            try {
                // Langsung konek ke database customer_management di MySQL
                self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
            } catch (PDOException $e) {
                // Jika database customer_management belum di-import di MySQL,
                // otomatis jalankan file database/customer_management.sql
                $sqlFile = dirname(__DIR__, 2) . "/database/customer_management.sql";
                if (strpos($e->getMessage(), "1049") !== false && file_exists($sqlFile)) {
                    $pdoSetup = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, $options);
                    $pdoSetup->exec(file_get_contents($sqlFile));
                    self::$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
                } else {
                    throw new RuntimeException(
                        "Koneksi MySQL gagal. Pastikan MySQL di XAMPP/Laragon sudah dinyalakan. Detail: " . $e->getMessage()
                    );
                }
            }
        }

        return self::$pdo;
    }
}
