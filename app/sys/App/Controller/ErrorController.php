<?php declare(strict_types=1);

namespace App\Controller;
use Core\Controller;

class ErrorController extends Controller
{

    protected function actionAjax()
    {
        header('Content-Type: application/ajax');
        echo json_encode(['error' => 'page not found']);
    }

}