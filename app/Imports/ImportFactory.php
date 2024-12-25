<?php

namespace App\Imports;

use Exception;

class ImportFactory
{
    /**
     * Factory class that creates an Import class object based on file key.
     *
     * @param string $fileKey
     * @return object
     * @throws Exception
     */
    public static function create(string $fileKey)
    {
        switch ($fileKey) {
            case 'standard_order':
                return new StandardOrderImport();
            case 'basic_invoice':
                return new BasicInvoiceImport();
            case 'tax_invoice':
                return new TaxInvoiceImport();
            default:
                throw new Exception("Unsupported import type: $fileKey");
        }
    }
}
