<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // List all clients with their project counts
    public function index()
    {
        $clients = Client::withCount('projects')
            ->with('projects')
            ->orderBy('name')
            ->get();

        return view('clients.index', compact('clients'));
    }

    // Show one client and all their projects
    public function show(Client $client)
    {
        $client->load(['projects.bands', 'projects.installments']);
        return view('clients.show', compact('client'));
    }

    // Show create form
    public function create()
    {
        return view('clients.create');
    }

    // Save new client
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes'   => ['nullable', 'string'],
        ]);

        $client = Client::create($data);

        return redirect()->route('clients.show', $client)
            ->with('success', 'تم إضافة العميل بنجاح.');
    }

    // Show edit form
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    // Save edits
    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:30'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'notes'   => ['nullable', 'string'],
        ]);

        $client->update($data);

        return redirect()->route('clients.show', $client)
            ->with('success', 'تم تحديث بيانات العميل.');
    }
}
