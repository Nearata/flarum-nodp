<?php

namespace Nearata\NoDP;

use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Discussion\Discussion;
use Flarum\Extend;
use Flarum\Post\Event\Saving as PostSavingEvent;
use Nearata\NoDP\Listeners\DoublePostingListener;

return [
    (new Extend\Frontend('forum'))
        ->css(__DIR__.'/less/forum.less')
        ->js(__DIR__.'/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),

    (new Extend\Locales(__DIR__.'/locale')),

    (new Extend\Event)
        ->listen(PostSavingEvent::class, DoublePostingListener::class),

    (new Extend\ApiSerializer(DiscussionSerializer::class))
        ->attributes(function (DiscussionSerializer $serializer, Discussion $discussion, array $attributes) {
            $attributes['canDoublePost'] = Helpers::canDoublePost($serializer->getActor(), $discussion);

            return $attributes;
        }),

    (new Extend\Settings)
        ->default('nearata-nodp.time_limit', 1440),
];
