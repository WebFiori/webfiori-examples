<?php
namespace App\Tasks;

use App\Infrastructure\Repository\PostRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use App\Apis\EmailHelper;
use WebFiori\Framework\Scheduler\AbstractTask;

/**
 * Background task that sends a weekly digest of recently published posts.
 *
 * Runs every Monday at 9:00 AM. Use `--test` to store email as HTML locally.
 */
class WeeklyDigestTask extends AbstractTask {
    public function __construct() {
        parent::__construct('send-weekly-digest', '0 9 * * 1', 'Sends weekly digest of new posts.');
        $this->addExecutionArgs([
            '--test' => [
                'description' => 'Store email as HTML file instead of sending via SMTP.',
            ],
        ]);
    }

    public function afterExec(): void {
    }

    public function execute(): void {
        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $repo = new PostRepository($db);

        $weekAgo = date('Y-m-d H:i:s', strtotime('-7 days'));
        $posts = $repo->findPublishedSince($weekAgo);

        if (empty($posts)) {
            return;
        }

        $email = EmailHelper::create();
        $email->setSubject('Weekly Blog Digest — ' . count($posts) . ' new post(s)');
        $email->addTo('subscribers@example.com', 'Blog Subscribers');

        $email->insert('h2')->text('Weekly Blog Digest');
        $email->insert('p')->text('Week of ' . date('M d, Y', strtotime('-7 days')) . ' — ' . date('M d, Y'));

        foreach ($posts as $post) {
            $email->insert('h3')->text($post->title);
            $email->insert('p')->text(
                'By ' . ($post->authorName ?? 'Unknown') .
                ' | ' . $post->createdAt .
                ' | Category: ' . ($post->categoryName ?? 'Uncategorized')
            );
        }

        $email->send();
    }

    public function onFail(): void {
    }

    public function onSuccess(): void {
    }
}
