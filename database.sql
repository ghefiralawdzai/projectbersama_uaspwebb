create database uaspweb ;

use uaspweb;

create table mahasiswa (
    nim varchar(15) primary key,
    nama varchar(255),
    prodi varchar(255),
    semester int,
    tanggallahir date,
    email varchar(255),
    dosen varchar(50),
    matakuliah varchar(50),
    created_at datetime default CURRENT_TIMESTAMP
);

create table dosen(
nip varchar(20) primary key,
nama varchar(255),
tanggallahir date,
alamat varchar(255),
nohp varchar(12),
jabatan varchar(255),
matakuliah varchar(10),
created_at datetime default CURRENT_TIMESTAMP
);

create table matakuliah (
	kodemk varchar(10) primary key,
	namamk varchar(255),
	kurikulum varchar(10),
	sks int,
	kategori varchar(20),
	jadwal varchar(30),
	created_at datetime default CURRENT_TIMESTAMP
);



insert into mahasiswa values ("12050112655", "Dwi Nur Fitrianto", "Teknik Informnatika", 4, "2001-12-20", "dwinurfitrianto360@gmail.com",1,default);
insert into mahasiswa values ("12050112657", "gege", "Teknik Informnatika", 4, "2001-10-20", "gege@gmail.com", 2, default);

insert into dosen values ("123456789", "pizaini", "1980-12-20", "pekanbaru", "081234567890", "kajur teknik informatika",1, default);
insert into dosen values ("987654321", "jasril", "1970-10-02", "pekanbaru", "082366554411", "sekjur teknik informatika",2, default);


insert into matakuliah values ("PIF1110", "Kalkulus", "2020-TIF", 3, "wajib", "rabu, 07:00", 1, default);
insert into matakuliah values ("PIF1111", "Tata Tulis Karya Ilmiah", "2020-TIF", 3, "wajib", "senin, 13:00", 2, default);