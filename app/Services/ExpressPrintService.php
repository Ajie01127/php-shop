<?php

namespace App\Services;

use App\Models\ExpressOrder;
use App\Models\ExpressPrintConfig;
use App\Services\SfExpressService;
use Exception;

/**
 * 电子面单打印服务
 * 支持多种打印方式：本地打印机、PDF导出、图片打印
 */
class ExpressPrintService
{
    private $sfExpressService;
    private $printConfig;
    
    // 打印模式常量
    const PRINT_LOCAL = 'local';
    const PRINT_PDF = 'pdf';
    const PRINT_IMAGE = 'image';
    const PRINT_PREVIEW = 'preview';
    
    // 面单模板尺寸
    const TEMPLATE_SIZE_100x150 = [100, 150];
    const TEMPLATE_SIZE_100x100 = [100, 100];
    const TEMPLATE_SIZE_76x130 = [76, 130];
    
    public function __construct()
    {
        $this->sfExpressService = new SfExpressService();
        $this->loadPrintConfig();
    }
    
    /**
     * 加载打印配置
     */
    private function loadPrintConfig()
    {
        // 从数据库加载配置
        $configModel = new ExpressPrintConfig();
        $dbConfig = $configModel->getConfig();
        
        // 解析模板尺寸
        $templateSize = $dbConfig['template_size'] ?? '100,150';
        $sizeParts = explode(',', $templateSize);
        $templateSizeArray = [
            intval($sizeParts[0] ?? 100),
            intval($sizeParts[1] ?? 150)
        ];
        
        $this->printConfig = [
            'print_mode' => $dbConfig['print_mode'] ?? self::PRINT_PREVIEW,
            'printer_name' => $dbConfig['printer_name'] ?? null,
            'template_size' => $templateSizeArray,
            'template_size_string' => $templateSize,
            'auto_print' => $dbConfig['auto_print'] ?? 0,
            'print_copies' => $dbConfig['print_copies'] ?? 1,
            'save_pdf' => $dbConfig['save_pdf'] ?? 1,
            'pdf_path' => $dbConfig['pdf_path'] ?? __DIR__ . '/../../storage/express/pdf/',
            'print_interval' => $dbConfig['print_interval'] ?? 1,
            'max_batch_size' => $dbConfig['max_batch_size'] ?? 50,
            'enable_barcode' => $dbConfig['enable_barcode'] ?? 1,
            'enable_qrcode' => $dbConfig['enable_qrcode'] ?? 0
        ];
    }
    
