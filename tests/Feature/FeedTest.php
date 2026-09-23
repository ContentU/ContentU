<?php

use App\Models\Content;
use App\Models\Quarter;
use App\Models\User;

it('mostra tutti gli stati al team interno', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create();

    Content::factory()->for($quarter)->create(['status' => 'draft']);
    Content::factory()->for($quarter)->published()->create();

    $this->actingAs($admin)
        ->get("/quarters/{$quarter->id}/feed")
        ->assertInertia(fn ($page) => $page->has('contents', 2));
});

it('con il toggle solo pubblicati restano solo i pubblicati', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create();

    Content::factory()->for($quarter)->create(['status' => 'draft']);
    Content::factory()->for($quarter)->approved()->create();
    Content::factory()->for($quarter)->published()->create();

    $this->actingAs($admin)
        ->get("/quarters/{$quarter->id}/feed?view=published")
        ->assertInertia(fn ($page) => $page
            ->has('contents', 1)
            ->where('contents.0.status', 'published')
        );
});

it('ordina i contenuti cronologicamente', function () {
    $admin = User::factory()->admin()->create();
    $quarter = Quarter::factory()->create();

    $late = Content::factory()->for($quarter)->create(['publish_at' => now()->addDays(20)]);
    $early = Content::factory()->for($quarter)->create(['publish_at' => now()->addDays(2)]);

    $this->actingAs($admin)
        ->get("/quarters/{$quarter->id}/feed")
        ->assertInertia(fn ($page) => $page->where('contents.0.id', $early->id));
});
