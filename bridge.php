<?php
/**
 * @author Nanda Widyatama <nanda7anku@gmail.com>
 *
 * This tool is used for converting c_nilai_report.csv to TXT ready for bridging
 * https://github.com/nandaabiz/bmn_simak_tools
 */

@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_clean();
ob_implicit_flush(true);
ob_start();
echo "<pre>";
echo "Starting bridging application ...\n";
ob_end_flush();
$parameter = $_GET;
if (empty($parameter['thn'])) {
	die('You must specify year with format YY!');
} else if (empty($parameter['bln'])) {
	die('You must specify month with format MM!');
} else if (empty($parameter['satker'])) {
	die('You must specify satker!');
}

if (!empty($parameter['source'])) {
	$filedata = $parameter['source'];
	$bln = $parameter['bln'];
	$thn = $parameter['thn'];

	if (!empty($parameter['jenis'])) {
		$fileoutput = 'TXT_' . strtoupper($parameter['satker']) . '_' . strtoupper($parameter['jenis']) . '.txt';
	} else {
		if ((int) $bln < 12) {
			if (!empty($parameter['counter'])) {
				$url_next = '//'.$_SERVER['SERVER_NAME']."/bridge.php?source=$filedata&thn=$thn&satker=".strtolower($parameter['satker'])."&bln=".str_pad((int)$bln+1,2,'0',STR_PAD_LEFT)."&counter=".$parameter['counter'];
			} else {
				$url_next = '//'.$_SERVER['SERVER_NAME']."/bridge.php?source=$filedata&thn=$thn&satker=".strtolower($parameter['satker'])."&bln=".str_pad((int)$bln+1,2,'0',STR_PAD_LEFT);
			}
			echo '</pre>NEXT: <a href="'.$url_next.'" target="_self">'.$url_next.'</a><pre>'."\n";
			
		}
		$fileoutput = 'TXT_' . strtoupper($parameter['satker']) . '.txt';
	}
	$hPrev = fopen($fileoutput, 'r');
	$cnt = !empty($parameter['counter']) ? $parameter['counter'] : 0;
	$cntprev = 0;
	if ($hPrev) {

		echo "\n\nCounting previous lines...\n";
		while (!feof($hPrev)) {
			if (fgets($hPrev, 4096))
				$cntprev++;
		}
		echo "Num of lines of $fileoutput : $cntprev lines\n";
		$cntprev = $cntprev/2;
		echo "Counter in $fileoutput      : $cntprev \n";
		
		fclose($hPrev);
	}
	$cnt += $cntprev;

	$hIn = fopen($filedata, "r") or die('Unable to open input file');
	$hOut = fopen($fileoutput, 'a') or die('Unable to open file with writable permission');
	if ($hIn && $hOut) {
		$cnt++;
		echo "\n\nConverting each line...\n";
		// kd_sskel;thn_ang;kd_brg;kd_lokasi;tglbuku;kuantitas_lalu;total_lalu;mutasi_tambah;mutasi_krg;saldo_mutasi;kuantitas_akhir;total_akhir;ur_sskel;ur_brg;total_lalu_sskel;total_akhir_sskel
		while (!feof($hIn)) {
			// sleep(1);
			$newline = '';
			// $line = fgets($hIn, 4096);
			// $lineArray = explode(';', $line);
			$lineArray = fgetcsv($hIn, 4096, ';', '"', '"');
			if (!$lineArray || $lineArray[0] == 'kd_sskel' || !$lineArray[0]) {
				continue;
			}
			$olah = array(
				'kd_sskel' => $lineArray[0],
				'thn_ang' => $lineArray[1],
				'kd_brg' => $lineArray[2],
				'kd_lokasi' => $lineArray[3],
				'tglbuku' => $lineArray[4],
				'kuantitas_lalu' => $lineArray[5],
				'total_lalu' => $lineArray[6],
				'mutasi_tambah' => $lineArray[7],
				'mutasi_krg' => $lineArray[8],
				'saldo_mutasi' => $lineArray[9],
				'kuantitas_akhir' => $lineArray[10],
				'total_akhir' => $lineArray[11],
				'ur_sskel' => $lineArray[12],
				'ur_brg' => $lineArray[13],
				'total_lalu_sskel' => $lineArray[14],
				'total_akhir_sskel' => $lineArray[15]
			);
			if (!empty($parameter['lalu'])) {
				$rph_sat = $olah['total_lalu'] / $olah['kuantitas_lalu'];
			} else if ($olah['total_akhir'] > 0) {
				$rph_sat = $olah['total_akhir'] / $olah['kuantitas_akhir'];
			} else if ($olah['total_lalu'] > 0) {
				$rph_sat = $olah['total_lalu'] / $olah['kuantitas_lalu'];
			} else {
				$rph_sat = 0;
			}
			$flag_kirim = '';
			$olah['ur_brg'] = str_replace(array("\r\n","\n\r","\r"),"",$olah['ur_brg']);

			if (!empty($parameter['jenis']) && strtoupper($parameter['jenis']) == 'P01') {
				$nodok_m = $olah['kd_lokasi'] . $thn . str_pad($cnt, 5, '0', STR_PAD_LEFT) . 'P';
				$tgl_m = '31-' . str_pad($bln, 2, '0', STR_PAD_LEFT) . '-' . $thn . ' 23:59:59';
				$kd_brg = substr($olah['kd_brg'], -6);
				if (!empty($parameter['lalu'])) {
					$out_m = "|" . $olah['kd_lokasi'] . "|,|" . $olah['ur_brg'] . "|,|" . $thn . "|,|" . $nodok_m . "|," . $tgl_m . "," . $tgl_m . ",|" . $olah['kd_sskel'] . "|,|" . $kd_brg . "|,|" . $olah['kuantitas_lalu'] . "|,|" . $olah['ur_sskel'] . "|,|ALL GUDANG|,||,|" . 'P01' . "|,|" . $rph_sat . "|,|" . ($olah['kuantitas_lalu'] * $rph_sat) . "|,|" . $flag_kirim . "|\n";
				} else {
					$out_m = "|" . $olah['kd_lokasi'] . "|,|" . $olah['ur_brg'] . "|,|" . $thn . "|,|" . $nodok_m . "|," . $tgl_m . "," . $tgl_m . ",|" . $olah['kd_sskel'] . "|,|" . $kd_brg . "|,|" . $olah['kuantitas_akhir'] . "|,|" . $olah['ur_sskel'] . "|,|ALL GUDANG|,||,|" . 'P01' . "|,|" . $rph_sat . "|,|" . ($olah['kuantitas_akhir'] * $rph_sat) . "|,|" . $flag_kirim . "|\n";
				}
				
				fwrite($hOut, $out_m);
				echo "$out_m";
			} else if (!empty($parameter['jenis']) && strtoupper($parameter['jenis']) == 'M99') {
				$nodok_m = $olah['kd_lokasi'] . $thn . str_pad($cnt, 5, '0', STR_PAD_LEFT) . 'M';
				$tgl_m = '01-' . str_pad($bln, 2, '0', STR_PAD_LEFT) . '-' . $thn . ' 00:00:00';
				$kd_brg = substr($olah['kd_brg'], -6);
				$out_m = "|" . $olah['kd_lokasi'] . "|,|" . $olah['ur_brg'] . "|,|" . $thn . "|,|" . $nodok_m . "|," . $tgl_m . "," . $tgl_m . ",|" . $olah['kd_sskel'] . "|,|" . $kd_brg . "|,|" . $olah['kuantitas_lalu'] . "|,|" . $olah['ur_sskel'] . "|,|ALL DEPO|,||,|" . 'M99' . "|,|" . $rph_sat . "|,|" . ($olah['kuantitas_lalu'] * $rph_sat) . "|,|" . $flag_kirim . "|\n";
				fwrite($hOut, $out_m);
				echo "$out_m";
			} else {
				$nodok_m = $olah['kd_lokasi'] . $thn . str_pad($cnt, 5, '0', STR_PAD_LEFT) . 'M';
				$tgl_m = '02-' . str_pad($bln, 2, '0', STR_PAD_LEFT) . '-' . $thn . ' 00:00:01';
				$kd_brg = substr($olah['kd_brg'], -20);
				$out_m = "|" . $olah['kd_lokasi'] . "|,|" . $olah['ur_brg'] . "|,|" . $thn . "|,|" . $nodok_m . "|," . $tgl_m . "," . $tgl_m . ",|" . $olah['kd_sskel'] . "|,|" . $kd_brg . "|,|" . $olah['mutasi_tambah'] . "|,|" . $olah['ur_sskel'] . "|,||,||,|" . 'M02' . "|,|" . $rph_sat . "|,|" . ($olah['mutasi_tambah'] * $rph_sat) . "|,|" . $flag_kirim . "|\n";
				fwrite($hOut, $out_m);
				echo "$out_m";

				$nodok_k = $olah['kd_lokasi'] . $thn . str_pad($cnt, 5, '0', STR_PAD_LEFT) . 'K';
				$tgl_k = '03-' . str_pad($bln, 2, '0', STR_PAD_LEFT) . '-' . $thn . ' 00:00:01';
				$kd_brg = substr($olah['kd_brg'], -20);
				$out_k = "|" . $olah['kd_lokasi'] . "|,|" . $olah['ur_brg'] . "|,|" . $thn . "|,|" . $nodok_k . "|," . $tgl_k . "," . $tgl_k . ",|" . $olah['kd_sskel'] . "|,|" . $kd_brg . "|,|" . $olah['mutasi_krg'] . "|,|" . $olah['ur_sskel'] . "|,||,||,|" . 'K01' . "|,|" . $rph_sat . "|,|" . ($olah['mutasi_krg'] * $rph_sat) . "|,|" . $flag_kirim . "|\n";
				fwrite($hOut, $out_k);
				echo "$out_k";
			}

			$cnt++;
		}
		fclose($hOut);
		fclose($hIn);
	}
	echo "\n\nAll data are succesfully processed!\n";
	echo "New Converted Data are appended to '$fileoutput'";
} else {
	die('You must specify data source csv file');
}