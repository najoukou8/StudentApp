<?php

namespace App\Service;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class UploadService
{
    private $target_dir;

    public function __construct( $target_dir)
    {
        $this->target_dir = $target_dir;
    }

    public function uploadFile( UploadedFile $file, $target_dir)
    {
        $uploadOk = 1;
        $fileName= md5(uniqid()) . '.' . $file->guessExtension();
        $target_file = $target_dir . $fileName;
    
        if($file) {
          $check = $file->getClientMimeType(); 
              if ($check === 'text/csv' || $check === 'application/pdf' || $check === 'application/msword' || $check ==='text/plain' | $check ==='application/vnd.openxmlformats-officedocument.wordprocessingml.document' ) {
                  $uploadOk = 1;
              } else {
                  $uploadOk = 0;
              }
          }
        
        if ($file->getSize() > 200000) {
          echo "le fichier est trop volumineux";
          $uploadOk = 0;
        }
        if ($uploadOk == 0) {
          echo "votre fichier n'a pas été téléchargé";
        } else {
          if ($file->move($target_dir, $fileName)) {
            echo "Le fichier ". htmlspecialchars( basename( $file->getClientOriginalName())). " a bien été ajouté.\n";
          } else {
            echo "Une erreur s'est produite lors du téléchargement de votre fichier.\n";
          }
        }
      }
              
    }
