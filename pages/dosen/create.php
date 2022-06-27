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
$nip = $_POST['nip'] ?? '';
$nama = $_POST['nama'] ?? '';
$tanggallahir = $_POST['tanggallahir'] ?? date('Y-m-d');
$alamat = $_POST['alamat'] ?? '';
$nohp = $_POST['nohp'] ?? '';
$jabatan = $_POST['jabatan'] ?? '';
$matakuliah = $_POST['matakuliah'] ?? 0;


/**
 * Validation empty fields
 */
$isValidated = true;
if($nip === false){
    $reply['error'] = "NIP harus diisi";
    $isValidated = false;
}
if(empty($nama)){
    $reply['error'] = 'NAMA harus diisi';
    $isValidated = false;
}
if(empty($tanggallahir)){
    $reply['error'] = 'TANGGAL LAHIR harus diisi';
    $isValidated = false;
}
if(empty($alamat)){
    $reply['error'] = 'ALAMAT harus diisi';
    $isValidated = false;
}
if(empty($nohp)){
    $reply['error'] = 'NOMOR HP harus diisi';
    $isValidated = false;
}
if(empty($jabatan)){
    $reply['error'] = 'JABATAN harus diisi';
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
    $query = "INSERT INTO dosen (nip, nama, tanggallahir, alamat, nohp, jabatan, matakuliah) 
VALUES (:nip, :nama, :tanggallahir, :alamat, :nohp, :jabatan, :matakuliah)";
    $statement = $connection->prepare($query);
    /**
     * Bind params
     */
    $statement->bindValue(":nip", $nip);
    $statement->bindValue(":nama", $nama);
    $statement->bindValue(":tanggallahir", $tanggallahir);
    $statement->bindValue(":alamat", $alamat);
    $statement->bindValue(":nohp", $nohp);
    $statement->bindValue(":jabatan", $jabatan);
    $statement->bindValue(":matakuliah", $matakuliah);
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
$getResult = "SELECT * FROM dosen WHERE nip = :nip";
$stm = $connection->prepare($getResult);
$stm->bindValue(':nip', $nip);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Get Matakuliah
 */
$stmMatakuliah = $connection->prepare("SELECT * FROM matakuliah where kodemk = :kodemk");
$stmMatakuliah->bindValue(':kodemk', $result['matakuliah']);
$stmMatakuliah->execute();
$resultMatakuliah = $stmMatakuliah->fetch(PDO::FETCH_ASSOC);
/*
 * Defulat matakuliah 'Tidak diketahui'
 */
$matakuliah = [
    'kodemk' => $result['matakuliah'],
    'namamk' => 'Tidak diketahui'
];
if ($resultMatakuliah) {
    $matakuliah = [
        'kodemk' => $resultMatakuliah['kodemk'],
        'namamk' => $resultMatakuliah['namamk']
    ];
}

/*
 * Transform result
 */
$dataFinal = [
    'nip' => $result['nip'],
    'nama' => $result['nama'],
    'tanggallahir' => $result['tanggallahir'],
    'alamat' => $result['alamat'],
    'nohp' => $result['nohp'],
    'jabatan' => $result['jabatan'],
    'created_at' => $result['created_at'],
    'matakuliah' => $matakuliah,
];

/**
 * Show output to client
 * Set status info true
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);