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
$nip = $_GET['nip'] ?? '';

if(empty($nip)){
    $reply['error'] = 'NIP tidak boleh kosong';
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

try{
    $queryCheck = "SELECT * FROM dosen where nip = :nip";
    $statement = $connection->prepare($queryCheck);
    $statement->bindValue(':nip', $nip);
    $statement->execute();
    $dataDosen = $statement->fetch(PDO::FETCH_ASSOC);

    /*
     * Ambil data dosen berdasarkan kolom dosen
     */
    if($dataDosen) {
        $stmMatakuliah = $connection->prepare("select * from matakuliah where kodemk = :kodemk");
        $stmMatakuliah->bindValue(':kodemk', $dataDosen['matakuliah']);
        $stmMatakuliah->execute();
        $resultMatakuliah = $stmMatakuliah->fetch(PDO::FETCH_ASSOC);
        /*
         * Defulat dosen 'Tidak diketahui'
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
         * Transoform hasil query dari table dosen dan matakuliah
         * Gabungkan data berdasarkan kolom kodemk
         * Jika kodemk tidak ditemukan, default "tidak diketahui'
         */
        $dataFinal = [
            'nip' => $dataDosen['nip'],
            'nama' => $dataDosen['nama'],
            'tanggallahir' => $dataDosen['tanggallahir'],
            'alamat' => $dataDosen['alamat'],
            'nohp' => $dataDosen['nohp'],
            'created_at' => $dataDosen['created_at'],
            'matakuliah' => $matakuliah,
            'jabatan' => $dataDosen['jabatan'],
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
    $reply['error'] = 'Data tidak ditemukan NIP '.$nip;
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