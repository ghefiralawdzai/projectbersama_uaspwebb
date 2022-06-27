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

$nim = $formData['nim'] ?? '';
$nama = $formData['nama'] ?? '';
$prodi = $formData['prodi'] ?? '';
$semester = $formData['semester'] ?? 0;
$tanggallahir = $formData['tanggallahir'] ?? date('Y-m-d');
$email = $formData['email'] ?? '';
$idDosen = $formData['dosen'] ?? 0;
$idMatakuliah = $formData['matakuliah'] ?? 0;

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
 * METHOD OK
 * Validation OK
 * Check if data is exist
 */
try{
    $queryCheck = "SELECT * FROM mahasiswa where nim = :nim";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':nim', $nim);
    $statement->execute();
    $row = $statement->rowCount();
    /**
     * Jika data tidak ditemukan
     * rowcount == 0
     */
    if($row === 0){
        $reply['error'] = 'Data tidak ditemukan NIM '.$nim;
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
    $query = "UPDATE mahasiswa SET nama = :nama, prodi = :prodi, semester = :semester, tanggallahir = :tanggallahir, email = :email, dosen = :dosen, matakuliah = :matakuliah 
WHERE nim = :nim";
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
    $statement->bindValue(":dosen", $idDosen, PDO::PARAM_INT);
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
$stmSelect = $connection->prepare("SELECT * FROM mahasiswa where nim = :nim");
$stmSelect->bindValue(':nim', $nim);
$stmSelect->execute();
$dataMahasiswa = $stmSelect->fetch(PDO::FETCH_ASSOC);

/*
 * Ambil data dosen berdasarkan kolom dosen
 */
$dataFinal = [];
if($dataMahasiswa) {
    $stmDosen = $connection->prepare("select * from dosen where nip = :nip");
    $stmDosen->bindValue(':nip', $dataMahasiswa['dosen']);
    $stmDosen->execute();
    $resultDosen = $stmDosen->fetch(PDO::FETCH_ASSOC);
    /*
     * Defulat dosen 'Tidak diketahui'
     */
    $dosen = [
        'nip' => $dataMahasiswa['dosen'],
        'nama' => 'Tidak diketahui'
    ];
    if ($resultDosen) {
        $dosen = [
            'nip' => $resultDosen['nip'],
            'nama' => $resultDosen['nama']
        ];
    }

    /*
     * Transoform hasil query dari table mahasiswa dan dosen
     * Gabungkan data berdasarkan kolom nip dosen
     * Jika nip dosen tidak ditemukan, default "tidak diketahui'
     */

    /*
 * Ambil data matakuliah berdasarkan kolom matakuliah
 */
    $dataFinal = [];
    if ($dataMahasiswa) {
        $stmMatakuliah = $connection->prepare("select * from matakuliah where kodemk = :kodemk");
        $stmMatakuliah->bindValue(':kodemk', $dataMahasiswa['matakuliah']);
        $stmMatakuliah->execute();
        $resultMatakuliah = $stmMatakuliah->fetch(PDO::FETCH_ASSOC);
        /*
         * Defulat matakuliah 'Tidak diketahui'
         */
        $matakuliah = [
            'kodemk' => $dataMahasiswa['matakuliah'],
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
            'nim' => $dataMahasiswa['nim'],
            'nama' => $dataMahasiswa['nama'],
            'prodi' => $dataMahasiswa['prodi'],
            'semester' => $dataMahasiswa['semester'],
            'tanggallahir' => $dataMahasiswa['tanggallahir'],
            'created_at' => $dataMahasiswa['created_at'],
            'dosen' => $dosen,
            'matakuliah' => $matakuliah,
            'email' => $dataMahasiswa['email'],
        ];
    }
}

/**
 * Show output to client
 */
$reply['data'] = $dataFinal;
$reply['status'] = $isOk;
header('Content-Type: application/json');
echo json_encode($reply);