<?php

use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

// Channel untuk update progress di ProjectDetailPage
Broadcast::channel('project.{id}', function ($user, $id) {
    // Untuk debug: izinkan semua user yang sudah login
    return true;
    
    // Nanti jika sudah lancar, ganti jadi:
    // return $user->id === \App\Models\Project::find($id)->user_id;
});

// Channel untuk list project di ProjectManagementPage
Broadcast::channel('projects', function ($user) {
    return true;
});