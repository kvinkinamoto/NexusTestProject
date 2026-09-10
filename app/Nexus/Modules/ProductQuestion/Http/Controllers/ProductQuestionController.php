<?php

namespace App\Nexus\Modules\ProductQuestion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\ProductQuestion\Requests\QuestionStoreRequest;
use App\Nexus\Modules\ProductQuestion\Requests\ToggleReactionRequest;
use App\Nexus\Modules\ProductQuestion\Services\QuestionService;

class ProductQuestionController extends Controller
{
    public function __construct(
        private readonly QuestionService $service,
    ) {}

    public function store(QuestionStoreRequest $request)
    {
        $question = $this->service->addQuestion($request->validated());

        return response()->json([
            'status' => 'success',
            'question' => $question,
        ]);
    }

    public function destroy(int $id)
    {
        $this->service->deleteQuestion($id);

        return response()->json(['status' => 'success']);
    }

    public function toggleReaction(ToggleReactionRequest $request)
    {
        $this->service->toggleReaction($request->validated());

        return response()->json(['status' => 'success']);
    }
}
