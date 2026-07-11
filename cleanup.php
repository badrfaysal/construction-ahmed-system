<?php
$projects = \App\Models\Project::latest('id')->take(6)->get();
foreach ($projects as $p) {
    foreach($p->bands as $b) {
        foreach($p->materials as $m) {
            $m->returns()->delete();
            $m->delete();
        }
        $b->delete();
    }
    $p->transactions()->delete();
    $p->delete();
}
echo 'Deleted 6 dummy projects.';
