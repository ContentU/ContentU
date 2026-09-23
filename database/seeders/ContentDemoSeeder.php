<?php

namespace Database\Seeders;

use App\Enums\AlertType;
use App\Models\Alert;
use App\Models\Client;
use App\Models\ClientPublicLink;
use App\Models\Content;
use App\Models\ContentType;
use App\Models\Quarter;
use App\Models\Tag;
use App\Models\TopicPreview;
use App\Models\TopicPreviewApproval;
use App\Models\TopicPreviewItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

/**
 * Dati di test per il piano editoriale (contenuti, tag, anteprima argomenti,
 * commenti, alert, link pubblico) sui clienti/trimestri creati da DemoSeeder.
 * Da eseguire solo in local/testing.
 */
class ContentDemoSeeder extends Seeder
{
    /** Stati coperti, in modo che ogni trimestre mostri l'intera pipeline. */
    private const STATUSES = ['draft', 'in_review', 'needs_changes', 'approved', 'scheduled', 'published', 'published'];

    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            $this->command->warn('ContentDemoSeeder saltato: disponibile solo in local/testing.');

            return;
        }

        $contentTypes = ContentType::all();

        if ($contentTypes->isEmpty()) {
            $this->command->warn('ContentDemoSeeder saltato: esegui prima ContentTypeSeeder.');

            return;
        }

        Client::with('quarters', 'users')->get()->each(function (Client $client) use ($contentTypes) {
            $tags = Tag::factory()->count(4)->create(['client_id' => $client->id]);

            $client->quarters->each(function (Quarter $quarter) use ($client, $contentTypes, $tags) {
                $this->seedContents($client, $quarter, $contentTypes, $tags);
                $this->seedTopicPreviews($client, $quarter, $contentTypes);
            });

            if ($client->publicLinks()->doesntExist()) {
                ClientPublicLink::factory()->create(['client_id' => $client->id]);
            }
        });
    }

    /**
     * @param  Collection<int, ContentType>  $contentTypes
     * @param  Collection<int, Tag>  $tags
     */
    private function seedContents(Client $client, Quarter $quarter, Collection $contentTypes, Collection $tags): void
    {
        $teamMember = $client->teamMembers()->inRandomOrder()->first();
        $clientUser = $client->users()->where('role', 'client')->first();

        foreach (self::STATUSES as $status) {
            /** @var ContentType $contentType */
            $contentType = $contentTypes->random();
            $hasResource = $status !== 'draft';

            $content = Content::factory()->create([
                'quarter_id' => $quarter->id,
                'content_type_id' => $contentType->id,
                'status' => $status,
                // Placeholder.com/picsum: immagini reali e stabili (seed = uuid), non link fake
                // "drive.example.com" che rendevano rotta la griglia stile Instagram del feed.
                'resource_url' => $hasResource ? $this->placeholderImage() : null,
                'cover_resource_url' => $hasResource && $contentType->requires_secondary_asset
                    ? $this->placeholderImage(portrait: true)
                    : null,
                'publish_at' => Carbon::parse($quarter->starts_on)->addDays(fake()->numberBetween(0, 80)),
            ]);

            $content->tags()->attach($tags->random(fake()->numberBetween(1, 3))->pluck('id'));

            if (in_array($status, ['in_review', 'needs_changes'], true) && $teamMember) {
                $content->comments()->create([
                    'author_id' => $teamMember->id,
                    'author_role' => 'team',
                    'body' => fake()->sentence(),
                ]);

                if ($status === 'needs_changes' && $clientUser) {
                    $content->comments()->create([
                        'author_id' => $clientUser->id,
                        'author_role' => 'client',
                        'body' => fake()->sentence(),
                    ]);
                }
            }

            if ($status === 'draft' && blank($content->resource_url)) {
                Alert::raise($client, AlertType::MissingResource, $content, "Manca la risorsa per «{$content->title}».");
            }
        }
    }

    /**
     * Bypassa la factory: fake()->unique() sui mesi si esaurirebbe dopo 12 trimestri.
     *
     * @param  Collection<int, ContentType>  $contentTypes
     */
    private function seedTopicPreviews(Client $client, Quarter $quarter, Collection $contentTypes): void
    {
        $start = Carbon::parse($quarter->starts_on);
        $teamMember = $client->teamMembers()->inRandomOrder()->first();

        foreach (range(0, 2) as $offset) {
            $month = $start->copy()->addMonths($offset);
            $isReady = fake()->boolean(60);

            $topicPreview = TopicPreview::create([
                'quarter_id' => $quarter->id,
                'month_label' => $month->translatedFormat('F'),
                'month_order' => (int) $month->format('n'),
                'status' => $isReady ? 'ready' : 'draft',
                'note' => fake()->optional()->sentence(),
            ]);

            foreach (range(1, fake()->numberBetween(2, 4)) as $sortOrder) {
                TopicPreviewItem::factory()->create([
                    'topic_preview_id' => $topicPreview->id,
                    'content_type_id' => $contentTypes->random()->id,
                    'sort_order' => $sortOrder,
                ]);
            }

            if ($isReady && $teamMember && fake()->boolean(70)) {
                TopicPreviewApproval::factory()->create([
                    'topic_preview_id' => $topicPreview->id,
                    'status' => fake()->randomElement(['approved', 'approved_with_notes', 'revise']),
                    'responded_by' => $teamMember->id,
                ]);
            }
        }
    }

    private function placeholderImage(bool $portrait = false): string
    {
        [$width, $height] = $portrait ? [900, 1600] : [900, 900];

        return "https://picsum.photos/seed/{$this->uniqueSeed()}/{$width}/{$height}";
    }

    private function uniqueSeed(): string
    {
        return (string) fake()->uuid();
    }
}
