<?php

namespace Nearata\NoDP;

use Flarum\Discussion\Discussion;
use Flarum\User\User;

class Helpers
{
    public static function canDoublePost(User $actor, Discussion $discussion)
    {
        /**
         * @var \Flarum\Settings\SettingsRepositoryInterface
         */
        $settings = resolve('flarum.settings');

        if ($actor->can('doublePost', $discussion)) {
            return true;
        }

        /**
         * @var \Flarum\Post\Post
         */
        $lastPost = $discussion->posts()
            ->where('type', 'comment')
            ->latest()
            ->first();

        if ($actor->id !== $lastPost->user_id) {
            return true;
        }

        if ($actor->cannot('edit', $lastPost)) {
            return true;
        }

        $timeLimit = (int) $settings->get('nearata-nodp.time_limit');

        if ($timeLimit === 0) {
            return false;
        }

        return $lastPost->created_at->addMinutes($timeLimit)->isPast();
    }
}
