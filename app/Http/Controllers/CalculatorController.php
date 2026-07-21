<?php

namespace App\Http\Controllers;

use App\Models\Calculator;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function index()
    {
        $history = Calculator::orderByDesc('created_at')->get();
        return view('calculator.index', compact('history'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'                => 'nullable|string|max:255',
            'work_type'            => 'required|string',
            'global_height'        => 'required|numeric|min:0',
            'spaces'               => 'required|json',
            'total_paints'         => 'required|numeric',
            'total_floor_ceramics' => 'required|numeric',
            'total_wall_ceramics'  => 'required|numeric',
            'total_deductions'     => 'required|numeric',
        ]);

        if (empty($data['title'])) {
            $data['title'] = 'حسبة مقايسة - ' . now()->format('Y/m/d H:i');
        }

        $data['spaces'] = json_decode($data['spaces'], true);

        Calculator::create($data);

        return redirect()->route('calculator.index')->with('success', 'تم حفظ المقايسة في السجل بنجاح.');
    }

    public function destroy(Calculator $calculator)
    {
        $calculator->delete();
        return back()->with('success', 'تم حذف المقايسة من السجل.');
    }
}
