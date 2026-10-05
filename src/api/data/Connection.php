<?php

declare(strict_types=1);

namespace TrocaDeTurno\Data;

use PDO;

final class Connection
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly string $host,
        private readonly string $database
    ){}

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            $dsn = "sqlsrv:Server={$this->host};Database={$this->database}";
            $this->pdo = new PDO(
                $dsn, 
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        }

        return $this->pdo;
    }
}

//define('DB_HOST'        , "localhost");
//define('DB_USER'        , "tdt_app");
//define('DB_PASSWORD'    , "Tdt_App@2026#Vale@076");
//define('DB_NAME'        , "troca_de_turno");
//define('DB_DRIVER'      , "sqlsrv");
//
//class Connection
//{
//    private static $connection;
//
//    private function __construct() {}
//
//    public static function get(): PDO
//    {
//        $pdoConfig =
//            "sqlsrv:Server=" . DB_HOST . ";" .
//            "Database=" . DB_NAME . ";" .
//            "TrustServerCertificate=true;";
//        
//        try {
//            if (!isset(self::$connection)) {
//                self::$connection = new PDO($pdoConfig);
//                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//                return self::$connection;
//            }
//        } catch (\Throwable $th) {
//            $mensagem = "Drivers disponiveis: " . implode(",", PDO::getAvailableDrivers());
//            $mensagem .= "\nErro: " . $th->getMessage();
//            throw new Exception($mensagem);
//        }
//    }
//}
?>