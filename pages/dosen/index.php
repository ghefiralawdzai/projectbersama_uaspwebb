<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */
try{
    /**
     * Prepare query dosen limit 50 rows
     */
    $statement = $connection->prepare("select * from dosen order by created_at desc limit 50");
    $isOk = $statement->execute();
    $resultsDosen = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data matakuliah
     */
    $stmMatakuliah = $connection->prepare("select * from matakuliah");
    $isOk = $stmMatakuliah->execute();
    $resultsMatakuliah = $stmMatakuliah->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table dosen dan matakuliah
     * Gabungkan data berdasarkan kolom kodemk matakuliah
     * Jika kodemk matakuliah tidak ditemukan, default "tidak diketahui'
     */
    $finalResults = [];
    $idsMatakuliah = array_column($resultsMatakuliah, 'kodemk');
    foreach ($resultsDosen as $dosen){
        /*
         * Default matakuliah 'Tidak diketahui'
         */
        $matakuliah = [
            'kodemk' => $dosen['matakuliah'],
            'namamk' => 'Tidak diketahui'
        ];
        /*
         * Cari matakuliah berdasarkan kodemk
         */
        $findByIdMatakuliah = array_search($dosen['matakuliah'], $idsMatakuliah);

        /*
         * Jika kodemk ditemukan
         */
        if($findByIdMatakuliah !== false){
            $findDataMatakuliah = $resultsMatakuliah[$findByIdMatakuliah];
            $matakuliah = [
                'kodemk' => $findDataMatakuliah['kodemk'],
                'namamk' => $findDataMatakuliah['namamk']
            ];
        }

        $finalResults[] = [
            'nip' => $dosen['nip'],
            'nama' => $dosen['nama'],
            'tanggallahir' => $dosen['tanggallahir'],
            'alamat' => $dosen['alamat'],
            'nohp' => $dosen['nohp'],
            'created_at' => $dosen['created_at'],
            'matakuliah' => $matakuliah,
            'jabatan' => $dosen['jabatan']
        ];
    }

    $reply['data'] = $finalResults;
}catch (Exception $exception){
    $reply['error'] = $exception->getMessage();
    echo json_encode($reply);
    http_response_code(400);
    exit(0);
}

if(!$isOk){
    $reply['error'] = $statement->errorInfo();
    http_response_code(400);
}
/*
 * Query OK
 * set status == true
 * Output JSON
 */
$reply['status'] = true;
header('Content-Type: application/json');
echo json_encode($reply);