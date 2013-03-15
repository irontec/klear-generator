<?php
class Generator_Db_FakeAdapter
{
    protected $_filename;

    public function __construct($deltaPath)
    {
        $nextFileNumber = $this->_getNextFileNumber($deltaPath);
        $this->_filename = $deltaPath . DIRECTORY_SEPARATOR . sprintf('%03d-db-generator.sql', $nextFileNumber);
    }

    public function query($query)
    {
        file_put_contents($this->_filename, $query . ";\n", FILE_APPEND);
    }

    protected function _getNextFileNumber($deltaPath)
    {
        var_dump($deltaPath);
        $files = glob($deltaPath . DIRECTORY_SEPARATOR . '*.sql');
        $max = 0;
        var_dump($files);
        foreach ($files as $file) {
            $filename = basename($file);
            if (preg_match('/(\d+)\-*/', $filename, $matches)) {
                $current = (int)$matches[1];
                if ($current > $max) {
                    $max = $current;
                }
            }
        }



        return ++$max;
    }
}