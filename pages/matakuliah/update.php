<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */

/*
 * Validate http method
 */
if($_SERVER['REQUEST_METHOD'] !== 'PATCH'){
    header('Content-Type: application/json');
    http_response_code(400);
    $reply['error'] = 'PATCH method required';
    echo json_encode($reply);
    exit();
}
/**
 * Get input data PATCH
 */
$formData = [];
parse_str(file_get_contents('php://input'), $formData);

$kodemk = $formData['kodemk'] ?? '';
$namamk = $formData['namamk'] ?? '';
$kurikulum = $formData['kurikulum'] ?? '';
$sks = $formData['sks'] ?? 0;
$kategori = $formData['kategori'] ?? '';
$jadwal = $formData['jadwal'] ?? '';

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
    $reply['error'] = 'KODEMK harus diisi';
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
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */
try{
    $queryCheck = "SELECT * FROM matakuliah where kodemk = :kodemk";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':kodemk', $kodemk);
    $statement->execute();
    $row = $statement->rowCount();
    /**
     * Jika data tidak ditemukan
     * rowcount == 0
     */
    if($row === 0){
        $reply['error'] = 'Data tidak ditemukan KODEMK '.$kodemk;
        echo json_encode($reply);
        http_response_code(400);
        exit(0);
    }
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

/**
 * Prepare query
 */
try{
    $fields = [];
    $query = "UPDATE matakuliah SET namamk = :namamk, kurikulum = :kurikulum, sks = :sks, kategori = :kategori, jadwal = :jadwal 
WHERE kodemk = :kodemk";
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
 * Get data
 */
$stmSelect = $connection->prepare("SELECT * FROM matakuliah where kodemk = :kodemk");
$stmSelect->bindValue(':kodemk', $kodemk);
$stmSelect->execute();
$dataFinal = $stmSelect->fetch(PDO::FETCH_ASSOC);



/**
 * Show output to client
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);