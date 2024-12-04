<?php

namespace SolutionForest\InspireCms\Listeners\Content;

use SolutionForest\InspireCms\Events\Content\CreatingContentVersion;
use SolutionForest\InspireCms\Facades\ContentStatusManifest;

class UnpubilshChildren
{
    public function handle(CreatingContentVersion $event)
    {
        if ($event->isPublishing) {
            return;
        }

        $parent = $event->content;
        $unpublishStatus = ContentStatusManifest::getOption('unpublish');
        if (! $unpublishStatus) {
            return;
        }

        if ($parent->status !== $unpublishStatus->getValue()) {
            return;
        }

        $parent->children()->update(['status' => $parent->status]);
    }
}
