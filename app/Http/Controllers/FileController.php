<?php

namespace App\Http\Controllers;

use Smalot\PdfParser\Parser;
use Smalot\PdfParser\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\File;

class FileController extends Controller
{
    public function store(Request $request){

        $file = $request->file;

        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx',
        ]);

        $filename = time()."_".$file->getClientOriginalName();

        $latest_file = File::latest()->first();

        if ($file->getClientOriginalExtension() == 'pdf') {

            $config = new Config();
            $config->setHorizontalOffset('');
            $pdfParser = new Parser([], $config);
            $pdf = $pdfParser->parseFile($file->path());
            $content = $pdf->getText();

            if ($content!="") {
                $v_content = explode("\n", $content);
                $chunked_content = array_chunk($v_content,20);
                
                foreach ($chunked_content as $chunks) {
                    $upload_file = new File;

                    array_walk($chunks,function($value){
                        $value = trim($value);
                    });

                    if ($latest_file) {
                        $upload_file->file_id = $latest_file->file_id + 1;
                    } else {                
                        $upload_file->file_id = 1;
                    }
        
                    $upload_file->filename = $filename;
                    $upload_file->mime_type = $file->getMimeType();
                    $upload_file->filesize = $file->getSize();
                    $upload_file->content = implode("", $chunks);
                    $upload_file->save();                      
                }
    
                $file->move("uploads",$filename);
            }
            else {
                return response()->json(['status' => 0, 'message' => "This file cannot be used."]);
            }
        }
        else {
        
            $mime_type = $file->getMimeType();
            $filesize = $file->getSize();

            $file->move("uploads",$filename);
            $uploaded_file = public_path('uploads\\'.$filename);

            $striped_content = '';
            $content = '';
    
            $zip = zip_open($uploaded_file);
    
            if (!$zip || is_numeric($zip)) return false;
    
            while ($zip_entry = zip_read($zip)) {
    
                if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
    
                if (zip_entry_name($zip_entry) != "word/document.xml") continue;
    
                $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
    
                zip_entry_close($zip_entry);
            }
    
            zip_close($zip);
    
            $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
            $content = str_replace('</w:r></w:p>', "\r\n", $content);
            $content = strip_tags($content);
            
            if ($content!="") {
                $v_content = explode("\n", $content);
                $chunked_content = array_chunk($v_content,20);
                
                foreach ($chunked_content as $chunks) {
                    $store_file = new File;

                    array_walk($chunks,function($value){
                        $value = trim($value);
                    });

                    if ($latest_file) {
                        $store_file->file_id = $latest_file->file_id + 1;
                    } else {                
                        $store_file->file_id = 1;
                    }
        
                    $store_file->filename = $filename;
                    $store_file->mime_type = ".docx";
                    $store_file->filesize = $filesize;
                    $store_file->content = implode("", $chunks);
                    $store_file->save();                      
                }
            }
            else {
                return response()->json(['status' => 0, 'message' => "This file cannot be used."]);
            }
        }
        
        return response()->json(['status' => 1, 'message' => 'File stored successfully.']);

    }

    public function search(Request $request){
        $result = File::whereRaw('MATCH (content) AGAINST (?)' , array([$request->term]))
            ->where('file_id',File::max('file_id'))
            ->get('content');

        if (!count($result)) {
            $result = File::where('content','like','%'.$request->term.'%')
                ->where('file_id',File::max('file_id'))
                ->get('content');
        }        
        if (!count($result)) {
            return response()->json(['status'=> 0, 'data'=> [], 'message'=>'No results found']);
        }
        return response()->json(['status'=> 1, 'data'=> $result, 'message'=>'Results found']);
        
    }
}