<?php
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_clean();
ob_implicit_flush(true);
ob_start();
echo "<pre>";
echo "Starting converting TXT HPT to FIFO application ...\n";
ob_end_flush();
$parameter = $_GET;
$error = array();
$inventory = array();

if (!empty($parameter['source'])) {
	$filedata = $parameter['source'];
	list($filename, $fileext) = explode('.', $filedata);

	$fileoutput = $filename.'_FIFO.'.$fileext;

	$hIn = fopen($filedata, "r") or die('Unable to open input file');
	$hOut = fopen($fileoutput, 'w') or die('Unable to open file with writable permission');
	if ($hIn && $hOut) {
		$cnt = 1;
		echo "\n\nConverting each line...\n";
		while (!feof($hIn)) {
			// sleep(1);
			$flag_kirim = '';
			$curline = fgets($hIn, 4096);
			$lineArray = explode('|,|', trim($curline, '|'));
			if (!$lineArray || empty($lineArray[3])) {
				$error[] = $curline;
				continue;
			}
			$olah = array(
				'kd_lokasi' => $lineArray[0],
				'nama_barang' => $lineArray[1],
				'thn_ang' => $lineArray[2],
				'nodok' => $lineArray[3],
				'tgl_dok' => $lineArray[4], // d-m-Y
				'tgl_buku' => $lineArray[5], // d-m-Y
				'kd_sskel' => $lineArray[6],
				'kd_brg' => $lineArray[7],
				'qty' => $lineArray[8],
				'satuan' => $lineArray[9], 
				'keterangan' => $lineArray[10], // asal
				'no_bukti' => $lineArray[11],
				'jenis_transaksi' => $lineArray[12],
				'rph_sat' => $lineArray[13],
				'rph_total' => $lineArray[14],
				'rph_total' => $lineArray[15],
				'intraco' => trim($lineArray[16]),
				'nobukti2' => trim(trim($lineArray[17],'|')),
			);

			$kode_dok = substr($olah['jenis_transaksi'], 0, 1);
			if (in_array($kode_dok, array('P','M','K'))) {

				$kode_simak = $olah['kd_sskel'].$olah['kd_brg'];

				if ($olah['jenis_transaksi'] == 'P00') { // saldo awal bayangan
					$inventory[$kode_simak][] = array(
						'qty' => $olah['qty'],
						'rph' => $olah['rph_sat'],
						'tgl_buku' => $olah['tgl_buku'],
					);
				// } else if ($olah['jenis_transaksi'] == 'P01') { // koreksi adjustment atau retur dari pasien
				// 	$out = $curline;
				} else if ($kode_dok == 'M') {
					$inventory[$kode_simak][] = array(
						'qty' => $olah['qty'],
						'rph' => $olah['rph_sat'],
						'tgl_buku' => $olah['tgl_buku'],
					);
					$out = $curline;
				} else { // K, proses hitung FIFO dan P01
					$rph_sat = $olah['rph_sat']; // default HPT
					$qty = $olah['qty']; // hitung stok berjalan
					$out = '';
					if (!empty($inventory[$kode_simak])) {
						$split = 0;
						foreach ($inventory[$kode_simak] as $kd => &$inv) {
							$rph_sat = $inv['rph'];
							if ($inv['qty'] >= $qty) {
								$add_ket = '';
								if ($split) {
									$split++;
									$add_ket = '(' . $split . ')';
								}
								$out .= "|" . $olah['kd_lokasi'] . 
									"|,|" . $olah['nama_barang'] . 
									"|,|" . $olah['thn_ang'] . 
									"|,|" . $olah['nodok'] . 
									"|,|" . $olah['tgl_dok'] . 
									"|,|" . $olah['tgl_buku'] . 
									"|,|" . $olah['kd_sskel'] . 
									"|,|" . $olah['kd_brg'] . 
									"|,|" . $qty . 
									"|,|" . $olah['satuan'] . 
									"|,|" . $olah['keterangan'] . $add_ket .
									"|,|" . $olah['no_bukti'] .
									"|,|" . $olah['jenis_transaksi'] . 
									"|,|" . $rph_sat . 
									"|,|" . number_format(($qty * $rph_sat), 2, '.', '') . 
									"|,|" . $flag_kirim . 
									"|,|" . $olah['intraco'] . 
									"|,|" . $olah['nobukti2'] . 
									"|\n";
								$inv['qty'] -= $qty;
								if ($inv['qty'] <= 0) {
									unset($inventory[$kode_simak][$kd]);
								}
								break;
							} else {
								$split++;
								$out .= "|" . $olah['kd_lokasi'] . 
									"|,|" . $olah['nama_barang'] . 
									"|,|" . $olah['thn_ang'] . 
									"|,|" . $olah['nodok'] . 
									"|,|" . $olah['tgl_dok'] . 
									"|,|" . $olah['tgl_buku'] . 
									"|,|" . $olah['kd_sskel'] . 
									"|,|" . $olah['kd_brg'] . 
									"|,|" . $inv['qty'] . 
									"|,|" . $olah['satuan'] . 
									"|,|" . $olah['keterangan'] . '(' . $split . ')' .
									"|,|" . $olah['no_bukti'] .
									"|,|" . $olah['jenis_transaksi'] . 
									"|,|" . $rph_sat . 
									"|,|" . number_format(($inv['qty'] * $rph_sat), 2, '.', '') . 
									"|,|" . $flag_kirim . 
									"|,|" . $olah['intraco'] . 
									"|,|" . $olah['nobukti2'] . 
									"|\n";
								$qty = $qty - $inv['qty'];
								$inv['qty'] = 0;
								if ($inv['qty'] == 0) {
									unset($inventory[$kode_simak][$kd]);
								}
							}
						}
					} else {
						$out = $curline; // jika tidak ada saldo awal
					}

				}
				fwrite($hOut, $out);
				echo "$out";

				$cnt++;
			}
		}
		fclose($hOut);
		fclose($hIn);
	}
	echo "\n\nAll data are succesfully processed!\n";
	echo "New Converted Data are saved to '$fileoutput'";
} else {
	echo 'USAGE: txt2fifo?source=THE_TXT_FILE.txt';
	die('You must specify data source txt file');
}