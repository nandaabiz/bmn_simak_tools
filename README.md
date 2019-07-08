# BMN-SIMAK Tools
A simple tools for helping generate text file which is formatted as Aplikasi Persediaan Importer.

###### Tools:
1. String Replacer
USAGE: http://<your_host>/replacer.php?needles=list.txt&data=source.txt

PARAMETERS:
@needles  a list of string with format "to be replaced;replace with" ended by newline placed in same folder as replacer.php
@data     a bunch of string which has string to be replaced ended by newline placed in same folder as replacer.php

OUTPUT:
A file named "replaced_data.txt" will be created in same folder as replacer.php



2. BMN-SIMAK Bridging-Converter
USAGE: http://<your_host>/bridge.php?source=src.csv&thn=2017&satker=PLKP&bln=03&counter=500

PARAMETERS:
@source   a csv file generated from c_nilai_report.xls (output by Aplikasi Persediaan) placed in same folder as replacer.php
@thn      year of document
@bln      month of document
@satker   name of Satuan Kerja, will be used as output filename
@counter  start of counter (last counter of previous file), usually used when month is not January

OUTPUT:
A file named "TXT_<SATKER>.txt" will be created in same folder as bridge.php
