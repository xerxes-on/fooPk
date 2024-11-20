<?php
/**
 * @copyright   Copyright © 2021 Lindenvalley GmbH (http://www.lindenvalley.de/)
 * @author      Mykola Mostovyi <nm@lindenvalley.de>
 * @date        20.02.2021
 */

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;

/**
 * PDF file format controller
 *
 * TODO: maybe make it just a service?
 *
 * @package App\Http\Controllers
 */
class PDFController extends Controller
{
    /**
     * Generate PDF file from HTML.
     */
    public function generatePdf(Request $request, ?string $customHtml = null): Response|ResponseFactory
    {
        $html     = is_null($customHtml) ? '<html>' . $request->html . '</html>' : $customHtml;
        $startKey = '<!--PDF_REMOVE_IT_START-->';
        $endKey   = '<!--PDF_REMOVE_IT_END-->';

        while (substr_count($html, $startKey)) {
            $html1 = substr($html, 0, strpos($html, $startKey));
            $html2 = substr(
                $html,
                strpos($html, $endKey) + strlen($endKey),
                strlen($html) - (strpos($html, $endKey) + strlen($endKey))
            );
            $html = $html1 . $html2;
        }

        $h1Value      = $this->getTextBetweenTags($html, 'h1');
        $snappy       = App::make('snappy.pdf');
        $tmpFileName  = 'pdf_export_' . date('Ymd_His') . '_' . uniqid() . '.pdf';
        $fullFilePath = public_path() . DIRECTORY_SEPARATOR . 'pdf_files' . DIRECTORY_SEPARATOR . $tmpFileName;
        $snappy->generateFromHtml($html, $fullFilePath);
        $fileContent   = File::get($fullFilePath);
        $mimeType      = File::mimeType($fullFilePath);
        $h1Value       = str_replace(' ', '_', $h1Value);
        $h1Value       = preg_replace('/[^a-zA-Z0-9_-]/', '', $h1Value);
        $h1Value       = trim($h1Value, '_');
        $fileName      = $h1Value . '.pdf';
        $file_contents = base64_encode($fileContent);
        unlink($fullFilePath);
        return response($file_contents)
            ->header('Cache-Control', 'no-cache private')
            ->header('Content-Description', 'File Transfer')
            ->header('Content-Type', $mimeType)
            ->header('Content-length', (string)strlen($file_contents))
            ->header('Content-Disposition', 'attachment; filename=' . $fileName)
            ->header('Content-Transfer-Encoding', 'binary');
    }

    private function getTextBetweenTags(string $string, string $tagname): string
    {
        $pattern = "/<$tagname ?.*>(.*)<\/$tagname>/";
        preg_match($pattern, $string, $matches);
        return !empty($matches[1]) ? $matches[1] : '';
    }
}
