<?php

include 'DB.php';

use Database\DB;

$dbClass = new DB();
$db = $dbClass->getDb();

new DataProduct($db);

class DataProduct
{
    protected $db,
              $dataForm,
              $responseErrors = [];

    public function __construct ($db) {
        $this->db = $db;
        if ( $_GET['productlist'] == 1 ){
            $this->getProductlist();
        } else {
            $this->setDataForm();
            $this->validate();
        }
    }

    protected function validate () {

        if (empty($this->dataForm['datetime']) || !$this->validateDate($this->dataForm['datetime'])) {
            $this->responseErrors['datetime'] = "Дата '{$this->dataForm['datetime']}' указана неверно.\n";
        }

        if (empty($this->dataForm['price']) || !is_numeric($this->dataForm['price'])) {
            $this->responseErrors['price'] = "Цена '{$this->dataForm['price']}' указана неверно.\n";
        }

        if (empty($this->dataForm['title']) || !is_string(strip_tags($this->dataForm['title']))) {
            $this->responseErrors['title'] = "Название '{$this->dataForm['title']}' указано неверно.\n";
        }

        if ( count($this->responseErrors) == 0 ) {

            setlocale(LC_MONETARY, 'us_US.utf8');
            $price =  money_format('%+n', $this->dataForm['price']);

            $idata = $this->db->prepare("INSERT INTO `products` (title,price,datetime) VALUES (:title,:price,:datetime)");

            $idata->execute([
                'title' => $this->dataForm['title'],
                'price' => $price,
                'datetime' => $this->dataForm['datetime']
            ]);

            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode([
                'status' => 'error',
                'errors' => $this->responseErrors
            ]);
        }
    }

    protected function validateDate($date, $format = 'Y.m.d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    protected function getProductList() {
        $result = $this->db->query("SELECT * FROM `products`");
        echo json_encode($result->fetchAll(), JSON_UNESCAPED_UNICODE);
    }

    protected function setDataForm(): void
    {
        $this->dataForm = $_POST;
    }
}
