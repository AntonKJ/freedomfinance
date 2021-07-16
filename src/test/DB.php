<?php
namespace Database;

class DB
{
    private static $_instance = null;
    private $db;

    private const DB_HOST = 'mariadb';
	private const DB_NAME = 'test';
	private const DB_USER = 'root';
	private const DB_PASS = 'rootpwd3245';

	public function __construct () {

        try {
            $this->db = new \PDO(
                'mysql:host=' . self::DB_HOST . ';dbname=' . self::DB_NAME,
                self::DB_USER,
                self::DB_PASS
            );
            $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->db->exec("set names utf8");
        }
        catch(PDOException $e) {
            echo $e->getMessage();
        }


    }

	private function __clone () {}
	private function __wakeup () {}

    public function getDb() {
        if ($this->db instanceof \PDO) {
            return $this->db;
        }
    }
}