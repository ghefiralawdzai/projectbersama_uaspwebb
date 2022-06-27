<?php
include '../../config/koneksi.php';
/**
 * @var $connection PDO
 */
try{
    /**
     * Prepare query mahasiswa limit 50 rows
     */
    $statement = $connection->prepare("select * from mahasiswa order by created_at desc limit 50");
    $isOk = $statement->execute();
    $resultsMahasiswa = $statement->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Ambil data dosen
     */
    $stmDosen = $connection->prepare("select * from dosen");
    $isOk = $stmDosen->execute();
    $resultDosen = $stmDosen->fetchAll(PDO::FETCH_ASSOC);

    /*
     * Transoform hasil query dari table mahasiswa dan dosen
     * Gabungkan data berdasarkan kolom nip doses
     * Jika nip dosen tidak ditemukan, default "tidak diketahui'
     */
    $finalResults = [];
    $idsDosen = array_column($resultDosen, 'nip');
    foreach ($resultsMahasiswa as $mahasiswa) {
        /*
         * Default dosen 'Tidak diketahui'
         */
        $dosen = [
            'nip' => $mahasiswa['dosen'],
            'nama' => 'Tidak diketahui'
        ];
        /*
         * Cari dosen berdasarkan nip
         */
        $findByIdDosen = array_search($mahasiswa['dosen'], $idsDosen);

        /*
         * Jika nip ditemukan
         */
        if ($findByIdDosen !== false) {
            $findDataDosen = $resultDosen[$findByIdDosen];
            $dosen = [
                'nip' => $findDataDosen['nip'],
                'nama' => $findDataDosen['nama']
            ];
        }

        /*
     * Ambil data matakuliah
     */
        $stmMatakuliah = $connection->prepare("select * from matakuliah");
        $isOk = $stmMatakuliah->execute();
        $resultMatakuliah = $stmMatakuliah->fetchAll(PDO::FETCH_ASSOC);

        /*
         * Transoform hasil query dari table mahasiswa dan matakuliah
         * Gabungkan data berdasarkan kolom kodemk
         * Jika kodemk tidak ditemukan, default "tidak diketahui'
         */
        $finalResults = [];
        $idsMatakuliah = array_column($resultMatakuliah, 'kodemk');
        foreach ($resultsMahasiswa as $mahasiswa) {
            /*
             * Default matakuliah 'Tidak diketahui'
             */
            $matakuliah = [
                'kodemk' => $mahasiswa['matakuliah'],
                'namamk' => 'Tidak diketahui'
            ];
            /*
             * Cari matakuliah berdasarkan kodemk
             */
            $findByIdMatakuliah = array_search($mahasiswa['matakuliah'], $idsMatakuliah);

            /*
             * Jika kodemk ditemukan
             */
            if ($findByIdMatakuliah !== false) {
                $findDataMatakuliah = $resultMatakuliah[$findByIdMatakuliah];
                $matakuliah = [
                    'kodemk' => $findDataMatakuliah['kodemk'],
                    'namamk' => $findDataMatakuliah['namamk']
                ];
            }

            $finalResults[] = [
                'nim' => $mahasiswa['nim'],
                'nama' => $mahasiswa['nama'],
                'prodi' => $mahasiswa['prodi'],
                'semester' => $mahasiswa['semester'],
                'tanggallahir' => $mahasiswa['tanggallahir'],
                'created_at' => $mahasiswa['created_at'],
                'dosen' => $dosen,
                'matakuliah' => $matakuliah,
                'email' => $mahasiswa['email']
            ];
        }
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