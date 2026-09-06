<?php

namespace App\Nexus\Plugins\GraphQL;

use App\Http\Controllers\Controller;
use GraphQL\GraphQL;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GraphQLController extends Controller
{
    public function __construct(private SchemaBuilder $schemaBuilder) {}

    public function handle(Request $request): JsonResponse
    {
        $schema = $this->schemaBuilder->build();

        $result = GraphQL::executeQuery(
            $schema,
            (string) $request->input('query', ''),
            null,
            null,
            (array) $request->input('variables', []),
            $request->input('operationName'),
        );

        return response()->json($result->toArray((bool) config('app.debug')));
    }
}
