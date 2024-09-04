<?php

namespace Test\AlphaSoft\AsLinkOrm;

use AlphaSoft\AsLinkOrm\EntityManager;
use PDO;
use PHPUnit\Framework\TestCase;
use Test\AlphaSoft\AsLinkOrm\Model\Post;
use Test\AlphaSoft\AsLinkOrm\Model\User;
use Test\AlphaSoft\AsLinkOrm\Repository\PostRepository;
use Test\AlphaSoft\AsLinkOrm\Repository\UserRepository;

class ConnectionTest extends TestCase
{
    private $connection;
    private $manager;
    private $userRepository;
    private $postRepository;

    protected function setUp(): void
    {
        $manager = new EntityManager([
            'url' => 'sqlite:///:memory:',
            'driverOptions' => array(
                PDO::ATTR_EMULATE_PREPARES => FALSE,
                PDO::FETCH_NUM => true,
            )
        ]);

        $this->connection = $manager->getConnection();
        $this->connection->enableDebugger();
        $this->setUpDatabaseSchema();

        $this->manager = $manager;
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

    protected function insertDb(): void
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

        $this->userRepository->insert($user_1);
         $this->userRepository->insert($user_2);

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

        $this->postRepository->insert($post_1);
        $this->postRepository->insert($post_2);

    }

    public function testLogExecuteStatement(): void
    {
        $sqlLogger = $this->manager->getConnection()->getSqlDebugger();
        $this->assertCount(2, $sqlLogger->getQueries());

        $this->insertDb();

        $this->assertCount(6, $sqlLogger->getQueries());
    }
}
