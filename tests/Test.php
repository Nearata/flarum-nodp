<?php

use Carbon\Carbon;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\Extend;
use Flarum\Group\Group;

class Test extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extend(
            (new Extend\ThrottleApi())->remove('postTimeout')
        );

        $this->extension('nearata-nodp');

        // Note that this input isn't validated: make sure you're populating with valid, representative data.
        $this->prepareDatabase([
            'users' => [
                $this->normalUser()
            ],
            'discussions' => [
                ['id' => 1, 'title' => 'some title', 'created_at' => Carbon::now(), 'last_posted_at' => Carbon::now(), 'user_id' => 1, 'first_post_id' => 1, 'comment_count' => 1]
            ],
            'posts' => [
                ['id' => 1, 'number' => 1, 'discussion_id' => 1, 'created_at' => Carbon::now(), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>something</p></t>']
            ]
        ]);
    }

    private function httpRequest(int $userId)
    {
        $request = $this->request('POST', '/api/posts', [
            'authenticatedAs' => $userId,
            'json' => [
                'data' => [
                    'attributes' => [
                        'content' => 'reply with predetermined content for automated testing - too-obscure',
                    ],
                    'relationships' => [
                        'discussion' => ['data' => ['id' => 1]],
                    ],
                ],
            ],
        ]);
        return $this->send($request);
    }

    private function firstPostAsNormalUser()
    {
        $this->database()
            ->table('posts')
            ->where('id', 1)
            ->update(['user_id' => 2]);
    }

    /**
     * @test
     */
    public function can_double_post_admin()
    {
        $response = $this->httpRequest(1);
        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function can_double_post_has_permission()
    {
        $this->firstPostAsNormalUser();
        $this->database()
            ->table('group_permission')
            ->insert([
                'permission' => 'discussion.doublePost',
                'group_id' => Group::MEMBER_ID
            ]);

        $response = $this->httpRequest(2);
        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function can_post_if_not_last_poster()
    {
        $response = $this->httpRequest(2);
        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function cannot_double_post_too_early()
    {
        $this->firstPostAsNormalUser();
        $response = $this->httpRequest(2);
        $this->assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function can_edit_post()
    {
        $this->firstPostAsNormalUser();

        $request = $this->request('PATCH', '/api/posts/1', [
            'authenticatedAs' => 2,
            'json' => [
                'data' => [
                    'attributes' => [
                        'content' => 'Hello, World!',
                    ],
                ],
            ],
        ]);
        $response = $this->send($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function can_double_post_if_cannot_edit()
    {
        $this->setting('allow_post_editing', 0);
        $this->firstPostAsNormalUser();

        $response = $this->httpRequest(2);
        $this->assertEquals(201, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function can_double_post_time_has_passed()
    {
        $this->database()
            ->table('posts')
            ->where('id', 1)
            ->update([
                'user_id' => 2,
                'created_at' => Carbon::now()->subMinutes(1440)
            ]);

        $response = $this->httpRequest(2);
        $this->assertEquals(201, $response->getStatusCode());
    }
}
