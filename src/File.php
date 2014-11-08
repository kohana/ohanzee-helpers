<?php

namespace Ohanzee\Helper;

class File
{
    /**
     * Split a file into pieces matching a specific size. Used when you need to
     * split large files into smaller pieces for easy transmission.
     *
     *     $count = File::split($file);
     *
     * @param   string  $filename   file to be split
     * @param   integer $piece_size size, in MB, for each piece to be
     * @return  integer The number of pieces that were created
     */
    public static function split($filename, $piece_size = 10)
    {
        // Open the input file
        $file = fopen($filename, 'rb');

        // Change the piece size to bytes
        $piece_size = floor($piece_size * 1024 * 1024);

        // Write files in 8k blocks
        $block_size = 1024 * 8;

        // Total number of pieces
        $pieces = 0;

        while (!feof($file)) {
            // Create another piece
            $pieces += 1;

            // Create a new file piece
            $piece = str_pad($pieces, 3, '0', STR_PAD_LEFT);
            $piece = fopen($filename.'.'.$piece, 'wb+');

            // Number of bytes read
            $read = 0;

            do {
                // Transfer the data in blocks
                fwrite($piece, fread($file, $block_size));

                // Another block has been read
                $read += $block_size;
            } while ($read < $piece_size);

            // Close the piece
            fclose($piece);
        }

        // Close the file
        fclose($file);

        return $pieces;
    }

    /**
     * Join a split file into a whole file. Does the reverse of [File::split].
     *
     *     $count = File::join($file);
     *
     * @param   string  $filename   split filename, without .000 extension
     * @return  integer The number of pieces that were joined.
     */
    public static function join($filename)
    {
        // Open the file
        $file = fopen($filename, 'wb+');

        // Read files in 8k blocks
        $block_size = 1024 * 8;

        // Total number of pieces
        $pieces = 0;

        while (is_file($piece = $filename.'.'.str_pad($pieces + 1, 3, '0', STR_PAD_LEFT))) {
            // Read another piece
            $pieces += 1;

            // Open the piece for reading
            $piece = fopen($piece, 'rb');

            while (!feof($piece)) {
                // Transfer the data in blocks
                fwrite($file, fread($piece, $block_size));
            }

            // Close the piece
            fclose($piece);
        }

        return $pieces;
    }
}
