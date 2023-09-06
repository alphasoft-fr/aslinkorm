<?php

namespace Test\AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\DoctrineManager;
use PDO;
use PHPUnit\Framework\TestCase;
use Test\AlphaSoft\AsLinkOrm\Model\Post;
use Test\AlphaSoft\AsLinkOrm\Model\User;
use Test\AlphaSoft\AsLinkOrm\Repository\PostRepository;
use Test\AlphaSoft\AsLinkOrm\Repository\UserRepository;

class RepositoryTest extends TestCase
{
    private $connection;
    private $userRepository;
    private $postRepository;

    protected function setUp(): void
    {
        $manager = new DoctrineManager([
            'url' => 'sqlite:///:memory:',
            'driverOptions' => array(
                PDO::ATTR_EMULATE_PREPARES => FALSE,
                PDO::FETCH_NUM => true,
            )
        ]);

        $this->connection = $manager->getConnection();

        $this->setUpDatabaseSchema();


        $this->userRepository = $manager->getRepository(UserRepository::class);
        $this->postRepository = $manager->getRepository(PostRepository::class);
    }

    protected function setUpDatabaseSchema(): void
    {
        $this->connection->executeStatement('CREATE TABLE user (
                id INTEGER PRIMARY KEY,
                firstname TEXT,
                lastname TEXT,
                email TEXT,
                password TEXT,
                is_active INTEGER
            );');

        $this->connection->executeStatement('CREATE TABLE post (
                id INTEGER PRIMARY KEY,
                user_id INTEGER,
                title TEXT,
                content TEXT,
                FOREIGN KEY (user_id) REFERENCES user (id)
            );');
    }

    public function testFindOneByReturnsModel()
    {
        $this->insertTestData();

        $result = $this->userRepository->findOneBy(['id' => 1]);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('John', $result->get('firstname'));
        $this->assertEquals(true, (bool)$result->get('isActive'));
    }

    public function testFindByReturnsArrayOfModels()
    {
        $this->insertTestData();

        $users = $this->userRepository->findBy(['isActive' => true]);

        $this->assertCount(2, $users);
        foreach ($users as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }

    public function testFindOneByReturnsNullWhenNotFound()
    {
        $this->insertTestData();

        $result = $this->userRepository->findOneBy(['id' => 10]);

        $this->assertNull($result);
    }

    public function testUpdateUpdatesModel()
    {
        $this->insertTestData();

        $user = $this->userRepository->findOneBy(['id' => 1]);
        $user->set('firstname', 'UpdatedName');
        $this->userRepository->update($user);

        $updatedUser = $this->userRepository->findOneBy(['id' => 1]);
        $this->assertEquals('UpdatedName', $updatedUser->get('firstname'));
    }

    public function testHasOne()
    {
        $this->insertTestData();

        /**
         * @var Post $post
         */
        $post = $this->postRepository->findOneBy(['id' => 1]);
        $relatedUser = $post->getUser();
        $this->assertSame(1, $relatedUser->getPrimaryKeyValue());
    }

    public function testHasMany()
    {
        $this->insertTestData();

        /**
         * @var User $user
         */
        $user = $this->userRepository->findOneBy(['id' => 1]);
        $relatedPosts = iterator_to_array($user->getPosts(), false);

        $this->assertCount(1, $relatedPosts);
        $this->assertInstanceOf(Post::class, $relatedPosts[0]);
        $this->assertEquals('First Post', $relatedPosts[0]->get('title'));
    }

    public function testMultipleFind()
    {
        $this->insertTestData();

        /**
         * @var User $user
         */
        $users = $this->userRepository->findBy(['firstname' => ['John', 'Jane']]);
        $this->assertCount(2, iterator_to_array($users, false));
    }

    public function testToDbWithDefaultColumnMapping()
    {
        $user = new User([
            'id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'isActive' => true,
        ]);

        $result = $user->toDb();

        $expectedDbData = [
            'id' => 1,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'is_active' => true,
        ];

        $this->assertEquals($expectedDbData, $result);
    }

    public function testGetPosts()
    {
        $this->insertTestData();

        $user = $this->userRepository->findOneBy(['id' => 1]);

        // Test initial posts count
        $this->assertCount(1, $user->getPosts());

        // Adding a new post
        $newPost = new Post([
            'title' => 'New Post',
            'content' => 'This is a new post.',
        ]);
        $newPost->setUser($user);
        $this->postRepository->insert($newPost);

        // Refreshing the user to get the updated posts
        $user = $this->userRepository->findOneBy(['id' => 1]);

        // Test updated posts count
        $this->assertCount(2, $user->getPosts());

        // Removing a post
        $postToRemove = iterator_to_array($user->getPosts(), false)[0];
        $this->postRepository->delete($postToRemove);

        // Refreshing the user to get the updated posts
        $user = $this->userRepository->findOneBy(['id' => 1]);

        // Test final posts count
        $this->assertCount(1, $user->getPosts());
    }

    public function testModelCache()
    {
        $this->insertTestData();

        // Fetch the same user twice
        $user1 = $this->userRepository->findOneBy(['id' => 1]);
        $user2 = $this->userRepository->findOneBy(['id' => 1]);

        $this->assertTrue(spl_object_hash($user1) === spl_object_hash($user2));

        // Fetch a different user
        $user3 = $this->userRepository->findOneBy(['id' => 2]);

        // Check if it's a different instance
        $this->assertNotSame($user1, $user3);

        // Fetch the same post twice
        $post1 = $this->postRepository->findOneBy(['id' => 1]);
        $post2 = $this->postRepository->findOneBy(['id' => 1]);

        // Check if both instances are the same
        $this->assertSame($post1, $post2);

        // Fetch a different post
        $post3 = $this->postRepository->findOneBy(['id' => 2]);

        // Check if it's a different instance
        $this->assertNotSame($post1, $post3);

        // Create a new post for user 1
        $newPost = new Post([
            'title' => 'New Post',
            'content' => 'This is a new post.',
            'user_id' => $user1->getPrimaryKeyValue(),
        ]);


        $this->postRepository->insert($newPost);
        $posts = $user1->getPosts();

        $this->assertTrue($posts->contains($newPost));
    }


    protected function insertTestData(): void
    {

        $user_1 = new User([
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'isActive' => true,
        ]);

        $user_2 = new User([
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'email' => 'jane@example.com',
            'password' => 'password',
            'isActive' => true,
        ]);

        $insert = $this->userRepository->insert($user_1);
        $this->assertEquals(1, $insert);
        $this->assertEquals(1, (int)$user_1->getPrimaryKeyValue());
        $insert = $this->userRepository->insert($user_2);
        $this->assertEquals(1, $insert);
        $this->assertEquals(2, (int)$user_2->getPrimaryKeyValue());

        // Inserting posts associated with users
        $post_1 = new Post([
            'title' => 'First Post',
            'content' => 'This is the content of the first post.',
        ]);

        $post_1->setUser($user_1);

        $post_2 = new Post([
            'title' => 'Second Post',
            'content' => 'This is the content of the second post.',
        ]);

        $post_2->setUser($user_2);

        $this->assertEquals(1, $this->postRepository->insert($post_1));
        $this->assertEquals(1, $this->postRepository->insert($post_2));

    }
}
