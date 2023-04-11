<?php

namespace App\Exports;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Excel;

/**
 * Class GlobalExport
 * @package App\Exports
 */
class GlobalExport implements FromCollection, Responsable, WithHeadings, ShouldAutoSize, WithEvents
{
    use Exportable;

    /**
     * @var Collection $collection
     */
    private $collection;

    /**
     * @var array
     */
    private $heading = [];

    /**
     * Optional Writer Type
     */
    private $writerType = Excel::XLSX;

    /**
     * Optional headers
     */
    private $headers = [
        'Content-Type' => 'text/xlsx',
    ];

    /**
     * Дата для екпорта
     * @param $collection
     * @param \Closure $closure
     * @return $this
     */
    public function setCollection($collection, \Closure $closure): GlobalExport
    {
        $this->collection = $collection->map($closure);
        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->collection;
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        return $this->heading;
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:D1';
                $event->sheet->getStyle($cellRange)->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);
            },
        ];
    }

    /**
     * @param array $heading
     * @return GlobalExport
     */
    public function setHeading(array $heading): GlobalExport
    {
        $this->heading = $heading;
        return $this;
    }
}
