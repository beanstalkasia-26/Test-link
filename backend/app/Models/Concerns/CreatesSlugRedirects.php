<?php

namespace App\Models\Concerns;

use App\Models\Redirect;

trait CreatesSlugRedirects
{
    public static function bootCreatesSlugRedirects(): void
    {
        static::updated(function ($model): void {
            if (! $model->wasChanged(['slug', 'path', 'type']) || ! $model->isLive()) {
                return;
            }

            $previous = (clone $model)->setRawAttributes($model->getRawOriginal());
            $previous->setRelations($model->getRelations());
            $oldPath = $previous->publicPath();
            $newPath = $model->publicPath();

            if ($oldPath && $newPath && $oldPath !== $newPath) {
                Redirect::record($oldPath, $newPath);
            }
        });
    }
}
