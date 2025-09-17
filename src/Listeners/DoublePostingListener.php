<?php

namespace Nearata\NoDP\Listeners;

use Flarum\Post\Event\Saving as PostSaving;
use Flarum\User\Exception\PermissionDeniedException;
use Nearata\NoDP\Helpers;

class DoublePostingListener
{
    public function handle(PostSaving $event)
    {
        $post = $event->post;

        // new discussion
        if (is_null($post->discussion->first_post_id)) {
            return;
        }

        // can't double post while editing
        if ($post->exists) {
            return;
        }

        if (! Helpers::canDoublePost($event->actor, $post->discussion)) {
            throw new PermissionDeniedException;
        }
    }
}