    /**
     * 打印电子面单
     */
    public function printWaybill($orderId, $options = [])
    {
        try {
            $order = ExpressOrder::find($orderId);
            if (!$order) {
                throw new Exception('快递订单不存在');
            }
            
            if (empty($order->waybill_no)) {
                throw new Exception('该订单还没有获取电子面单');
            }
            
            $waybillData = $this->getWaybillData($order);
            if (!$waybillData) {
                throw new Exception('获取电子面单数据失败');
            }
            
            $printMode = $options['print_mode'] ?? $this->printConfig['print_mode'];
            
            switch ($printMode) {
                case self::PRINT_LOCAL:
                    return $this->printToLocal($waybillData, $options);
                case self::PRINT_PDF:
                    return $this->exportToPDF($waybillData, $options);
                case self::PRINT_IMAGE:
                    return $this->exportToImage($waybillData, $options);
                case self::PRINT_PREVIEW:
                    return $this->previewWaybill($waybillData, $options);
                default:
                    throw new Exception('不支持的打印模式');
            }
        } catch (Exception $e) {
            error_log('打印电子面单失败: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * 获取电子面单数据
     */
    private function getWaybillData($order)
    {
        try {
            if ($order->waybill_data) {
                return json_decode($order->waybill_data, true);
            }
            
            $result = $this->sfExpressService->getWaybill([
                'waybill_no' => $order->waybill_no,
                'order_no' => $order->order_no
            ]);
            
            if ($result && isset($result['waybill_data'])) {
                $order->waybill_data = json_encode($result['waybill_data']);
                $order->save();
                return $result['waybill_data'];
            }
            
            return null;
        } catch (Exception $e) {
            error_log('获取电子面单数据失败: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * 打印到本地打印机
     */
    private function printToLocal($waybillData, $options)
    {
        try {
            $html = $this->generateWaybillHtml($waybillData);
            $printerName = $options['printer_name'] ?? $this->printConfig['printer_name'];
            $copies = $options['copies'] ?? $this->printConfig['print_copies'];
            
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $result = $this->printOnWindows($html, $printerName, $copies);
            } else {
                $result = $this->printOnLinux($html, $printerName, $copies);
            }
            
            return [
                'success' => $result,
                'message' => $result ? '打印成功' : '打印失败',
                'data' => [
                    'printer' => $printerName ?: '默认打印机',
                    'copies' => $copies
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '本地打印失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Windows系统打印
     */
    private function printOnWindows($html, $printerName, $copies)
    {
        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'waybill_') . '.html';
            file_put_contents($tempFile, $html);
            
            for ($i = 0; $i < $copies; $i++) {
                exec("start /min \"\" \"$tempFile\"");
                sleep(1);
            }
            
            unlink($tempFile);
            return true;
        } catch (Exception $e) {
            error_log('Windows打印失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Linux/Mac系统打印
     */
    private function printOnLinux($html, $printerName, $copies)
    {
        try {
            $pdfFile = $this->htmlToPDF($html);
            
            if ($pdfFile && file_exists($pdfFile)) {
                $printerOption = $printerName ? "-P $printerName" : '';
                $command = sprintf("lpr %s -# %d %s", $printerOption, $copies, escapeshellarg($pdfFile));
                exec($command, $output, $returnCode);
                
                unlink($pdfFile);
                return $returnCode === 0;
            }
            
            return false;
        } catch (Exception $e) {
            error_log('Linux/Mac打印失败: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * 导出为PDF
     */
    private function exportToPDF($waybillData, $options)
    {
        try {
            $html = $this->generateWaybillHtml($waybillData);
            $pdfFile = $this->htmlToPDF($html);
            
            if ($pdfFile && file_exists($pdfFile)) {
                $savePath = $this->printConfig['pdf_path'];
                if (!is_dir($savePath)) {
                    mkdir($savePath, 0755, true);
                }
                
                $filename = 'waybill_' . $waybillData['waybill_no'] . '_' . date('YmdHis') . '.pdf';
                $destPath = $savePath . $filename;
                
                if (copy($pdfFile, $destPath)) {
                    unlink($pdfFile);
                    return [
                        'success' => true,
                        'message' => 'PDF导出成功',
                        'data' => [
                            'file_path' => $destPath,
                            'file_name' => $filename,
                            'file_url' => '/storage/express/pdf/' . $filename
                        ]
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'PDF导出失败'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'PDF导出失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 导出为图片
     */
    private function exportToImage($waybillData, $options)
    {
        try {
            $html = $this->generateWaybillHtml($waybillData);
            $imagePath = $this->htmlToImage($html);
            
            if ($imagePath && file_exists($imagePath)) {
                return [
                    'success' => true,
                    'message' => '图片导出成功',
                    'data' => [
                        'image_path' => $imagePath,
                        'image_url' => str_replace(__DIR__ . '/../../', '/', $imagePath)
                    ]
                ];
            }
            
            return [
                'success' => false,
                'message' => '图片导出失败'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '图片导出失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 预览电子面单
     */
    private function previewWaybill($waybillData, $options)
    {
        try {
            $html = $this->generateWaybillHtml($waybillData);
            
            return [
                'success' => true,
                'message' => '预览生成成功',
                'data' => [
                    'html' => $html,
                    'waybill_no' => $waybillData['waybill_no']
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => '预览生成失败: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * 生成电子面单HTML
     */
    private function generateWaybillHtml($waybillData)
    {
        $size = $this->printConfig['template_size'];
        $width = $size[0];
        $height = $size[1];
        
        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>电子面单</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, "Microsoft YaHei", sans-serif; }
        .waybill {
            width: {$width}mm;
            height: {$height}mm;
            padding: 5mm;
            border: 1px solid #000;
        }
        .header { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 5mm; }
        .barcode { text-align: center; margin: 5mm 0; }
        .barcode img { max-width: 80%; height: 30mm; }
        .info { font-size: 12px; line-height: 1.5; }
        .info-row { display: flex; margin-bottom: 3mm; }
        .info-label { width: 30%; font-weight: bold; }
        .info-value { width: 70%; }
        .section { margin-bottom: 5mm; }
        .divider { border-top: 1px dashed #000; margin: 3mm 0; }
    </style>
</head>
<body>
    <div class="waybill">
        <div class="header">顺丰速运 电子面单</div>
        
        <div class="barcode">
            <img src="data:image/png;base64,{$waybillData['barcode_base64']}" alt="条形码">
            <div style="font-size: 14px; font-weight: bold; margin-top: 2mm;">{$waybillData['waybill_no']}</div>
        </div>
        
        <div class="divider"></div>
        
        <div class="section">
            <div class="info-row">
                <div class="info-label">寄件人：</div>
                <div class="info-value">{$waybillData['sender_name']}</div>
            </div>
            <div class="info-row">
                <div class="info-label">电话：</div>
                <div class="info-value">{$waybillData['sender_phone']}</div>
            </div>
            <div class="info-row">
                <div class="info-label">地址：</div>
                <div class="info-value">{$waybillData['sender_address']}</div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="section">
            <div class="info-row">
                <div class="info-label">收件人：</div>
                <div class="info-value">{$waybillData['receiver_name']}</div>
            </div>
            <div class="info-row">
                <div class="info-label">电话：</div>
                <div class="info-value">{$waybillData['receiver_phone']}</div>
            </div>
            <div class="info-row">
                <div class="info-label">地址：</div>
                <div class="info-value">{$waybillData['receiver_address']}</div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="section">
            <div class="info-row">
                <div class="info-label">快递类型：</div>
                <div class="info-value">{$waybillData['express_type']}</div>
            </div>
            <div class="info-row">
                <div class="info-label">重量：</div>
                <div class="info-value">{$waybillData['weight']} kg</div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
        
        return $html;
    }
    
    /**
     * HTML转PDF
     */
    private function htmlToPDF($html)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'waybill_') . '.pdf';
        
        // 这里需要使用PDF库，如wkhtmltopdf、dompdf等
        // 示例使用wkhtmltopdf命令行工具
        $htmlFile = tempnam(sys_get_temp_dir(), 'html_') . '.html';
        file_put_contents($htmlFile, $html);
        
        $command = sprintf(
            'wkhtmltopdf --page-width %dmm --page-height %dmm %s %s',
            $this->printConfig['template_size'][0],
            $this->printConfig['template_size'][1],
            escapeshellarg($htmlFile),
            escapeshellarg($tempFile)
        );
        
        exec($command, $output, $returnCode);
        unlink($htmlFile);
        
        if ($returnCode === 0 && file_exists($tempFile)) {
            return $tempFile;
        }
        
        return null;
    }
    
    /**
     * HTML转图片
     */
    private function htmlToImage($html)
    {
        $savePath = __DIR__ . '/../../storage/express/images/';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }
        
        $filename = 'waybill_' . time() . '_' . rand(1000, 9999) . '.png';
        $imagePath = $savePath . $filename;
        
        // 这里需要使用截图库，如wkhtmltoimage等
        $htmlFile = tempnam(sys_get_temp_dir(), 'html_') . '.html';
        file_put_contents($htmlFile, $html);
        
        $command = sprintf(
            'wkhtmltoimage --width %d --height %d %s %s',
            $this->printConfig['template_size'][0] * 4,
            $this->printConfig['template_size'][1] * 4,
            escapeshellarg($htmlFile),
            escapeshellarg($imagePath)
        );
        
        exec($command, $output, $returnCode);
        unlink($htmlFile);
        
        if ($returnCode === 0 && file_exists($imagePath)) {
            return $imagePath;
        }
        
        return null;
    }
    
    /**
     * 批量打印电子面单
     */
    public function batchPrint($orderIds, $options = [])
    {
        $results = [];
        $interval = $this->printConfig['print_interval'] ?? 1;
        
        foreach ($orderIds as $orderId) {
            $result = $this->printWaybill($orderId, $options);
            $results[] = [
                'order_id' => $orderId,
                'result' => $result
            ];
            
            // 使用配置的打印间隔
            if ($interval > 0) {
                sleep($interval);
            }
        }
        
        return $results;
    }
    
    /**
     * 获取可用打印机列表
     */
    public function getAvailablePrinters()
    {
        $printers = [];
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec('wmic printer get name', $output);
            $printers = array_filter(array_slice($output, 1), function($line) {
                return trim($line) !== '';
            });
        } else {
            exec('lpstat -p', $output);
            foreach ($output as $line) {
                if (preg_match('/^printer\s+(.+?)\s+/', $line, $matches)) {
                    $printers[] = $matches[1];
                }
            }
        }
        
        return array_values(array_map('trim', $printers));
    }
}
