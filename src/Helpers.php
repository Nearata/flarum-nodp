<?php

namespace TheTurk\NoDP;

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

        if ($actor->can('doublePost', $discussion)) return true;

        /**
         * @var \Flarum\Post\CommentPost
         */
        $lastPost = $discussion->posts()
            ->where('type', 'comment')
            ->latest()
            ->first();

        if ($actor->id !== $lastPost->user_id) return true;

        $allowDpNoRepliesMinutes = (int) $settings->get('the-turk-nodp.allow_dp_no_replies_minutes');

        if ($allowDpNoRepliesMinutes !== 0 && $lastPost->created_at->addMinutes($allowDpNoRepliesMinutes)->isPast()) return true;

        if ($actor->cannot('edit', $lastPost)) return true;

        $timeLimit = (int) $settings->get('the-turk-nodp.time_limit');

        if ($timeLimit === 0) return false;

        return $lastPost->created_at->addMinutes($timeLimit)->isPast();
    }
}
