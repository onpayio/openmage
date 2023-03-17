<?php
/**
 * MIT License
 *
 * Copyright (c) 2023 OnPay.io
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

    function setFileMap($mapFile, $mapPath) {
        $currentFileMap = scanRecursiveDir($mapPath);
        $lines = '';
        foreach($currentFileMap as $key => $mapLine) {
            $line = '';
            if ($key > 0) {
                $line .= PHP_EOL;
            }
            $line .= $mapLine;
            $lines .= $line;
        }
        file_put_contents($mapFile, $lines);
    }

    function checkFileMapValid($mapFile, $mapPath) {
        $mapFileContent = file_get_contents($mapFile);
        $storedFileMap = explode(PHP_EOL, $mapFileContent);
        $currentFileMap = scanRecursiveDir($mapPath);

        $mapExtra = array_diff($storedFileMap, $currentFileMap);
        $fileExtra = array_diff($currentFileMap, $storedFileMap);

        return [
            'map' => $mapExtra,
            'file' => $fileExtra,
        ];
    }

    function scanRecursiveDir($dir) {
        $result = [];
        foreach(scandir($dir) as $filename) {
            if ($filename[0] === '.') continue;
            $filePath = $dir . '/' . $filename;
            if (is_dir($filePath)) {
                foreach (scanRecursiveDir($filePath) as $childFilename) {
                    $result[] = $filename . '/' . $childFilename;
                }
            } else {
                $result[] = $filename;
            }
        }
        return $result;
    }

    $mapFile = './map';
    $mapperPath = './';

    $arguments = [];
    for($i = 1; $i < $argc; $i++) {
        $explode = explode('=', $argv[$i]);
        if (count($explode) === 1) {
            $arguments[$explode[0]] = true;
        } else if (count($explode) === 2) {
            $arguments[$explode[0]] = $explode[1];
        }
    }

    if (array_key_exists('path', $arguments)) {
        if (is_dir($arguments['path'])) {
            $mapperPath = $arguments['path'];
        } else {
            die('No such path');
        }
    }

    if (array_key_exists('set', $arguments)) {
        setFileMap($mapFile, $mapperPath);
        die('Map set');
    }

    if (!is_file($mapFile)) {
        die('No map file');
    }

    $mapCheck = checkFileMapValid($mapFile, $mapperPath);
    if (!empty($mapCheck['map']) || !empty($mapCheck['file'])) {
        echo 'Filesystem not according to map' . PHP_EOL;

        if (!empty($mapCheck['map'])) {
            echo '-------------------------------' . PHP_EOL;
            echo 'Mapped but not present in filesystem:' . PHP_EOL;
            foreach ($mapCheck['map'] as $key => $line) {
                echo '- ' . $line . PHP_EOL;
            }
        }

        if (!empty($mapCheck['file'])) {
            echo '-------------------------------' . PHP_EOL;
            echo 'Present in filesystem but not mapped:' . PHP_EOL;
            foreach ($mapCheck['file'] as $key => $line) {
                echo '- ' . $line . PHP_EOL;
            }
        }
        die;
    }
