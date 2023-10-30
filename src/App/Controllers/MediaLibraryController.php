<?php

namespace Src\App\Controllers;

use GTG\MVC\Controller;

class MediaLibraryController extends Controller
{
    public function load(array $data): void 
    {
        $data = array_merge($data, filter_input_array(INPUT_GET, FILTER_UNSAFE_RAW));
        $callback = [];

        $count = 0;
        $files = scandir($this->getStorageFolderRoot($data['root']));
        $limit = $data['limit'];
        $page = $data['page'];

        $files = $files ? array_map(
            function ($o) { return $o; }, 
            array_filter($files, function ($e) {
                return !in_array($e, ['.', '..']);
            })
        ) : [];
        
        if(isset($data['search'])) {
            $search = $data['search'];
            if($search !== '') {
                $files = array_filter($files, function ($e) use ($search) {
                    return strpos(strtolower(pathinfo($e, PATHINFO_FILENAME)), strtolower($search)) !== false;
                });
            }
        }

        if(count($files) < $limit * ($page - 1)) {
            $page = 1;
        }

        foreach($files as $file) {
            if($count >= $limit * ($page - 1) && $count < $limit * $page) {
                $callback['files'][] = $file;
            }
            $count++;
        }

        $callback['pages'] = ceil(count($files) / $limit);

        $callback['success'] = true;
        $this->APIResponse($callback, 200);
    }

    public function add(array $data): void
    {
        if(!isset($_FILES) || !isset($data['root'])) {
            $this->setMessage('error', _('Nenhum arquivo foi escolhido!'))->APIResponse([], 422);
            return;
        }

        $file = $_FILES['file'];
        $root = $this->getStorageFolderRoot($data['root']);

        if($file['name'] == 'blob') {
            $filename = $this->getImageCaptureFilename();
        } else {
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $basename = slugify(pathinfo($file['name'], PATHINFO_FILENAME));
    
            $files = scandir($root);
            while($files && in_array($basename . '.' . $extension, $files)) {
                $basename .= '-1';
            }
    
            $filename = $basename . '.' . $extension;
        }

        if(!is_dir($root)) {
            mkdir($root);
        }

        if(!move_uploaded_file($file['tmp_name'], $root . '/' . $filename)) {
            $this->setMessage('error', _('Lamentamos, mas parece que ocorreu um erro no upload do seu arquivo.') . $filename)->APIResponse([], 422);
            return;
        }

        $this->setMessage('success', _('O arquivo foi carregado com sucesso!'))->APIResponse(['filename' => $filename], 200);
    }

    public function delete(array $data): void 
    {
        if(!isset($data['name'])) {
            $this->setMessage('error', _('Nenhum nome de arquivo foi declarado!'))->APIResponse([], 422);
            return;
        }
        
        $files = glob($this->getStorageFolderRoot($data['root']) . '/' . $data["name"]);
        if(count($files) > 0) {
            foreach($files as $file) {
                if(is_file($file)) {
                    unlink($file);
                }
            }
        }

        $this->setMessage('success', _('O arquivo foi excluído com sucesso.'))->APIResponse([], 200);
    }

    private function getStorageFolderRoot(string $root): string 
    {
        return dirname(__FILE__, 4) . '/' . $root;
    }

    private function getImageCaptureFilename(): string 
    {
        return 'image-capture' . time() . '.png';
    }
}