<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StudentController extends AbstractController {
    /**
     * @Route("/student/uploads", name="uploads_file")
     */
    public function upload(Request $request) {
        $target_dir = __DIR__ . '/uploads/';
        $file = $request->files->get('fileToUpload');
        $uploadOk = 1;
        $csvFileType = strtolower($file->getClientOriginalExtension());

        if ($request->isMethod('POST')) {
            $check = mime_content_type($file->getPathname());
            if ($check === 'text/csv' || $check === 'application/vnd.ms-excel') {
                echo "File is a CSV - " . $check . ".";
                $uploadOk = 1;
            } else {
                echo "File is not a CSV.";
                $uploadOk = 0;
            }
        }

        if ($file->getSize() > 200000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        if ($uploadOk === 1) {
            $target_file = $target_dir . basename($file->getClientOriginalName());
            $file->move($target_dir, $target_file);
            echo "The file " . basename($file->getClientOriginalName()) . " has been uploaded.";
        }
    }
}
