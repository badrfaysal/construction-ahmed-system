<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\Project;
use App\Models\WarrantyComplaint;

class AlertController extends Controller
{
    // Aggregated monitoring screen — things that need attention today
    public function index()
    {
        // Installments past their due date and still not paid
        $overdueInstallments = Installment::with('project')
            ->where('status', '!=', 'paid')
            ->where('due_date', '<', today())
            ->orderBy('due_date')
            ->get();

        // Installments due within the next 7 days (early warning)
        $upcomingInstallments = Installment::with('project')
            ->where('status', '!=', 'paid')
            ->whereBetween('due_date', [today(), today()->addDays(7)])
            ->orderBy('due_date')
            ->get();

        // Warranties expiring within the next 30 days
        $expiringWarranties = Project::with(['client', 'warranty'])
            ->whereHas('warranty')
            ->get()
            ->filter(function ($project) {
                $w = $project->warranty;
                return $w->isActive() && $w->expiresAt()->lessThanOrEqualTo(today()->addDays(30));
            });

        // Warranty complaints still not resolved
        $openComplaints = WarrantyComplaint::with('warranty.project')
            ->where('status', '!=', 'resolved')
            ->orderBy('date')
            ->get();

        // Active projects with no money movement in the last 30 days
        $staleProjects = Project::where('status', 'active')
            ->whereDoesntHave('transactions', function ($q) {
                $q->where('date', '>=', today()->subDays(30));
            })
            ->get();

        return view('alerts.index', compact(
            'overdueInstallments', 'upcomingInstallments',
            'expiringWarranties', 'openComplaints', 'staleProjects'
        ));
    }
}
