<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */
if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'DELETE method required';
    echo json_encode($reply);
    exit();
}

$dataFinal = [];
$kodemk = $_GET['kodemk'] ?? '';

if(empty($kodemk)){
    $reply['error'] = 'KODEMK tidak boleh kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try{
    $queryCheck = "SELECT * FROM matakuliah where kodemk = :kodemk";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kodemk', $kodemk);
    $statement->execute();
    $dataMatakuliah = $statement->fetch(PDO::FETCH_ASSOC);


        $dataFinal = [
            'kodemk' => $dataMatakuliah['kodemk'],
            'namamk' => $dataMatakuliah['namamk'],
            'kurikulum' => $dataMatakuliah['kurikulum'],
            'sks' => $dataMatakuliah['sks'],
            'kategori' => $dataMatakuliah['kategori'],
            'created_at' => $dataMatakuliah['created_at'],
            'jadwal' => $dataMatakuliah['jadwal'],
        ];
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Show response
 */
if(!$dataFinal){
    $reply['error'] = 'Data tidak ditemukan KODEMK '.$kodemk;
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/*
 * Otherwise show data
 */
$reply['status'] = true;
$reply['data'] = $dataFinal;
header('Content-Type: application/json');
echo json_encode($reply);