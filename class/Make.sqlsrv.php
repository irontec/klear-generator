<?php
require_once 'Make.mssql.php';

class Make_sqlsqrv extends Make_mssql {

    protected function getAdapterType()
    {
        return 'Sqlsrv';
    }
}