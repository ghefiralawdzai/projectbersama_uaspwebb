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
$nim = $_POST['nim'] ?? '';
$nama = $_POST['nama'] ?? '';
$prodi = $_POST['prodi'] ?? '';
$semester = $_POST['semester'] ?? 0;
$tanggallahir = $_POST['tanggallahir'] ?? date('Y-m-d');
$email = $_POST['email'] ?? '';
$dosen = $_POST['dosen'] ?? 0;
$matakuliah = $_POST['matakuliah'] ?? 0;

/**
 * Validation int value
 */
$jumlahSemester = filter_var($semester, FILTER_VALIDATE_INT);

/**
 * Validation empty fields
 */
$isValidated = true;
if($jumlahSemester === false){
    $reply['error'] = "SEMESTER harus format INT";
    $isValidated = false;
}
if(empty($nim)){
    $reply['error'] = 'NIM harus diisi';
    $isValidated = false;
}
if(empty($nama)){
    $reply['error'] = 'NAMA harus diisi';
    $isValidated = false;
}
if(empty($prodi)){
    $reply['error'] = 'PRODI harus diisi';
    $isValidated = false;
}
if(empty($tanggallahir)){
    $reply['error'] = 'TANGGAL LAHIR harus diisi';
    $isValidated = false;
}
if(empty($email)){
    $reply['error'] = 'EMAIL harus diisi';
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
    $query = "INSERT INTO mahasiswa (nim, nama, prodi, semester, tanggallahir, email, dosen, matakuliah) 
VALUES (:nim, :nama, :prodi, :semester, :tanggallahir, :email, :dosen, :matakuliah)";
    $statement = $connection->prepare($query);
    /**
     * Bind params
     */
    $statement->bindValue(":nim", $nim);
    $statement->bindValue(":nama", $nama);
    $statement->bindValue(":prodi", $prodi);
    $statement->bindValue(":semester", $semester, PDO::PARAM_INT);
    $statement->bindValue(":tanggallahir", $tanggallahir);
    $statement->bindValue(":email", $email);
    $statement->bindValue(":dosen", $dosen, PDO::PARAM_INT);
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
$getResult = "SELECT * FROM mahasiswa WHERE nim = :nim";
$stm = $connection->prepare($getResult);
$stm->bindValue(':nim', $nim);
$stm->execute();
$result = $stm->fetch(PDO::FETCH_ASSOC);

/*
 * Get Dosen
 */
$stmDosen = $connection->prepare("SELECT * FROM dosen where nip = :nip");
$stmDosen->bindValue(':nip', $result['dosen']);
$stmDosen->execute();
$resultDosen = $stmDosen->fetch(PDO::FETCH_ASSOC);
/*
 * Defulat dosen 'Tidak diketahui'
 */
$dosen = [
    'nip' => $result['dosen'],
    'nama' => 'Tidak diketahui'
];
if ($resultDosen) {
    $dosen = [
        'nip' => $resultDosen['nip'],
        'nama' => $resultDosen['nama']
    ];
}

/*
 * Get matakuliah
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
    'nim' => $result['nim'],
    'nama' => $result['nama'],
    'prodi' => $result['prodi'],
    'semester' => $result['semester'],
    'tanggallahir' => $result['tanggallahir'],
    'email' => $result['email'],
    'created_at' => $result['created_at'],
    'dosen' => $dosen,
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