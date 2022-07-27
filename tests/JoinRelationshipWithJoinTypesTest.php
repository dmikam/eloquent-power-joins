<?php

namespace Kirschbaum\PowerJoins\Tests;

use Kirschbaum\PowerJoins\Tests\Models\Category;
use Kirschbaum\PowerJoins\Tests\Models\Comment;
use Kirschbaum\PowerJoins\Tests\Models\Group;
use Kirschbaum\PowerJoins\Tests\Models\Post;
use Kirschbaum\PowerJoins\Tests\Models\User;
use Kirschbaum\PowerJoins\Tests\Models\UserProfile;

class JoinRelationshipWithJoinTypesTest extends TestCase {

    /**
     * $category_1
     *      |- $post_1_1 (published)
     *      |- $post_1_2 (published)
     *
     * $category_2
     *      |- $post_2_1 (unpublished)
     */
    protected function prepare_test_case_1(){
        $category_1 = factory(Category::class)->create();
        $category_2 = factory(Category::class)->create(); // with no posts assigned

        $post_1_1 = factory(Post::class)->create(['category_id' => $category_1->id]);
        $post_1_2 = factory(Post::class)->create(['category_id' => $category_1->id]);

        $post_2_1 = factory(Post::class)->create(['category_id' => $category_2->id, 'published' => false]);

        $post_no_category_1 = factory(Post::class)->create(['category_id' => 0, 'published' => true]);
        $post_no_category_2 = factory(Post::class)->create(['category_id' => 0, 'published' => false]);
    }

    /**
     * @test
     */
    public function test_categories_inner_join_published_posts() {
        $this->prepare_test_case_1();

        $categories_with_published_posts = Category::query()->joinRelationship('posts', function($join){
            $join->as('post');
            $join->published();
        });

        $categories_with_UNpublished_posts = Category::query()->joinRelationship('posts', function($join){
            $join->where('published', false);
        });

        // dump($categories_with_published_posts->toSql(), $categories_with_published_posts->get()->toArray());
        $this->assertCount(2, $categories_with_published_posts->get());
        $this->assertCount(1, $categories_with_UNpublished_posts->get());
    }

    /**
     * @test
     */
    public function test_categories_left_join_published_posts() {
        $this->prepare_test_case_1();

        $categories_with_published_posts = Category::query()->joinRelationship('posts', function($join){
            $join->left();
            $join->published();
        })->select('posts.*');
        $this->assertCount(3, $categories_with_published_posts->get());

        dump('LEFT JOUIN RESULTS', $categories_with_published_posts->get('posts'));

        $categories_with_UNpublished_posts = Category::query()->joinRelationship('posts', function($join){
            $join->left();
            $join->where('published', false);
        });

        // dump($categories_with_published_posts->toSql(), $categories_with_published_posts->get()->toArray());
        $this->assertCount(3, $categories_with_UNpublished_posts->get());
    }

    public function test_conditions_inside_joins() {
        $this->markTestSkipped('[SKIPPED] Reported inconsistent conditioning of joins: https://github.com/kirschbaum-development/eloquent-power-joins/issues/105');
        return;

        $queryBuilder = User::query()->joinRelationship('posts', function ($join) {
            $join->where('posts.published', true);
        });

        $query = $queryBuilder->toSql();

        dump("RUN 1", $query);

        $queryBuilder = User::query()->joinRelationship('posts.comments', [
            'posts' => function ($join) {
                $join->where('posts.published', true);
            },
            'comments' => function ($join) {
                $join->where('comments.approved', true);
                $join->left();
            },
        ]);

        $query = $queryBuilder->toSql();
        dump("RUN 2.1", $query);

        $queryBuilder = User::query()->joinRelationship('posts.comments', [
            'posts' => function ($join) {
                $join->where('posts.published', true);
                // $join->left();
            },
            // 'comments' => function ($join) {
            //     $join->where('comments.approved', true);
            //     $join->left();
            // },
        ]);

        $query = $queryBuilder->toSql();
        dump("RUN 2.2", $query);

        $queryBuilder = User::query()->joinRelationship('posts', [
            'posts' => function ($join) {
                $join->where('posts.published', true);
                // $join->left();
            },
            // 'comments' => function ($join) {
            //     $join->where('comments.approved', true);
            //     $join->left();
            // },
        ]);

        $query = $queryBuilder->toSql();
        dump("RUN 2.3", $query);

        $this->markTestSkipped('[SKIPPED] Just trying conditioned joins without nesting');
    }



}
