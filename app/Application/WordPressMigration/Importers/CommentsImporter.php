<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Identity\Models\User;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommentsImporter
{
    private int $processed = 0;
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;
    private int $missingPostMap = 0;
    private int $failed = 0;
    private int $relationsUpdated = 0;

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('comments')
            ->where('comment_type', '')
            ->orWhere('comment_type', 'comment')
            ->orderBy('comment_ID')
            ->chunkById(500, function ($wpComments) use ($onProgress) {
                foreach ($wpComments as $wpComment) {
                    $this->processed++;

                    try {
                        $result = $this->importComment($wpComment);

                        match ($result) {
                            'created' => $this->created++,
                            'updated' => $this->updated++,
                            'skipped' => $this->skipped++,
                            'missing_post_map' => $this->missingPostMap++,
                        };
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress comment import failed', [
                            'wordpress_comment_id' => $wpComment->comment_ID,
                            'wordpress_post_id' => $wpComment->comment_post_ID,
                            'message' => $exception->getMessage(),
                        ]);
                    }

                    if ($onProgress) {
                        $onProgress($this->stats());
                    }
                }
            }, 'comment_ID');

        $this->syncCommentRelations();
    }

    public function total(): int
    {
        return DB::connection('wordpress')
            ->table('comments')
            ->where(function ($query) {
                $query
                    ->where('comment_type', '')
                    ->orWhere('comment_type', 'comment');
            })
            ->count();
    }

    public function stats(): array
    {
        return [
            'processed' => $this->processed,
            'created' => $this->created,
            'updated' => $this->updated,
            'skipped' => $this->skipped,
            'missing_post_map' => $this->missingPostMap,
            'relations_updated' => $this->relationsUpdated,
            'failed' => $this->failed,
        ];
    }

    private function importComment(object $wpComment): string
    {
        $commentable = $this->resolveCommentable((int)$wpComment->comment_post_ID);

        if (!$commentable) {
            return 'missing_post_map';
        }

        $existingMap = WpMigrationMap::query()
            ->where('source_type', 'wordpress_comment')
            ->where('source_id', $wpComment->comment_ID)
            ->where('target_type', Comment::class)
            ->first();

        $payload = $this->buildPayload($wpComment, $commentable);

        if ($existingMap) {
            $comment = Comment::query()->find($existingMap->target_id);

            if (!$comment) {
                return 'skipped';
            }

            $comment->forceFill($payload)->save();

            return 'updated';
        }

        $comment = Comment::query()->create($payload);

        WpMigrationMap::query()->create([
            'source_type' => 'wordpress_comment',
            'source_id' => $wpComment->comment_ID,
            'target_type' => Comment::class,
            'target_id' => $comment->id,
            'meta' => [
                'comment_post_ID' => $wpComment->comment_post_ID,
                'comment_parent' => $wpComment->comment_parent,
                'comment_approved' => $wpComment->comment_approved,
                'comment_type' => $wpComment->comment_type,
                'user_id' => $wpComment->user_id,
            ],
        ]);

        return 'created';
    }

    private function buildPayload(object $wpComment, Model $commentable): array
    {
        $status = $this->mapStatus((string)$wpComment->comment_approved);
        $createdAt = $this->normalizeDate($wpComment->comment_date);

        return [
            'commentable_type' => $commentable->getMorphClass(),
            'commentable_id' => $commentable->getKey(),

            // مرحله دوم ست می‌شوند
            'root_id' => null,
            'parent_id' => null,

            'user_id' => $this->resolveUserId((int)$wpComment->user_id),
            'author_name' => $this->cleanText($wpComment->comment_author) ?: null,
            'author_email' => $this->normalizeEmail($wpComment->comment_author_email),
            'ip_address' => $this->normalizeIp($wpComment->comment_author_IP),
            'user_agent' => $this->cleanText($wpComment->comment_agent) ?: null,
            'content' => $this->cleanContent($wpComment->comment_content),
            'status' => $status,
            'approved_at' => $status === CommentStatusEnum::APPROVED ? $createdAt : null,
            'rating_percent' => null,
            'likes_count' => 0,
            'dislikes_count' => 0,
            'score' => 0,
            'created_at' => $createdAt,
            'updated_at' => $this->normalizeDate($wpComment->comment_date),
        ];
    }

    private function syncCommentRelations(): void
    {
        DB::connection('wordpress')
            ->table('comments')
            ->where(function ($query) {
                $query
                    ->where('comment_type', '')
                    ->orWhere('comment_type', 'comment');
            })
            ->where('comment_parent', '>', 0)
            ->orderBy('comment_ID')
            ->chunkById(500, function ($wpComments) {
                foreach ($wpComments as $wpComment) {
                    try {
                        $comment = $this->resolveImportedComment((int)$wpComment->comment_ID);
                        $parent = $this->resolveImportedComment((int)$wpComment->comment_parent);

                        if (!$comment || !$parent) {
                            continue;
                        }

                        $comment->forceFill([
                            'parent_id' => $parent->id,
                            'root_id' => $this->resolveRootId($parent),
                        ])->save();

                        $this->relationsUpdated++;
                    } catch (Throwable $exception) {
                        logger()->error('WordPress comment relation sync failed', [
                            'wordpress_comment_id' => $wpComment->comment_ID,
                            'wordpress_parent_comment_id' => $wpComment->comment_parent,
                            'message' => $exception->getMessage(),
                        ]);
                    }
                }
            }, 'comment_ID');
    }

    private function resolveRootId(Comment $parent): int
    {
        return $parent->root_id ?: $parent->id;
    }

    private function resolveImportedComment(int $wpCommentId): ?Comment
    {
        $map = WpMigrationMap::query()
            ->where('source_type', 'wordpress_comment')
            ->where('source_id', $wpCommentId)
            ->where('target_type', Comment::class)
            ->first();

        if (!$map) {
            return null;
        }

        return Comment::query()->find($map->target_id);
    }

    private function resolveCommentable(int $wpPostId): ?Model
    {
        $map = WpMigrationMap::query()
            ->where('source_type', 'wordpress_post')
            ->where('source_id', $wpPostId)
            ->where('target_type', Post::class)
            ->first();

        if (!$map) {
            return null;
        }

        return Post::withoutGlobalScopes()->find($map->target_id);
    }

    private function resolveUserId(int $wpUserId): ?int
    {
        if ($wpUserId <= 0) {
            return null;
        }

        $map = WpMigrationMap::query()
            ->where('source_type', 'wordpress_user')
            ->where('source_id', $wpUserId)
            ->where('target_type', User::class)
            ->first();

        return $map?->target_id;
    }

    private function mapStatus(string $approved): CommentStatusEnum
    {
        return match ($approved) {
            '1' => CommentStatusEnum::APPROVED,
            '0' => CommentStatusEnum::PENDING,
            'spam', 'trash' => CommentStatusEnum::REJECTED,
            default => CommentStatusEnum::PENDING,
        };
    }

    private function cleanText(?string $text): ?string
    {
        if (!$text) {
            return null;
        }

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private function cleanContent(?string $content): string
    {
        $content = $this->cleanText($content);

        return $content !== null && $content !== '' ? $content : '...';
    }

    private function normalizeEmail(?string $email): ?string
    {
        $email = trim((string)$email);

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    private function normalizeIp(?string $ip): ?string
    {
        $ip = trim((string)$ip);

        if ($ip === '') {
            return null;
        }

        return mb_substr($ip, 0, 45);
    }

    private function normalizeDate(?string $date): Carbon
    {
        try {
            if (!$date || str_starts_with($date, '0000-00-00')) {
                return Carbon::create(2000, 1, 1);
            }

            return Carbon::parse($date);
        } catch (Throwable) {
            return Carbon::create(2000, 1, 1);
        }
    }
}
