<?php

namespace App\Console\Commands;

use App\Admin\ResourceRegistry;
use App\Services\ContentWorkflow;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('content:publish-scheduled')]
#[Description('Publish content whose scheduled publication time has passed')]
class PublishScheduledContent extends Command
{
    public function handle(ContentWorkflow $workflow): int
    {
        $models = collect(ResourceRegistry::all())->filter->isPublishable()->map->model()->values()->all();
        $count = $workflow->publishDue($models);

        $this->info("Published {$count} scheduled item(s).");

        return self::SUCCESS;
    }
}
