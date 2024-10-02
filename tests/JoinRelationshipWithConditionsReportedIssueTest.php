<?php

namespace Kirschbaum\PowerJoins\Tests;

use Kirschbaum\PowerJoins\Tests\Models\Category;
use Kirschbaum\PowerJoins\Tests\Models\Comment;
use Kirschbaum\PowerJoins\Tests\Models\Group;
use Kirschbaum\PowerJoins\Tests\Models\Post;
use Kirschbaum\PowerJoins\Tests\Models\User;
use Kirschbaum\PowerJoins\Tests\Models\UserProfile;

class JoinRelationshipWithConditionsReportedIssueTest extends TestCase {

    public function test_conditions_inside_join_plain_callback()
    {
        $queryBuilder = User::query()->joinRelationship('posts', function ($join) {
            $join->published();
        });

        $query = $queryBuilder->toSql();

        $this->assertQueryContains('"posts"."published" = ?', $query);
    }

    public function test_conditions_inside_nested_join_callback_array_callback()
    {
        $queryBuilder = User::query()->joinRelationship('posts.comments', [
            'posts' => function ($join) {
                $join->published();
            }
        ]);

        $query = $queryBuilder->toSql();
        $this->assertQueryContains('"posts"."published" = ?', $query);
    }
}
