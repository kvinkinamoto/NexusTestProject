<?php

namespace App\Nexus\Modules\ProductQuestion\Services;

use App\Nexus\Modules\ProductQuestion\Models\ProductQuestion;
use App\Nexus\Modules\ProductQuestion\Models\QuestionReaction;

class QuestionService
{
    public function getQuestions(int $productId, int $perPage, int $page, ?string $sort = null)
    {
        $query = ProductQuestion::query()
            ->where('product_id', $productId)
            ->whereIsRoot()
            ->publish()
            ->with(['user'])
            ->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('value', 1),
                'reactions as dislikes_count' => fn ($q) => $q->where('value', -1),
            ]);

        $query->orderBy('created_at', $sort === 'date' ? 'asc' : 'desc');

        $questions = $query->paginate($perPage, ['*'], 'page', $page);

        $questions->getCollection()->transform(function (ProductQuestion $question) {
            $question->comments_tree = $question->descendants()
                ->publish()
                ->with(['user'])
                ->withCount([
                    'reactions as likes_count' => fn ($q) => $q->where('value', 1),
                    'reactions as dislikes_count' => fn ($q) => $q->where('value', -1),
                ])
                ->defaultOrder()
                ->get()
                ->toTree($question->id);

            $question->rating = $question->likes_count - $question->dislikes_count;

            return $question;
        });

        return $questions;
    }

    public function getUserQuestions()
    {
        $questions = ProductQuestion::query()
            ->where('user_id', auth()->id())
            ->whereIsRoot()
            ->with(['user', 'product'])
            ->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('value', 1),
                'reactions as dislikes_count' => fn ($q) => $q->where('value', -1),
            ])
            ->publish()
            ->orderBy('created_at', 'desc')
            ->get();

        $questions->transform(function (ProductQuestion $question) {
            $question->comments_tree = $question->descendants()
                ->publish()
                ->with(['user'])
                ->withCount([
                    'reactions as likes_count' => fn ($q) => $q->where('value', 1),
                    'reactions as dislikes_count' => fn ($q) => $q->where('value', -1),
                ])
                ->defaultOrder()
                ->get()
                ->toTree($question->id);

            return $question;
        });

        return $questions;
    }

    public function addQuestion(array $data): ProductQuestion
    {
        $publish = ! empty($data['parent_id']);

        return ProductQuestion::create([
            'product_id' => $data['product_id'],
            'parent_id' => $data['parent_id'] ?? null,
            'text' => $data['text'],
            'publish' => $publish,
            'user_id' => auth()->id(),
            'guest_name' => auth()->check() ? null : $data['name'],
            'guest_email' => auth()->check() ? null : $data['email'],
        ]);
    }

    public function deleteQuestion(int $id): void
    {
        ProductQuestion::query()
            ->where('user_id', auth()->id())
            ->findOrFail($id)
            ->delete();
    }

    public function toggleReaction(array $data): void
    {
        $query = QuestionReaction::query()->where('question_id', $data['question_id']);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $reaction = $query->first();

        if ($reaction && (int) $reaction->value === (int) $data['value']) {
            $reaction->delete();

            return;
        }

        if ($reaction) {
            $reaction->update(['value' => $data['value']]);

            return;
        }

        QuestionReaction::create([
            'user_id' => auth()->id(),
            'session_id' => auth()->check() ? null : session()->getId(),
            'question_id' => $data['question_id'],
            'value' => $data['value'],
        ]);
    }
}
