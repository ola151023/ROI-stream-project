<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvestmentResource;
use App\Models\Investment;
use App\Models\WeeklyRoi;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WeeklyRoiController extends Controller
{

    public function index()
    {

        return WeeklyRoi::all();

    }

    public function store(Request $request){

    }

    public function show(WeeklyRoi $weeklyRoi)
    {

          $weeklyRoi = WeeklyRoi::findOrFail($weeklyRoi);
            return $weeklyRoi;


    }

}
