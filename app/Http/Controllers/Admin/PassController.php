<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GenericPassService;
use Illuminate\Http\Request;

class PassController extends Controller
{
    protected GenericPassService $genericPassService;

    public function __construct(GenericPassService $genericPassService)
    {
        $this->genericPassService = $genericPassService;
    }

    /**
     * Display the Pass Gallery.
     */
    public function index()
    {
        $passes = $this->genericPassService->getAllActivePasses();
        
        return view('admin.passes.index', [
            'passes' => $passes
        ]);
    }
}
