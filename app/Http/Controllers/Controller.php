<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController; // Gunakan alias untuk menghindari konflik nama

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests; // DispatchesJobs tidak selalu diperlukan, tapi ini yang utama
}
