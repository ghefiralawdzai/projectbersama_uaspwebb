<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    http_response_code(400);
    $reply['error'] = 'POST method required';
    echo json_encode($reply);
    exit();
}
/**
 * Get input data POST
 */
$kodemk = $_POST['kodemk'] ?? '';
$namamk = $_POST['namamk'] ?? '';
$kurikulum = $_POST['kurikulum'] ?? '';
$sks = $_POST['sks'] ?? 0;
$kategori = $_POST['kategori'] ?? '';
$jadwal = $_POST['jadwal'] ?? '';

/**
 * Validation int value
 */
$jumlahsks = filter_var($sks, FILTER_VALIDATE_INT);

/**
 * Validation empty fields
 */
$isValidated = true;
if($jumlahsks === false){
    $reply['error'] = "SKS harus format INT";
    $isValidated = false;
}
if(empty($kodemk)){
    $reply['error'] = 'KODEMK kategori harus diisi';
    $isValidated = false;
}
if(empty($namamk)){
    $reply['error'] = 'NAMAMK harus diisi';
    $isValidated = false;
}
if(empty($kurikulum)){
    $reply['error'] = 'KURIKULUM harus diisi';
    $isValidated = false;
}
if(empty($kategori)){
    $reply['error'] = 'KATEGORI harus diisi';
    $isValidated = false;
}
if(empty($jadwal)){
    $reply['error'] = 'JADWAL harus diisi';
    $isValidated = false;
}

/*
 * Jika filter gagal
 */
if(!$isValidated){
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
/**
 * Method OK
 * Validation OK
 * Prepare query
 */
try{
    $query = "INSERT INTO matakuliah (kodemk, namamk, kurikulum, sks, kategori, jadwal) 
VALUES (:kodemk, :namamk, :kurikulum, :sks, :kategori, :jadwal)";
    $statement = $connection->prepare($query);
    /**
     * Bind params
     */
    $statement->bindValue(":kodemk", $kodemk);
    $statement->bindValue(":namamk", $namamk);
    $statement->bindValue(":kurikulum", $kurikulum);
    $statement->bindValue(":sks", $sks, PDO::PARAM_INT);
    $statement->bindValue(":kategori", $kategori);
    $statement->bindValue(":jadwal", $jadwal);

    /**
     * Execute query
     */
    $isOk = $statement->execute();
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}
/**
 * If not OK, add error info
 * HTTP Status code 400: Bad request
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status#client_error_responses
 */
if(!$isOk){
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}

/*
 * Get last data
 */
$lastkodemk = $connection->lastInsertId();
$getResult = "SELECT * FROM matakuliah WHERE kodemk = :kodemk";
$stm = $connection->prepare($getResult);
$stm->bindValue(':kodemk', $lastkodemk);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/**
 * Show output to client
 * Set status info true
 */
$reply['data'] = $result;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);