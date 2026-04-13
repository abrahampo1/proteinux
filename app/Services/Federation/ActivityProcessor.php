<?php

namespace App\Services\Federation;

use App\Models\Federation\FederationActivity;
use App\Services\Federation\Handlers\ForumPostHandler;
use App\Services\Federation\Handlers\ForumThreadHandler;
use App\Services\Federation\Handlers\LibraryEntryHandler;
use App\Services\Federation\Handlers\ScientificDocumentHandler;
use Illuminate\Support\Facades\Log;

class ActivityProcessor
{
    /**
     * @var array<string, class-string>
     */
    private array $handlers = [
        'library_entry' => LibraryEntryHandler::class,
        'forum_thread' => ForumThreadHandler::class,
        'forum_post' => ForumPostHandler::class,
        'scientific_document' => ScientificDocumentHandler::class,
    ];

    /**
     * Process an inbound federation activity by routing to the appropriate handler.
     */
    public function process(FederationActivity $activity): void
    {
        $type = $activity->type;

        if (! isset($this->handlers[$type])) {
            Log::warning("No handler registered for federation activity type: {$type}", [
                'activity_id' => $activity->id,
            ]);

            $activity->markAsFailed("No handler for type: {$type}");

            return;
        }

        try {
            $handler = app($this->handlers[$type]);
            $handler->handle($activity);
            $activity->markAsProcessed();
        } catch (\Throwable $e) {
            Log::error("Federation activity processing failed: {$e->getMessage()}", [
                'activity_id' => $activity->id,
                'type' => $type,
            ]);

            $activity->markAsFailed($e->getMessage());
        }
    }
}
