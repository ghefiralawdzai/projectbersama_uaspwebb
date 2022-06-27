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
$nim = $_GET['nim'] ?? '';

if(empty($nim)){
    $reply['error'] = 'NIM tidak boleh kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try{
    $queryCheck = "SELECT * FROM mahasiswa where nim = :nim";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':nim', $nim);
    $statement->execute();
    $dataMahasiswa = $statement->fetch(PDO::FETCH_ASSOC);

    /*
     * Ambil data dosen berdasarkan kolom dosen
     */
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
        $dataFinal = [
            'nim' => $dataMahasiswa['nim'],
            'nama' => $dataMahasiswa['nama'],
            'prodi' => $dataMahasiswa['prodi'],
            'semester' => $dataMahasiswa['semester'],
            'tanggallahir' => $dataMahasiswa['tanggallahir'],
            'created_at' => $dataMahasiswa['created_at'],
            'dosen' => $dosen,
            'email' => $dataMahasiswa['email'],
        ];
    }

    /*
     * Ambil data matakuliah berdasarkan kolom matakuliah
     */
    if($dataMahasiswa) {
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
    $reply['error'] = 'Data tidak ditemukan NIM '.$nim;
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