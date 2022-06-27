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

$nip = $formData['nip'] ?? '';
$nama = $formData['nama'] ?? '';
$alamat = $formData['alamat'] ?? '';
$nohp = $formData['nohp'] ?? '';
$tanggallahir = $formData['tanggallahir'] ?? date('Y-m-d');
$jabatan = $formData['jabatan'] ?? '';
$idMatakuliah = $formData['matakuliah'] ?? 0;


/**
 * Validation empty fields
 */
$isValidated = true;
if($nip === false){
    $reply['error'] = "NIP harus di isi";
    $isValidated = false;
}
if(empty($nama)){
    $reply['error'] = 'NAMA harus diisi';
    $isValidated = false;
}
if(empty($alamat)){
    $reply['error'] = 'ALAMAT harus diisi';
    $isValidated = false;
}
if(empty($nohp)){
    $reply['error'] = 'NO HP harus diisi';
    $isValidated = false;
}
if(empty($tanggallahir)){
    $reply['error'] = 'TANGGAL LAHIR harus diisi';
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
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */
try{
    $queryCheck = "SELECT * FROM dosen where nip = :nip";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':nip', $nip);
    $statement->execute();
    $row = $statement->rowCount();
    /**
     * Jika data tidak ditemukan
     * rowcount == 0
     */
    if($row === 0){
        $reply['error'] = 'Data tidak ditemukan NIP '.$nip;
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
    $query = "UPDATE dosen SET nama = :nama, alamat = :alamat, nohp = :nohp, tanggallahir = :tanggallahir, jabatan = :jabatan, matakuliah = :matakuliah 
WHERE nip = :nip";
    $statement = $connection->prepare($query);
    /**
     * Bind params
     */
    $statement->bindValue(":nip", $nip);
    $statement->bindValue(":nama", $nama);
    $statement->bindValue(":alamat", $alamat);
    $statement->bindValue(":nohp", $nohp);
    $statement->bindValue(":tanggallahir", $tanggallahir);
    $statement->bindValue(":jabatan", $jabatan);
    $statement->bindValue(":matakuliah", $idMatakuliah);
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
$stmSelect = $connection->prepare("SELECT * FROM dosen where nip = :nip");
$stmSelect->bindValue(':nip', $nip);
$stmSelect->execute();
$dataDosen = $stmSelect->fetch(PDO::FETCH_ASSOC);

/*
* Ambil data matakuliah berdasarkan kolom matakuliah
*/
$dataFinal = [];
if ($dataDosen) {
    $stmMatakuliah = $connection->prepare("select * from matakuliah where kodemk = :kodemk");
    $stmMatakuliah->bindValue(':kodemk', $dataDosen['matakuliah']);
    $stmMatakuliah->execute();
    $resultMatakuliah = $stmMatakuliah->fetch(PDO::FETCH_ASSOC);
    /*
     * Defulat matakuliah 'Tidak diketahui'
     */
    $matakuliah = [
        'kodemk' => $dataDosen['matakuliah'],
        'namamk' => 'Tidak diketahui'
    ];
    if ($resultMatakuliah) {
        $matakuliah = [
            'kodemk' => $resultMatakuliah['kodemk'],
            'namamk' => $resultMatakuliah['namamk']
        ];
    }

    /*
     * Transoform hasil query dari table mahasiswa dan matakuliah
     * Gabungkan data berdasarkan kolom kodemk
     * Jika kodemk tidak ditemukan, default "tidak diketahui'
     */
    $dataFinal = [
        'nip' => $dataDosen['nip'],
        'nama' => $dataDosen['nama'],
        'alamat' => $dataDosen['alamat'],
        'nohp' => $dataDosen['nohp'],
        'tanggallahir' => $dataDosen['tanggallahir'],
        'created_at' => $dataDosen['created_at'],
        'matakuliah' => $matakuliah,
        'jabatan' => $dataDosen['jabatan'],
    ];
}

/**
 * Show output to client
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);