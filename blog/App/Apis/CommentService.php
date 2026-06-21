<?php
namespace App\Apis;

use App\Domain\Comment;
use App\Infrastructure\Repository\CommentRepository;
use App\Infrastructure\Repository\PostRepository;
use WebFiori\Database\Database;
use WebFiori\Framework\App;
use WebFiori\Http\Annotations\AllowAnonymous;
use WebFiori\Http\Annotations\PostMapping;
use WebFiori\Http\Annotations\RequestParam;
use WebFiori\Http\Annotations\ResponseBody;
use WebFiori\Http\Annotations\RestController;
use WebFiori\Http\Exceptions\NotFoundException;
use WebFiori\Http\ParamType;
use WebFiori\Http\WebService;

/**
 * REST controller for adding comments to posts.
 *
 * Sends a notification email to the post author when a new comment is added.
 */
#[RestController('comments', 'Post comments API')]
class CommentService extends WebService {
    /**
     * Adds a comment to a published post and notifies the author.
     */
    #[PostMapping]
    #[ResponseBody]
    #[AllowAnonymous]
    #[RequestParam(name: 'postId', type: ParamType::INT, description: 'Post ID')]
    #[RequestParam(name: 'authorName', type: ParamType::STRING, description: 'Commenter name')]
    #[RequestParam(name: 'authorEmail', type: ParamType::EMAIL, description: 'Commenter email')]
    #[RequestParam(name: 'content', type: ParamType::STRING, description: 'Comment text')]
    public function addComment(?int $postId = null, ?string $authorName = null, ?string $authorEmail = null, ?string $content = null): array {
        $db = new Database(App::getConfig()->getDBConnection('blog'));
        $postRepo = new PostRepository($db);
        $post = $postRepo->findByIdWithDetails($postId);

        if ($post === null || $post->status !== 'published') {
            throw new NotFoundException('Post not found.');
        }

        $comment = new Comment(
            postId: $post->id,
            authorName: $authorName,
            authorEmail: $authorEmail,
            content: $content,
            createdAt: date('Y-m-d H:i:s')
        );

        $commentRepo = new CommentRepository($db);
        $commentRepo->save($comment);

        $this->sendNotification($post, $comment);

        return [$comment];
    }

    /**
     * Sends a comment notification email to the blog admin.
     */
    private function sendNotification(object $post, Comment $comment): void {
        $email = EmailHelper::create();
        $email->setSubject('New comment on: ' . $post->title);
        $email->addTo('admin@example.com', 'Blog Admin');

        $email->insert('h2')->text('New Comment');
        $email->insert('p')->text('Post: ' . $post->title);
        $email->insert('p')->text('By: ' . $comment->authorName . ' (' . $comment->authorEmail . ')');
        $email->insert('p')->text($comment->content);

        $email->send();
    }
}
