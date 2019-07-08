<?php
/**
 * @author Nanda Widyatama <nanda7anku@gmail.com>
 *
 * This tool is used to replace any text each line
 * https://github.com/nandaabiz/bmn_simak_tools
 */
@ini_set('zlib.output_compression', 0);
@ini_set('implicit_flush', 1);
@ob_end_clean();
ob_implicit_flush(true);
ob_start();
echo "<pre>";
echo "Starting application ...\n";
ob_end_flush();
$parameter = $_GET;
if (!empty($parameter['needles'])) {
	$needles = file_get_contents($parameter['needles']);
	$needlesArr = explode("\n",str_replace(array("\r\n","\n\r","\r"),"\n",$needles));
	if (!empty($parameter['data'])) {
		$filedata = $parameter['data'];
		foreach ($needlesArr as $needle) {
			list($from,$to) = explode(';',$needle);
			$fromArray[] = $from;
			$toArray[] = $to;
			echo "Will replace '$from' ==> '$to'\n";
		}
		sleep(3);

		$fileoutput = 'replaced_data.txt';
		$hIn = fopen($filedata, "r") or die('Unable to open input file');
		$hOut = fopen($fileoutput, 'w') or die('Unable to open file with writable permission');
		if ($hIn && $hOut) {
			echo "\n\nReplacing each line...\n";
			$i = 1;
			while (!feof($hIn)) {
				$newline = '';
				$line = fgets($hIn, 4096);
				$newline = str_replace($fromArray,$toArray,$line);
				$newline = str_replace(array('|K08|','|K09|'),'|K01|',$newline);
				if (strpos($newline, 'M02') > 0 && $newline != $line) {
					echo "\nSkip line: \n";
					echo "$line\n";
					echo "vvvvv\n";
					echo "$newline\n";
				} else {
					fwrite($hOut, $newline);
				}
				// $newline = trim($newline);
				echo "=";
				$i++;
				if ($i > 100) {
					$i = 1;
					echo "\n";
				}
			}
			fclose($hOut);
			fclose($hIn);
		}
		echo "\n\nAll data are succesfully processed!\n";
		echo "New Replaced Data is saved as '$fileoutput'";
	} else {
		die('You must specified data text file');
	}
} else {
	die('You must specified needles text file');
}
