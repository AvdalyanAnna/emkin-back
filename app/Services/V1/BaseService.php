<?php

namespace App\Services\V1;

use App\Exports\GlobalExport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Facades\Excel;
use Prettus\Repository\Contracts\RepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class BaseService
 * @package App\Services
 */
abstract class BaseService {

    /**
     * @var bool
     */
    private bool $exportData = false;

   /**
    * @return RepositoryInterface
    */
   abstract public function getRepo(): RepositoryInterface;

   /**
    * @return Model
    */
   abstract public function model(): Model;

    /**
     * @param bool $exportData
     * @return static
     */
    public function setExportData(bool $exportData): static {
        $this->exportData = $exportData;
        return $this;
    }

    /**
     * @return bool
     */
    public function isExportData(): bool
    {
        return $this->exportData;
    }

    /**
     * @param $collection
     * @param array $headings
     * @param \Closure $callback
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @return Collection|BinaryFileResponse
     */
    public function exportData($collection, array $headings, \Closure $callback): Collection|BinaryFileResponse {
        return $this->isExportData()
            ? Excel::download(
                (new GlobalExport)
                    ->setHeading($headings)
                    ->setCollection($collection, $callback),
                'GlobalExport.xlsx'
            )
            : $collection;
    }
}
