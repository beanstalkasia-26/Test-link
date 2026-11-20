<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\ContentRevision;
use App\Support\AuditLogger;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ContentWorkflow
{
    public function save(Model $model, array $attributes, array $relations, array $mediaIds = [], ?string $note = null): bool
    {
        return DB::transaction(function () use ($model, $attributes, $relations, $mediaIds, $note): bool {
            if ($model->exists && $model->status === ContentStatus::Published) {
                $snapshot = $model->toSnapshot();
                $data = [
                    'attributes' => array_merge($snapshot['attributes'], $attributes),
                    'relations' => array_merge($snapshot['relations'], $relations),
                    'media' => array_values(array_unique($mediaIds)),
                ];

                $model->workingCopy()->updateOrCreate(
                    ['kind' => ContentRevision::KindWorking],
                    ['data' => $data, 'user_id' => auth()->id(), 'note' => $note ?? 'Pending changes'],
                );
                $model->syncMediaUsage();
                AuditLogger::log('content.draft_saved', $model, 'Saved pending changes to "'.$model->displayTitle().'"');

                return true;
            }

            $isNew = ! $model->exists;
            $model->fill($attributes);
            if ($isNew) {
                $model->status = ContentStatus::Draft;
            }
            $model->save();
            $model->restoreRelations($relations);
            $this->snapshot($model, $note ?? ($isNew ? 'Created' : 'Saved'));
            AuditLogger::log($isNew ? 'content.created' : 'content.updated', $model, ($isNew ? 'Created' : 'Updated').' "'.$model->displayTitle().'"');

            return false;
        });
    }

    public function submitForReview(Model $model): void
    {
        $model->forceFill(['review_state' => 'pending'])->save();
        AuditLogger::log('content.submitted', $model, 'Submitted "'.$model->displayTitle().'" for review');
    }

    public function publish(Model $model, ?string $note = null): void
    {
        DB::transaction(function () use ($model, $note): void {
            $working = $model->workingCopy()->first();
            if ($working) {
                $model->fill($working->data['attributes'] ?? []);
            }

            $model->status = ContentStatus::Published;
            $model->published_at ??= now();
            if ($model->published_at->isFuture()) {
                $model->published_at = now();
            }
            $model->scheduled_for = null;
            $model->review_state = null;
            $model->save();

            if ($working) {
                $model->restoreRelations($working->data['relations'] ?? []);
                $working->delete();
                $model->syncMediaUsage();
            }

            $this->snapshot($model, $note ?? 'Published');
        });

        AuditLogger::log('content.published', $model, 'Published "'.$model->displayTitle().'"');
    }

    public function schedule(Model $model, CarbonInterface $when): void
    {
        $model->scheduled_for = $when;
        $model->review_state = null;
        if ($model->status !== ContentStatus::Published) {
            $model->status = ContentStatus::Scheduled;
        }
        $model->save();

        AuditLogger::log('content.scheduled', $model, 'Scheduled "'.$model->displayTitle().'" for '.$when->toDayDateTimeString());
    }

    public function unpublish(Model $model): void
    {
        $model->forceFill(['status' => ContentStatus::Draft, 'scheduled_for' => null])->save();
        $this->snapshot($model, 'Unpublished');
        AuditLogger::log('content.unpublished', $model, 'Unpublished "'.$model->displayTitle().'"');
    }

    public function discardWorkingCopy(Model $model): void
    {
        $model->workingCopy()->delete();
        $model->syncMediaUsage();
        AuditLogger::log('content.discarded', $model, 'Discarded pending changes to "'.$model->displayTitle().'"');
    }

    public function restore(Model $model, ContentRevision $revision): bool
    {
        $data = $revision->data;
        $held = $this->save($model, $data['attributes'] ?? [], $data['relations'] ?? [], $data['media'] ?? [], 'Restored revision #'.$revision->id);
        AuditLogger::log('content.restored', $model, 'Restored revision #'.$revision->id.' of "'.$model->displayTitle().'"');

        return $held;
    }

    public function publishDue(array $models): int
    {
        $count = 0;
        foreach ($models as $class) {
            $class::query()->whereNotNull('scheduled_for')->where('scheduled_for', '<=', now())
                ->get()->each(function (Model $model) use (&$count): void {
                    $this->publish($model, 'Published on schedule');
                    $count++;
                });
        }

        return $count;
    }

    public function previewModel(Model $model): Model
    {
        $working = $model->workingCopy()->first();
        $preview = clone $model;
        if ($working) {
            $preview->forceFill($working->data['attributes'] ?? []);
            $preview->previewRelations($working->data['relations'] ?? []);
        }

        return $preview;
    }

    private function snapshot(Model $model, string $note): void
    {
        $model->revisions()->create([
            'kind' => ContentRevision::KindSnapshot,
            'user_id' => auth()->id(),
            'note' => $note,
            'data' => $model->fresh()->toSnapshot(),
        ]);

        $keep = $model->revisions()->where('kind', ContentRevision::KindSnapshot)->latest('id')->skip(50)->take(PHP_INT_MAX)->pluck('id');
        if ($keep->isNotEmpty()) {
            ContentRevision::query()->whereIn('id', $keep)->delete();
        }
    }
}
