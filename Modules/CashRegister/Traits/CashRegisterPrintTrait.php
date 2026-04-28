<?php

namespace Modules\CashRegister\Traits;

use Exception;
use App\Models\PrintJob;
use App\Models\Printer;
use App\Events\PrintJobCreated;
use Modules\CashRegister\Models\CashRegisterSession;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helper\Files;

trait CashRegisterPrintTrait
{
    protected $imageFilename = null;
    protected $printerSetting;

    /**
     * Get active printer for cash register reports
     */
    private function getActivePrinter()
    {
        return Printer::where('is_active', 1)
            ->where('restaurant_id', restaurant()->id)
            ->first();
    }

    /**
     * Get print width in mm
     */
    private function getPrintWidth($printerSetting = null)
    {
        return match ($printerSetting?->print_format ?? 'thermal80mm') {
            'thermal56mm' => 56,
            'thermal112mm' => 112,
            default => 80,
        };
    }

    /**
     * Print X-Report
     */
    public function printXReport($sessionId, $reportData)
    {

        $this->printerSetting = $this->getActivePrinter();

        $width = $this->getPrintWidth($this->printerSetting);
        $thermal = true;
        $content = view('cashregister::print.x-report', compact('reportData', 'width', 'thermal'))->render();

        if ($this->checkGeneratePdf()) {
            $this->generateXReportPdf($sessionId, $content);
        } else {
            // Always generate image first (same-as main project's flow)
            $this->generateXReportImage($sessionId, $content);
        }

        // Then create the print job record
        $this->executeXReportPrint($sessionId, $reportData);
    }

    private function generateXReportImage($sessionId, $content)
    {
        try {
            // Small delay to avoid race conditions
            usleep(200000); // 200ms



            $this->dispatch('saveReportImageFromPrint', $sessionId, $content, 'x-report');
        } catch (Exception $e) {
        }
    }

    private function executeXReportPrint($sessionId, $reportData)
    {

        if ($this->checkGeneratePdf()) {
            $this->imageFilename = 'x-report-' . $sessionId . '.pdf';
        } else {
            $this->imageFilename = 'x-report-' . $sessionId . '.png';
        }

        $branchId = $reportData['session']->branch_id ?? null;

        $this->createReportPrintJob($branchId);
        $this->alert('success', 'X-Report sent to printer successfully.');
    }

    /**
     * Print Z-Report
     */
    public function printZReport($sessionId, $reportData)
    {
        $this->printerSetting = $this->getActivePrinter();

        $thermal = true;

        // Get denominations for this session
        $denominations = \Modules\CashRegister\Entities\CashRegisterCount::with('denomination')
            ->where('cash_register_session_id', $sessionId)
            ->where('count', '>', 0)
            ->get();

        $content = view('cashregister::print.z-report', compact('reportData', 'width', 'thermal', 'denominations'))->render();

        if ($this->checkGeneratePdf()) {
            $this->generateZReportPdf($sessionId, $content);
        } else {
            $this->generateZReportImage($sessionId, $content);
        }


        // Then create the print job record
        $this->executeZReportPrint($sessionId, $reportData);
    }

    private function generateZReportImage($sessionId, $content)
    {
        try {
            // Small delay to avoid race conditions
            usleep(200000); // 200ms

            $width = $this->getPrintWidth($this->printerSetting);


            $this->dispatch('saveReportImageFromPrint', $sessionId, $content, 'z-report');
        } catch (Exception $e) {
        }
    }

    private function executeZReportPrint($sessionId, $reportData)
    {
        if ($this->checkGeneratePdf()) {
            $this->imageFilename = 'z-report-' . $sessionId . '.pdf';
        } else {
            $this->imageFilename = 'z-report-' . $sessionId . '.png';
        }

        $branchId = $reportData['session']->branch_id ?? null;

        $this->createReportPrintJob($branchId);
        $this->alert('success', 'Z-Report sent to printer successfully.');
    }

    /**
     * Create print job record for cash register reports (same-as main project flow)
     */
    private function createReportPrintJob($branchId = null)
    {
        $printJob = PrintJob::create([
            'image_filename' => $this->imageFilename,
            'restaurant_id' => restaurant()->id,
            'branch_id' => $branchId,
            'status' => 'pending',
            'printer_id' => $this->printerSetting->id ?? null,
        ]);

        // Dispatch event for print job creation
        event(new PrintJobCreated($printJob));

        return $printJob;
    }


    private function generateXReportPdf($sessionId, $content)
    {
        $width = $this->getPrintWidth($this->printerSetting);
        $paperWidthInPoints = $width * 2.85;
        $paperHeightInPoints = 800;

        $pdf = Pdf::loadHTML($content)->setPaper([0, 0, $paperWidthInPoints, $paperHeightInPoints], 'portrait');
        $fullPath = public_path(Files::UPLOAD_FOLDER . '/' . 'print/x-report-' . $sessionId . '.pdf');
        $pdf->save($fullPath);
    }

    private function generateZReportPdf($sessionId, $content)
    {
        $width = $this->getPrintWidth($this->printerSetting);
        $paperWidthInPoints = $width * 2.85;
        $paperHeightInPoints = 800;

        $pdf = Pdf::loadHTML($content)->setPaper([0, 0, $paperWidthInPoints, $paperHeightInPoints], 'portrait');
        $fullPath = public_path(Files::UPLOAD_FOLDER . '/' . 'print/z-report-' . $sessionId . '.pdf');
        $pdf->save($fullPath);
    }

    public function ifMobileDevice()
    {
        $isMobile = false;

        if (request()->header('User-Agent')) {
            $agent = strtolower(request()->header('User-Agent'));
            $isMobile = preg_match('/mobile|android|iphone|ipad|phone/i', $agent);
        }

        return $isMobile ?? false;
    }

    public function ifDesktopDevice()
    {
        return !$this->ifMobileDevice();
    }

    private function checkGeneratePdf()
    {
        return ($this->printerSetting->print_type == 'pdf' || $this->ifMobileDevice());
    }
}
