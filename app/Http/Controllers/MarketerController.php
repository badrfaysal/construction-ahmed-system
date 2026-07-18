<?php

namespace App\Http\Controllers;

use App\Models\Marketer;
use Illuminate\Http\Request;

class MarketerController extends Controller
{
    public function index()
    {
        $marketers = Marketer::with('transactions')->get();
        return view('marketers.index', compact('marketers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        Marketer::create($data);

        return back()->with('success', 'تم إضافة المسوق بنجاح.');
    }

    public function destroy(Marketer $marketer)
    {
        if ($marketer->transactions()->exists()) {
            return back()->withErrors(['marketer' => 'لا يمكن حذف مسوق له عمولات مسجلة.']);
        }
        $marketer->delete();
        return back()->with('success', 'تم حذف المسوق بنجاح.');
    }
}
